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

    // Forget Password
    public function forgetPassword(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate([
                'email' => 'required|email',
            ]);

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

        return view('frontend.authentication.forget-password');
    }

    // Reset Password OTP
    public function resetPassword(Request $request)
    {
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
}
