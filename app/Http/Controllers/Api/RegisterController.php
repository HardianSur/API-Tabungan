<?php

namespace App\Http\Controllers\Api;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TargetResource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse; // Added this line

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'name' => 'required',
            // 'email' => 'required|email',
            'password' => 'required|min:8',
            'c_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->plainTextToken;
        $success['name'] = $user->name;

        return new TargetResource(true, 'User berhasil ditambahkan', [$success,$user]);

    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
            $user = Auth::user();
            $success['token'] = $user->createToken( $user->username,['*'],now()->addMonth())->plainTextToken;
            $success['name'] = $user->username;

            return new TargetResource(true, 'User berhasil login', [$success, $user]);
        } else {
            return new TargetResource(false, 'Gagal', ['data' => 'password atau username salah']);
        }
    }

    public function logout(Request $request)
    {
        $token = explode('|', substr($request->header('Authorization'), 7))[0];

        PersonalAccessToken::where('id', $token)->delete();

        return new TargetResource(true, 'User berhasil logout', null);
    }
}
