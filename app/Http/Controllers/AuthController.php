<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
            'password' => 'required|string|min:8'
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
                return response()->json(['error'=>'Unauthorized'], 403);
            }
        }

        return $this->createNewToken($token);
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
        return response()->json(auth()->user());
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

            $user->remember_otp = random_int(1000, 9999);
            $user->save();

            return response()->json([
                'email'=>$request->get('email'),
                'remember_otp' => $user->remember_otp
            ]);
        }

    }

   public function resetPassword(Request $request) {
        $otp = $request->get('otp');

        if(!$otp) {
            return response()->json(['error'=>'Not found OTP code'], 400);
        }

        $user = User::where('remember_otp', $otp)->first();

        if(!$user) {
            return response()->json(['error'=>'OTP not true'], 400);
        }

        $validator = Validator::make($request->all(),[
            'new_password' => 'required|string|confirmed|min:8'
        ]);

       if($validator->fails()) {
           return response()->json($validator->errors(), 400);
       }

       $user->remember_otp = null;
       $user->password = bcrypt($request->get('new_password'));
       $user->save();

       return response()->json([
           'message' => "Reset password complete!"
       ], 201);

   }
}
