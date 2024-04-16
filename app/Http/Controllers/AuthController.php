<?php

namespace App\Http\Controllers;

use App\Models\User;
use Flugg\Responder\Facades\Responder;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): SuccessResponseBuilder
    {
        
        $request->validate([
            'unmn' => 'required',
            'password' => Rule::requiredIf(function () use ($request) {
                return !($request->has('code') || $request->has("recovery_code"));
            }),
            'code' => Rule::requiredIf(function () use ($request) {
                return !($request->has('password') || $request->has("recovery_code"));
            }),
            'recovery_code' => Rule::requiredIf(function () use ($request) {
                return !($request->has('password') || $request->has("code"));
            }),
        ]);
      
        $u = strtolower($request->unmn);
      
        try {
            $user = User::where('unmn', $u)->first();
            throw_if(is_null($user), ValidationException::withMessages(["unmn" => "User is missing"]));
            return $this->authlo($user, $request->password, $request->get("code", null), $request->get("recovery_code", "null"));
        } catch (\Throwable $th) {
            if ($th instanceof ValidationException || env("APP_ENV", "production") == "local") {
                throw $th;
            }

            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
    }


    protected function authlo(User $user, $password, $code = null, $recovery_code = null)
    {


        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'unmn' => ['The provided credentials are incorrect.'],
            ]);
        }


        $u = $user->unmn;

        Cache::rememberForever($u, function () use ($user, $u) {
            return $user->createToken($u)->plainTextToken;
        });


        return responder()->success($user)->with(['address', 'organisation', 'token', 'license']);
    }
}
