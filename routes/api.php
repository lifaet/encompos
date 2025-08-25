<?php
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/currencies', function () {
    return Currency::all();
});

Route::get('/currencies/default', function () {
    return Currency::where('active', true)->first();
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
