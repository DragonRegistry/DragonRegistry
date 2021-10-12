<?php

namespace App\Http\Controllers\Node;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class AuthController extends NodeController
{
    public function login(Request $request)
    {
        return $this->error('Not found', 404);
    }

    public function putUser(Request $request, $user)
    {
        $user = User::where('username', $request->name)->first();
        if (empty($user)) {
            $user = new User();
            $user->username = $request->name;
            $user->password = Hash::make($request->password);
            $user->save();
        } else {
            if (!Hash::check($request->password, $user->password))
                return $this->error('Bad username/password', 401);
        }
        if (!empty($request->email)) {
            $user->email = $request->email;
            $user->save();
        }
        return [
            'ok' => 'authenticated',
            'token' => explode("|", $user->createToken('npm')->plainTextToken)[1]
        ];
    }
}
