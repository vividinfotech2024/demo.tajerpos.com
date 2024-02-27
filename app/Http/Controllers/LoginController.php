<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin\Store;
use App\Http\Controllers\CommonController;

class LoginController extends Controller
{
    protected $store_url;
    public function __construct() {
        $this->store_url = CommonController::storeURL();
    }

    public function showLogin()
    {
        $store_url = $this->store_url;
        $store_details = Store::select('store_background_image','store_logo','store_name')->where([
            ['store_url', '=', $this->store_url],
            ['is_deleted', '=', 0],
        ])->get();
        return view('store.login',compact('store_url','store_details'));
    }

    public function login(Request $request)
    {   
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        $input = $request->all();
        $store_details = Store::where([
            ['store_url', '=', $this->store_url],
            ['is_deleted', '=', 0],
        ])->get();
        if(!empty($store_details)) {
            $store_id = $store_details[0]['store_id'];
            $web_status = $store_details[0]['web_status'];
            $cashier_status = $store_details[0]['cashier_status'];
        } else {
            $store_id = $web_status = $cashier_status = 0;
        }
        if(auth()->attempt(array('email' => $input['email'], 'password' => $input['password'], 'store_id' => $store_id)))
        {
            if (($web_status == 1 && (auth()->user()->is_admin == 2)) || (auth()->user()->is_admin == 3 && $cashier_status == 1)) 
                return redirect()->route(config('app.prefix_url').'.'.$this->store_url.'.'.config('app.module_prefix_url').'.home');
            else
                return redirect()->route(config('app.prefix_url').'.'.$this->store_url)->withErrors(['email' => "You don't have access."]);
        }else
            return redirect()->route(config('app.prefix_url').'.'.$this->store_url)->withErrors(['email' => 'Email-Address And Password Are Wrong.']);
    }
}
