<?php

namespace App\Http\Controllers\CashierAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin\Store;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Http\Requests\CashierAdmin\Cashier;
use URL;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Http\Requests\Admin\ChangePassword;
use Illuminate\Support\Facades\Hash;
use App\Models\StoreAdmin\Product;
use App\Models\Customers\User as Customer;
use App\Models\CashierAdmin\Order;
use App\Http\Controllers\CommonController;
use Image;
use App\Models\State;
use App\Models\Cities;
use App\Models\Country;

class CashierController extends Controller
{
    protected $store_url;
    protected $store_logo;
    public function __construct() {
        $this->store_url = CommonController::storeURL();
        $this->store_logo = CommonController::storeLogo();
    }

    public function showLogin()
    {
        $store_url = $this->store_url;
        return view('cashier_admin.login',compact('store_url'));
    }

    public function login(Request $request)
    {   
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
        ], 
        [
            'email.required' => 'The user name field is required.',
            'email.email' => 'The user name field is invalid.'
        ]);
        $input = $request->all();
        $store_details = Store::where('store_url',$this->store_url)->get('store_id');
        $store_id = (!empty($store_details)) ? $store_details[0]['store_id'] : 0;
        if(auth()->attempt(array('email' => $input['email'], 'password' => $input['password'], 'store_id' => $store_id)))
        {
            if (auth()->user()->is_admin == 3) 
                return redirect()->route(config('app.prefix_url').'.'.$this->store_url.'.'.config('app.module_prefix_url').'.home');
            else
                return redirect()->route('home');
        }else
            return redirect()->route(config('app.prefix_url').'.'.$this->store_url.'.cashier-login')->withErrors(['email' => 'Email-Address And Password Are Wrong.']);
    }

    public function dashboard()
    {
        $store_url = $this->store_url;
        $store_logo = $this->store_logo;
        $data['total_product_count'] = Product::select('product_id')->where([
            ['store_id', '=', Auth::user()->store_id],
            ['status', '=', '1'],
            ['is_deleted','=','0']
        ])->get()->count();
        $data['total_revenue'] = Order::where([
            ['store_id', '=', Auth::user()->store_id],
            ['status', '=', '1'],
            ['is_deleted','=','0']
        ])->sum('total_amount');
        $data['total_customer_count'] = Customer::select('customer_id')->where([
            ['store_id', '=', Auth::user()->store_id],
            ['status', '=', '1']
        ])->get()->count();
        return view('cashier_admin.dashboard',compact('store_url','data','store_logo'));
    }

    public function logout(Request $request)
    {
        Session::flush();
        Auth::logout();
        return redirect()->route(config('app.prefix_url').'.'.$this->store_url);
    }

    public function editProfile() {
        $store_url = $this->store_url;
        $store_logo = $this->store_logo;
        $cashier_admin_details = User::select('id','name','email','company_name','phone_number','address','city_id','state_id','country_id','postal_code','profile_image','street_name','building_name',DB::raw('(SELECT company_logo FROM module_logos WHERE module_name = "admin" AND is_deleted = 0) as company_logo'))->where('id',Auth::user()->id)->get();
        $countries = Country::get(['id','name']);
        return view('cashier_admin.profile',compact('cashier_admin_details','store_url','store_logo','countries'));
    }

    public function updateProfile(Cashier $request) {
        try {
            if (Auth::user()->is_admin == 2) 
                $input = $request->except('_token','user_id','store_id','remove_image','email','name','phone_number','building_name','street_name','profile_image');
            $user_details = $request->except('_token','user_id','store_id','remove_image','email','store_name','store_country','store_state','store_city','store_postal_code');
            $user_id = Crypt::decrypt($request->user_id);
            $store_id = Auth::user()->store_id;
            $url = URL::to("/");
            //Profile Image
            if ($request->hasFile('profile_image')) {
                $image = $request->file('profile_image');
                $profileImage = date('YmdHis') . "." . $image->extension();
                $destinationPath = base_path().'/images/';
                if (!file_exists($destinationPath)) 
                    mkdir($destinationPath, 0777, true);
                $destinationPath = base_path().'/images/'.$store_id;
                if (!file_exists($destinationPath)) 
                    mkdir($destinationPath, 0777, true);
                $destinationPath = base_path().'/images/'.$store_id.'/profile';
                if (!file_exists($destinationPath)) 
                    mkdir($destinationPath, 0777, true);
                $user_details['profile_image'] = $url.'/images/'.$store_id.'/profile'.'/'.$profileImage;
                $img = Image::make($image->path());
                $img->resize(100, 100, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath.'/'.$profileImage);
            } else {
                if($request->remove_image == 1)
                    $user_details['profile_image'] = '';
            }
            DB::beginTransaction();
            if (Auth::user()->is_admin == 2)  {
                $input['store_user_name'] = $request->name;
                $input['store_phone_number'] = $request->phone_number;
                $user_details['updated_by'] = $input['updated_by'] = Auth::user()->id;
                Store::where('store_id',$store_id)->update($input); 
                $user_details['company_name'] = $request->store_name;
                $user_details['country_id'] = $request->store_country; 
                $user_details['state_id'] = $request->store_state;
                $user_details['city_id'] = $request->store_city;
                $user_details['postal_code'] = $request->store_postal_code;
            }
            User::where('id',$user_id)->update($user_details);
            DB::commit();
            return redirect()->route(config('app.prefix_url').'.'.$this->store_url.'.'.config('app.module_prefix_url').'.home')->with('message',trans('store-admin.profile_success_msg'));
        } catch (Exception $e) {
            //Rollback Database Entry
            DB::rollback();
            throw $e;
        }
    }

    public function changePassword() {
        $store_url = $this->store_url;
        $store_logo = $this->store_logo;
        return view('cashier_admin.change_password',compact('store_url','store_logo'));
    }

    public function updatePassword(ChangePassword $request) {
        if (!(Hash::check($request->current_password, Auth::user()->password))) 
            return redirect()->back()->with("error",trans('store-admin.current_pwd_err'));
        // Current password and new password same
        if(strcmp($request->current_password, $request->new_password) == 0)
            return redirect()->back()->with("error",trans('store-admin.new_pwd_err'));
        $user = Auth::user();  
        $user->plain_password = encrypt($request->new_password);
        $user->password = Hash::make($request->new_password);
        $user->save();
        return redirect()->back()->with("message",trans('store-admin.pwd_update_message'));
    }

    public function index()
    {
        //
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}
