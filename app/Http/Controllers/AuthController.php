<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            // Validate incoming request
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|min:8'
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors()
                ], 422);
            }
    
            // Get the validated credentials
            $validated = $validator->validated();
            $credentials = $request->only('email', 'password');
    
            if (Auth::attempt($credentials)) {
    
                $user = User::where('email', $validated['email'])->first()->toArray();
                $payload = [
                    'email' => $validated['email'],
                    'user_id' => $user['id'],
                    'timestamp' => now()
                ];
    
                $jwtToken = JWTAuth::claims($payload)->attempt($credentials);
                // dd($user);
                return response()->json([
                    'success' => true,
                    'message' => 'Logged in successfully',
                    'data' => [
                        'user' => $user,
                        'token' => $jwtToken,
                        'email' => $validated['email']
                    ]
                ]);
    
            } else {
                // If authentication failed
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.'
                ], 401);
            }
        } catch (Exception $e) {
            // Catch any other exceptions
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.'
            ], 500);
        }
    }
    



    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
                'phone' => 'required|digits:10',
                'is_seller' => 'required',
                'address' => 'required|string|max:500',
                'profileImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);
            // Check if validation fails
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Form validation failed.',
                    'errors' => $validator->errors()
                ], 200);
            }
            $validated = $validator->validated();
            // dd('register data', $validated);

            if ($request->hasFile('profileImage')) {
                $imagePath = $request->file('profileImage')->store('profile-images', 'public');
                $imageUrl = asset('storage/' . $imagePath);
            }

            // Save data to database
            $user = new User();
            $user->name = $validated['name'];
            $user->email = $validated['email'];
            $user->password = bcrypt($validated['password']);
            $user->phone = $validated['phone'];
            $user->address = $validated['address'];
            $user->is_seller = $validated['is_seller'];
            $user->profile = $imagePath;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Registered successfully.',
                'imageUrl' => $imageUrl
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Something went wrong.',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
