<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }
    
    public function connect(){//子ユーザーコネクト作成
           define('CLIENT_ID', 'ca_FQxcDmLGpG0rI0FYZmty7MXO9W6cU0k8');//connectの設置ページにある
           define('TOKEN_URI', 'https://connect.stripe.com/oauth/token');
           define('AUTHORIZE_URI', 'https://connect.stripe.com/oauth/authorize');
           if (isset($_GET['code'])) { // Redirect/ code
             $code = $_GET['code'];
             $token_request_body = array(
               'client_secret' => env('STRIPE_SECRET'),
               'grant_type' => 'authorization_code',
               'client_id' => CLIENT_ID,
               'code' => $code,
             );
             $req = curl_init(TOKEN_URI);
             curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
             curl_setopt($req, CURLOPT_POST, true );
             curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));
             // TODO: Additional error handling
             $respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
             $resp = json_decode(curl_exec($req), true);
             curl_close($req);

             $id = Auth::id();//user_id取得
             \App\User::where('id', $id)->update(['stripe_user_id' => $resp['stripe_user_id']]);

             return redirect()->back();
           } else if (isset($_GET['error'])) { // Error
             echo $_GET['error_description'];
           } else { // Show OAuth link
             $authorize_request_body = array(
               'response_type' => 'code',
               'scope' => 'read_write',
               'client_id' => CLIENT_ID,
             );
             $url = AUTHORIZE_URI . '?' . http_build_query($authorize_request_body);

             return view('connect')->with('url',$url);
           }
    }
}
