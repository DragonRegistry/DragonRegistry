<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends ApiController
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails())
            return $this->error('Validation error', $validator->errors());
        if (!Auth::attempt(['username' => $request->get('username'), 'password' => $request->get('password')]))
            return $this->error('Unknown user', 401);
        return $this->success('User authorized', 200, [
            'token' => explode("|", Auth::user()->createToken($request->get('reference', 'default'))->plainTextToken)[1]
        ]);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'password' => 'required',
            'confirm_password' => 'same:password' // Don't require this, only if they want to do it themself
        ]);
        if ($validator->fails())
            return $this->error('Validation error', 400, $validator->errors());
        $user = User::create([
            'username' => $request->get('username'),
            'password' => Hash::make($request->get('password'))
        ]);
        return $this->success('User registered', 200, [
            'token' => explode("|", $user->createToken($request->get('reference', 'default'))->plainTextToken)[1]
        ]);
    }
}
