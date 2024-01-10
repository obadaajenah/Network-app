<?php

namespace App\Http\Controllers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;
 use Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
// use Illuminate\Contracts\Validation\Validator;
class LoginController extends Controller
{
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function register(Request $request){

        $validator = Validator::make($request->all(), [

            'name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],

        ]);
        if ($validator->fails()) {
            return response()->json([$validator->errors()], 401);
        }



        $data = [


            'name' => $request['name'],
            'email'     => $request['email'],
            'password'  => Hash::make($request['password']),


        ];

        $user = user::create($data);

        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 200);
    }

    public function login(Request $request){


        $credentials = request(['email', 'password']);
        $token = auth()->guard('api')->attempt($credentials);
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token){

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 20,
            'user' => auth('api')->user()
        ]);


    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }

    public function logout(){
        auth()->logout();

        return response()->json(['message' => 'logout successfully']);
    }


}
