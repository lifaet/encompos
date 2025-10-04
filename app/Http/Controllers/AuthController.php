<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ForgetPassword;
use App\Mail\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $databases = [];
        foreach ($_ENV as $key => $value) {
            if (str_starts_with($key, 'DB_DATABASE')) {
                $databases[] = $value;
            }
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'db_select' => 'required',
            ]);

            $selectedDb = $request->db_select;

            $request->session()->put('selected_db', $selectedDb);
            $request->session()->put('current_db', $selectedDb);

            // Switch DB connection
            config(['database.connections.mysql.database' => $selectedDb]);
            DB::purge('mysql');

            $credentials = $request->only('email', 'password');
            $remember = $request->has('remember_me');

            if (!Auth::validate($credentials)) {
                return redirect()->back()->with('error', 'Incorrect email or password');
            }

            $user = User::where('email', $request->email)->first();
            if ($user && Auth::attempt($credentials, $remember)) {
                $request->session()->regenerate();
                return redirect()->intended(route('backend.admin.dashboard'));
            }

            return redirect()->back()->with('error', 'Incorrect email or password');
        }

        $current_db = session('current_db', $databases[0] ?? env('DB_DATABASE'));

        return view('frontend.authentication.login', [
            'databases' => $databases,
            'current_db' => $current_db,
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        return redirect()->route('login');
    }

    // Register
    public function register(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'name' => 'required',
                'email' => 'required|email|unique:users',
                'password' => 'required|confirmed|min:6',
            ]);

            $newUser = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'username' => uniqid(),
            ]);

            Auth::login($newUser);

            return redirect()->route('backend.admin.dashboard')->with('success', 'User registered successfully');
        }

        return view('frontend.authentication.sign-up');
    }

    // Helper to switch DB connection
    private function switchDbFromSession(Request $request)
    {
        $selectedDb = $request->session()->get('selected_db');
        if ($selectedDb) {
            config(['database.connections.mysql.database' => $selectedDb]);
            DB::purge('mysql');
        }
    }

    // Forget Password
    public function forgetPassword(Request $request)
    {
        $databases = [];
        foreach ($_ENV as $key => $value) {
            if (str_starts_with($key, 'DB_DATABASE')) {
                $databases[] = $value;
            }
        }

        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required|email',
                'db_select' => 'required',
            ]);

            // Store selected DB in session
            $request->session()->put('selected_db', $request->db_select);

            // Switch DB connection
            $this->switchDbFromSession($request);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return back()->with('error', 'User not found');
            }

            $otp = rand(11111, 99999);

            ForgetPassword::updateOrCreate(
                ['user_id' => $user->id],
                ['otp' => $otp, 'email' => $user->email, 'suspend_duration' => now()->addMinutes(5)]
            );

            session(['user_id' => $user->id, 'reset-email' => $user->email]);

            Mail::to($user->email)->send(new PasswordReset([
                'title' => 'Site Name',
                'otp' => $otp,
                'name' => $user->name
            ]));

            return redirect()->route('password.reset')->with('success', 'Check your inbox for OTP');
        }

        // Show DB selection on forget password page
        $current_db = session('selected_db', $databases[0] ?? env('DB_DATABASE'));
        return view('frontend.authentication.forget-password', [
            'databases' => $databases,
            'current_db' => $current_db,
        ]);
    }

    
    public function resendOtp(Request $request)
    {
        $this->switchDbFromSession($request);

        $findUser = ForgetPassword::where('user_id', session('user_id'))
            ->where('email', session('reset-email'))
            ->first();

        if ($findUser) {
            $user = User::find(session('user_id'));
            $otp = rand(11111, 99999);

            $findUser->otp = $otp;
            $findUser->resent_count++;
            $findUser->suspend_duration = now()->addMinutes(5);
            $findUser->save();

            $mailData = [
                'title' => readConfig('site_name'),
                'otp' => $otp,
                'name' => $user->name,
            ];
            Mail::to($findUser->email)->send(new PasswordReset($mailData));

            return back()->with('success', 'Otp resent successfully');
        } else {
            return back()->with('error', 'Something went wrong');
        }
    }

    // Reset Password OTP
    public function resetPassword(Request $request)
    {
        $this->switchDbFromSession($request);

        if ($request->isMethod('post')) {
            $request->validate([
                'number_1' => 'required',
                'number_2' => 'required',
                'number_3' => 'required',
                'number_4' => 'required',
                'number_5' => 'required',
            ]);

            $otp = $request->number_1.$request->number_2.$request->number_3.$request->number_4.$request->number_5;

            $record = ForgetPassword::where('email', session('reset-email'))->where('otp', $otp)->first();
            if (!$record) {
                return back()->with('error', 'Invalid OTP');
            }

            if (now()->greaterThan($record->suspend_duration)) {
                return redirect()->route('login')->with('error', 'OTP expired');
            }

            $record->delete();
            session()->forget('reset-email');

            return redirect()->route('new.password');
        }

        return view('frontend.authentication.reset');
    }

    // New Password
    public function newPassword(Request $request)
    {
        $this->switchDbFromSession($request);

        if ($request->isMethod('post')) {
            $request->validate(['password' => 'required|confirmed|min:6']);
            $user = User::find(session('user_id'));

            if (!$user) {
                return redirect()->route('forget.password')->with('error', 'Something went wrong');
            }

            $user->password = bcrypt($request->password);
            $user->save();

            session()->forget('user_id');

            return redirect()->route('login')->with('success', 'Password reset successfully');
        }

        return view('frontend.authentication.new-password');
    }
    // Profile Update
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|string|confirmed|min:6',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        // Profile image upload
        if ($request->hasFile('profile_image')) {
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }
            $path = $request->file('profile_image')->store('profile', 'public');
            $user->profile_image = $path;
        }

        // Password change
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->back()->with('success', 'Profile updated successfully');
    }
}
