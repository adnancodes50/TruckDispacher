<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AppAuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json([
                'status' => false,
                'message' => 'User not found',
            ], 404);
        }

        // admin not allowed
        if ($user->role === 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Admin cannot login',
            ], 403);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid password',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // base response
        $data = [
            'id' => $user->id,
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,

            'email' => $user->email,
            'role' => $user->role,
        ];

        // broker response
        if ($user->role === 'broker') {
            // $data['id'] = $user->id;
            $data['name'] = $user->full_name;
            $data['company_name'] = $user->company_name;
        }

        // driver response
        if ($user->role === 'driver') {
            $data['name'] = $user->full_name;
            $data['truck_info'] = $user->truck_info;
        }

        return response()->json($data);
    }

    // broker registerration api method

   public function registerBroker(Request $request)
{
    $validator = Validator::make($request->all(), [
        'full_name' => 'required|string|max:255',
        'company_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:20',
        'password' => 'required|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors(),
        ], 422);
    }

    $user = User::create([
        'role' => 'broker',
        'full_name' => $request->full_name,
        'company_name' => $request->company_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Registered successfully',
        'data' => $user
    ]);
}

    public function registerDriver(Request $request)
{
    $validator = Validator::make($request->all(), [
        'full_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|string|max:20',
        'password' => 'required|min:6|confirmed',
        'license_number' => 'required|string|max:255',

        // OPTIONAL FIELDS 👇
        'truck_info' => 'nullable|string|max:255',
        'truck_type' => 'nullable|string|max:255',
        'truck_plate' => 'nullable|string|max:255',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => $validator->errors(),
        ], 422);
    }

    $driver = User::create([
        'role' => 'driver',
        'full_name' => $request->full_name,
        'email' => $request->email,
        'phone' => $request->phone,
        'password' => Hash::make($request->password),
        'license_number' => $request->license_number,

        // OPTIONAL (will store null if not provided)
        'truck_info' => $request->truck_info,
        'truck_type' => $request->truck_type,
        'truck_plate' => $request->truck_plate,
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Driver registered successfully',
        'email' => $driver->email,
        'role' => $driver->role,
        'name' => $driver->full_name,
        'truck_info' => $driver->truck_info,
    ]);
}

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ]);
    }
}
