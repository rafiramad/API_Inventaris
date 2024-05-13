<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\ApiFormatter;

class AuthController extends Controller
{
    public function __construct()
    {
        // middleware : membatasi, nama2 function yang hanya bisa diakses setelah login
        $this->middleware('auth:api', ['except' => ['login']]);
    }
    
    public function login(Request $request)
    {

        $this->validate($request, [
            'email' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return ApiFormatter::sendResponse(400, 'User not found', 'Silahkan cek kembali email dan password anda!');
        }

        $respondWithToken = [
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ];
        return ApiFormatter::sendResponse(200, 'Logged-in', $respondWithToken);
    }

    public function me()
    {
        return ApiFormatter::sendResponse(200, 'success', auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return ApiFormatter::sendResponse(200, 'success', 'Berhasil logout!');
    }
}
