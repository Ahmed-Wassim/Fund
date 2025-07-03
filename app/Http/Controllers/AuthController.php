<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|between:2,100',
            'email'         => 'required|string|email|max:100|unique:users',
            'password'      => 'required|string|min:6',
            'phone'         => 'nullable|string',
            'gender'        => 'nullable|in:male,female',
            'country'       => 'required|string|max:100',
            'birth_date'    => 'required|date|before:today',
            'type'          => 'required|in:investor,owner',
            'title'         => 'required_if:user_type,investor|string|max:100',
            'bio'           => 'required_if:user_type,investor|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $userData             = $request->only(['name', 'email', 'country', 'phone', 'gender', 'birth_date', 'type', 'title', 'bio']);
        $userData['password'] = Hash::make($request->password);

        if ($request->hasFile('profile_image')) {
            $userData['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user  = User::create($userData);
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User successfully registered',
            'user'    => $user,
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (! $token = JWTAuth::attempt($validator->validated())) {
            return response()->json(['error' => 'Invalid credentials'], 422);
        }

        return $this->createNewToken($token);
    }

    public function profile()
    {
        return response()->json(auth()->user());
    }

    public function updateProfile(Request $request)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'name'          => 'required|string|between:2,100',
            'email'         => 'required|string|email|max:100|unique:users',
            'phone'         => 'nullable|string',
            'gender'        => 'nullable|in:male,female',
            'country'       => 'nullable|string|max:100',
            'birth_date'    => 'nullable|date|before:today',
            'title'         => 'required_if:user_type,investor|string|max:100',
            'bio'           => 'required_if:user_type,investor|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = auth()->user();

        $user->update($request->all());

        return response()->json([
            'message' => 'User successfully updated',
            'user'    => $user,
        ], 200);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }

    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }

    protected function createNewToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth()->factory()->getTTL() * 60,
            'user'         => auth()->user(),
        ]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422); // 422 Unprocessable Entity
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

}
