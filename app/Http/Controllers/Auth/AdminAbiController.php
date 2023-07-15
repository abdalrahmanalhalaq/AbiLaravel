<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\AdminResetPasswordEmail;
use App\Models\Admin;
use Dotenv\Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AdminAbiController extends Controller
{
    //
    public function login( Request $request)
    {
        $validator = Validator($request->all() , [
            'email'=>'required|string|exists:admins,email', // exists -  لانه موجود مش فريد زي الي تحت جاي يسجل اما هاد جاي وبياناته نوجودة
            'password'=>'required', // وهان ما حطينا رولز لانه بياناته موجودة ضمنياً
        ]);
        if (! $validator->fails()) {
            $admin = Admin::where('email' , '=' , $request->input('email'))->first();
            if(Hash::check($request->input('password') ,$admin->password)){
                $token = $admin->createToken('abi-token');//token مميزة للمستحدم المسجل دخول -- 'token'=>$token رجع اوبجيكت للي تخزن في الداتا بيز مع متغير التويكن
                $admin->token = $token->accessToken; //خزنلي في اوبجكت الادمن هاد الحقل
                return new response(['status'=>true , 'message'=>'Login Successfully' ,'token'=>$admin]  , Response::HTTP_OK);
            }else{
            return new Response(['status'=>false , 'message'=>'Failed to Login , Password wrong'] , Response::HTTP_BAD_REQUEST);
            }
        }else{
            return new Response(['status'=>false , 'message'=>$validator->getMessageBag()->first()] , Response::HTTP_BAD_REQUEST);
        }
    }
    //
    public function logout( Request $request)
    {
        $user = $request->user('admins-api');
        $revoked = $user->token()->revoke();
        return Response(['status'=>$revoked, 'message'=>$revoked ? 'Logout Successfully' : 'Failed to Logout']);
    }
    //



    
    public function register(Request $request)
    {
        $validator = Validator($request->all() , [
            'name'=>'required|string|min:3|max:20',
            'email'=>'required|string|unique:admins,email',
            'password'=>['required' , Password::min(3)->letters()->uncompromised()->symbols()->mixedCase()],
        ]);
        if (!$validator->fails()) {
            $admin = new Admin();
            $admin->name = $request->input('name');
            $admin->email = $request->input('email');
            $admin->password = Hash::make($request->input('password'));
            $saved = $admin->save();
            if($admin->save()){
                $role = Role::findOrFail(1);
                $admin->assignRole($role);
            }
            return response(['status'=>$saved , 'message'=>$saved ? 'Register Successfully' : 'Register Failed'] , $saved ?  Response::HTTP_OK : Response::HTTP_BAD_REQUEST );
        }else{
            return Response()->Json(['status'=>false , 'message'=>$validator->getMessageBag()->first()] , Response::HTTP_BAD_REQUEST);
        }
    }
    //
    public function changePassword(Request $request)
    {
                /*
            * 1- must send token in headers (Authorization)
            * 2- must send following data in body:
                    -old password
                    -new password
                    -new password confirmation
            */
        /** */  //slash and two stars
         $validator = Validator($request->all() , [
            'current_password'=>'required|current_password:admins-api',
            'new_password'=>['required', 'confirmed', Password::min(3)->letters()->uncompromised()->symbols()->mixedCase()],
            'new_password_confirmation'=>'required',
         ]);
         if(!$validator->fails()){
                //**هيجيب من الريكويس التوكن الخاص اليوزر المسجل دخول في الجارد حالياَ */
            $user = $request->user('admins-api');
            $user->password = Hash::make($request->input('new_password'));
            $saved = $user->save();
            return response(['status'=>$saved , 'message'=>$saved ? 'Updated Password Successfuly' : 'Failed to Update Password'] , $saved ?  Response::HTTP_OK : Response::HTTP_BAD_REQUEST );

         }else{
            return new Response(['status'=>false , 'message'=>$validator->getMessageBag()->first()] , Response::HTTP_BAD_REQUEST);
         }
    }
    //
    public function forgetPassowrd(Request $request){
        $validator = Validator($request->all() , [
            'email'=>'required|email|exists:admins,email',
        ]);
        if(! $validator->fails()){
            $user = Admin::where('email' , '=' , $request->input('email'))->first();  // هوا هاد اينميل المستخدم الذي يريد تنفيذ العملية وهل هو موجود؟
            //generate reset code & save
          $ResentCode =  random_int(1000 , 9999);  //إذا انت ايعتبي كود الاستعادة اللهي ارسلوه على ايميلك لما كنت ناسي الباسسورد
          $user->reset_code = Hash::make($ResentCode);
           // $user->\\\\\\\\\\    email = $request->input('email');
            Mail::to($user)->send(new AdminResetPasswordEmail($user ,$ResentCode)); // $user -> بدي ارسله اوبجكت اليوزر كله
            $saved = $user->save();
            return new Response(['status'=>$saved , 'message'=>$saved ? 'Updated Password Successfuly' : 'Failed to Update Password'] , $saved ?  Response::HTTP_OK : Response::HTTP_BAD_REQUEST );

        }else{
            return new Response(['status'=>false , 'message'=>$validator->getMessageBag()->first()] , Response::HTTP_BAD_REQUEST);
        }
    }
    public function resetPassword(Request $request ){
          $validator = Validator($request->all() , [
            'email'=>'required|email|exists:admins,email',
            'reset_code'=>'required|numeric|digits:4',
            'new_password'=>['required', 'confirmed', Password::min(3)->letters()->uncompromised()->symbols()->mixedCase()],
          ]);
          if (! $validator->fails()) {
            $admin  = Admin::where('email' , '=' , $request->input('email'))->first();
            if (! is_null($admin->reset_code)) {
                if(Hash::check($request->input('reset_code')  , $admin->reset_code)){
                    $admin->password = Hash::make($request->new_password);
                    $admin->reset_code = null;
                    $saved  =  $admin->save();
                return new Response(['status'=>$saved , 'message'=>$saved ? 'Updated Password Successfuly' : 'Failed to Update Password'] , $saved ?  Response::HTTP_OK : Response::HTTP_BAD_REQUEST );
                }else{
            return new Response(['status'=>false , 'message'=>'code reset is false try agin!!'] , Response::HTTP_BAD_REQUEST);
                }
            }else{
            return new Response(['status'=>false , 'message'=>'reset code is not exist'] , Response::HTTP_BAD_REQUEST);
            }

          }
    }
}
?>
