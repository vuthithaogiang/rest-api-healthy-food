<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    public function _construct() {
        $this->middleware('auth:api', ['except'=>['login', 'register']]);
    }

    public function register(Request $request) {

       $validator = Validator::make($request->all(),[
           'name' => 'required|string|min:3',
           'email' => 'required|string|email|unique:users',
           'password' => 'required|string|confirmed|min:8'
       ]);

       if($validator->fails()) {
             return response()->json($validator->errors(), 400);
       }

       $user = User::create(
             [   'name'=>$request->get('name'),
                 'email'=> $request->get('email'),
                 'password'=>bcrypt($request->get("password")),
                 'verify_token'=>Str::random(40)
             ]
       );

       Mail::send('emails.verify-account', compact('user'), function ($email) use($user) {
           $email->subject('Verify Account');
           $email->to($user->email);

       });

       return response()->json([
                'message'=> 'User register successfully!',
                'user' => $user
       ], 201);
    }

    public function verifyAccount($token) {

         $verify_token = $token;

         $user =  User::where('verify_token', $verify_token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token verify is not valid.'], 400);
        }

        $user->email_verified_at = now();
        $user->verify_token = null;
        $user->save();

//        return response()->json(['message' => 'Verify email successfully.']);
        return redirect('http://localhost:3000/login');
    }

    public function login(Request $request ) {
        $validator = Validator::make($request->all(),[
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if(!auth()->attempt($validator->validated())){
             return response()->json(['error'=>'Unauthorized'], 403);
        }

        if($token = auth()->attempt($validator->validated()) ){
            $user = User::where('email', $request->get("email"))->first();

            if($user->email_verified_at == null) {
                return response()->json(['error'=>'Your account not activate'], 400);
            }
        }

        return $this->createNewToken($token);
    }

    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }
    public function createNewToken($token){

         return response()->json([
             'access_token' => $token,
             'token_type' => 'bearer',
             'expires_in' =>  auth()->factory()->getTTL() * 60,
             'user' => auth()->user()
         ]);
    }

    public function profile() {

        try {
            $token = JWTAuth::parseToken();
            $payload = $token->getPayload();

            if ($payload->hasKey('exp') && $payload->get('exp') < time()) {
                return  response()->json([
                    'message' => 'Token has expired'
                ], 400);
            }
            return response()->json(auth()->user());

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            // Token has expired
            return  response()->json([
                'message' => 'Token has expired'
            ], 400);
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            // Token is invalid
            return  response()->json([
                'message' => 'Token has expired'
            ], 400);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            // Token not found in request
            return  response()->json([
                'message' => 'Token has expired'
            ], 400);
        }
    }
    public function  logout() {
        auth()->logout();

        return response()->json([
            'message'=> "User logout successfully."
        ], 201);
    }

    public function createOTP(Request $request){

        $user = User::where('email', $request->get("email"))->first();

        if(!$user) {
            return response()->json(['error'=>'Unauthorized'], 403);
        }
        else{
            if($user->email_verified_at == null) {
                return response()->json(['error'=>'Email is not activate'], 400);
            }

            if($user->remember_otp !== null) {
                $user->remember_otp = null;
                $user->save();
            }

            $otp = random_int(1000, 9999);
            $user->remember_otp = $otp;
            $user->save();

            Mail::send('emails.create-otp', compact(['otp', 'user']), function ($email) use($user) {
                $email->subject('Verify OTP');
                $email->to($user->email);

            });

            return response()->json([
                "message" => "We have e-mailed your password reset link!"
            ]);
        }

    }

     public function goToChangePassword() {
        return redirect('http://localhost:3000/fill-otp');
     }

    public function checkOTP(Request $request){

        $otp = $request->get('otp');

        $validator = Validator::make($request->all(),[
            'otp' => 'required|numeric|digits:4',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::where('remember_otp', $otp)->first();

        if(!$user) {
            return response()->json(['error'=>'Not true OTP'], 403);
        }

        return response()->json([
            'user' => $user,
            'otp' => $request->get('otp')
        ]);

    }

   public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(),[
            'otp' =>'required|numeric|digits:4|exists:users,remember_otp',
            'email' => 'required|email|exists:users,email',
            'new_password' => 'required|string|confirmed|min:8']);

       if($validator->fails()) {
           return response()->json($validator->errors(), 400);
       }
       $user = User::where('email', $request->get('email'))->first();

       $user->remember_otp = null;
       $user->password = bcrypt($request->get('new_password'));
       $user->save();

       return response()->json([
           'message' => "Reset password complete!"
       ], 201);

   }
}
