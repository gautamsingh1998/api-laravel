<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Validator;
use mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
//use Illuminate\Validation\Validator;
//use Illuminate\Support\Facades\Validator;


use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
   public function register(Request $request)
   {
    $validator = Validator::make($request->all(),[
        'name'=>'required|string|min:2|max:100',
        'email'=>'required|string|email|max:100|unique:users,email',
        'password'=>'required|string|min:6|confirmed'
    ]);
  

    if($validator->fails())
    {
        return response()->json($validator->errors()->toArray());
        
    }
   $user = User::create([
        'name'=>$request->name,
        'email'=>$request->email,
        'password'=>Hash::make($request->password),

    ]);
    return response()->json([
        'msg'=>'User Inserted Successfuly',
        'user'=>$user

    ]);
   }

   //Login Api method call

   public function login(Request $request)
   {
    $validator = validator::make($request->all(),[
        'email'=>'required|string|email',
        'password'=>'required|string|min:6'
    ]);

   
    if($validator->fails())
    {
        return response()->json($validator->errors()->toArray());
        
    }
    if(!$token = auth()->attempt($validator->validated()))
    {
        return response()->json(['success'=>false, 'msg'=>'Username & Password is incorrect']);
  
    }
    return $this->respondWithToken($token);
   }

   protected function respondWithToken($token)
   {
    return response()->json([
        'success' => true,
        'access_token' => $token,
        'token_type'=>'Bearer',
        'expires_in'=> auth()->factory()->getTTL()*60

    ]);

   }

   //Logout API Method
   public function logout()
   {
    try{
        auth()->logout();
        return response()->json(['success'=>true, 'msg'=>'User logged out']);
     } catch(\Exception $e){
        return response()->json(['success'=>false, 'msg'=>$e->getMessage()]);
    }
    
   }

   //Profile Method

   public function profile()
   {
    try{
        return response()->json(['success'=>true,'data'=>auth()->user()]);

    } catch(\Exception $e){
        return response()->json(['success'=>false, 'msg'=>$e->getMessage()]);
    }
   }

   //Update Profile Method

   public function updateProfile(Request $request)
   {
    
    if(auth()->user())
    {
        $validator = validator::make($request->all(),[
            'id'=>'required',
            'name'=>'required|string',
            'email'=>'required|email|string',

        ]);
      
    if($validator->fails())
    {
        return response()->json($validator->errors());
    }
    $user = User::find($request->id);
    $user->name = $request->name;
    $user->email = $request->email;
    $user->save();
    return response()->json(['success'=>true,'msg'=>'User Data','data'=>$user]);

    }
    else{
        return response()->json(['success'=>false,'msg'=>'User is not Authentication.']);

    }
   }


   public function sendVerifyMail($email)
   {
      if(auth()->user())
      {
        $user = User::where('email',$email)->get();
        if(count($user)>0)
        {
            
            

            $random = Str::random(40);
            $domain = URL::to('/');
            $url = $domain.'/'.$random;

            $data['url'] = $url;
            $data['email'] = $email;
            $data['title'] = "Email Verification";
            $data['body'] = "Please click here to below to verify your mail";

            Mail::send('verifyMail',['data'=>$data],function($message)use($data){
                $message->to($data['email'])->subject($data['title']);
            });

           $user = User::find($user[0]['id']);
           $user->remember_token = $random;
           $user->save();

           return response()->json(['success'=>true,'msg'=>'Mail sent successfully']);
        }
      }
      else
      {
        return response()->json(['success'=>false,'msg'=>'User is not Authentication.']);

      }
   }
}
