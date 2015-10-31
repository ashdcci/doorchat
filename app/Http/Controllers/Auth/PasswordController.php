<?php namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\PasswordBroker;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Response;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Mail;

class PasswordController extends Controller {

  /*
  |--------------------------------------------------------------------------
  | Password Reset Controller
  |--------------------------------------------------------------------------
  |
  | This controller is responsible for handling password reset requests
  | and uses a simple trait to include this behavior. You're free to
  | explore this trait and override any methods you wish to tweak.
  |
  */

  use ResetsPasswords;

  /**
   * Create a new password controller instance.
   *
   * @param  \Illuminate\Contracts\Auth\Guard  $auth
   * @param  \Illuminate\Contracts\Auth\PasswordBroker  $passwords
   * @return void
   */
  public function __construct(Guard $auth, PasswordBroker $passwords)
  {
    $this->auth = $auth;
    $this->passwords = $passwords;

    $this->middleware('guest');
  }

    public function getEmail()
   {
       return view('auth.password')->with('tokan');;
   }

   
    public function postEmail(Request $request)
    {
    
        $this->validate($request, ['email' => 'required']);

        $response = Password::sendResetLink($request->only('email'), function($message)
        {
            $message->subject('Password Reminder');
        });

        switch ($response)
        {
            case Password::RESET_LINK_SENT:
                return redirect()->back()->with('status', trans($response));
            // return Response::json(array(
            //     'error'=>false,
            //     'content'=>trans($response),
            //     'email'=>$request->input('email')
            //     ),200);

            case Password::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans($response)]);
                //         return Response::json(array(
                // 'error'=>true,
                // 'content'=>trans($response),
                // 'email'=>$request->input('email')
                // ),200);

        }
      
   }


 /**
    * Get the e-mail subject line to be used for the reset link email.
    *
    * @return string
    */
   protected function getEmailSubject()
   {
       return isset($this->subject) ? $this->subject : 'Your Password Reset Link';
   }

   /**
    * Display the password reset view for the given token.
    *
    * @param  string  $token
    * @return \Illuminate\Http\Response
    */
   public function getReset($token = null)
   {
       if (is_null($token)) {
           throw new NotFoundHttpException;
       }

       return view('auth.reset')->with('token', $token);
   }

   /**
    * Reset the given user's password.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
   public function postReset(Request $request)
   {

       $this->validate($request, [
           'token' => 'required',
           'email' => 'required|email',
           'password' => 'required|confirmed',
       ]);

       $credentials = $request->only(
           'email', 'password', 'password_confirmation', 'token'
       );

       $response = Password::reset($credentials, function ($user, $password) {
           $this->resetPassword($user, $password);
       });


       switch ($response) {
           case Password::PASSWORD_RESET:
               return redirect()->back()->with('status', trans($response));
           default:
               return redirect()->back()
                           ->withInput($request->only('email'))
                           ->withErrors(['email' => trans($response)]);
       }
   }

   /**
    * Reset the given user's password.
    *
    * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
    * @param  string  $password
    * @return void
    */
   protected function resetPassword($user, $password)
   {
       $user->password = bcrypt($password);

       $user->save();

       Auth::login($user);
   }

   /**
    * Get the post register / login redirect path.
    *
    * @return string
    */
   public function redirectPath()
   {
       if (property_exists($this, 'redirectPath')) {
           return $this->redirectPath;
       }

       return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
   }

}
