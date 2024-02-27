<?php

namespace App\Http\Controllers\StoreAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAdmin\ShippingRequest;
use Illuminate\Support\Facades\Crypt;
use App\Models\StoreAdmin\Shipping;
use Illuminate\Support\Facades\Auth;
use Exception;
use App\Http\Controllers\CommonController;

class ShippingController extends Controller
{
    protected $store_url;
    public function __construct() {
        $this->store_url = CommonController::storeURL();
    }

    public function index()
    {
        //
    }

    public function create()
    {
        $store_url = $this->store_url; 
        $shipping_details = Shipping::where([
            ['store_id', '=', Auth::user()->store_id],
            ['created_by', '=', Auth::user()->id]
        ])->get(['shipping_method', 'flat_rate', 'shipping_cost','shipping_id']);
        $mode = (count($shipping_details) > 0) ? 'edit' : 'add';
        $user_role_id = Auth::user()->is_admin;
        return view('store_admin.shipping.create',compact('store_url','shipping_details','mode','user_role_id'));
    }

    public function store(Request $request)
    {
        try {
            $mode = $request->mode;
            $input = $request->all();
            $shipping_id = ($mode == "edit") ? Crypt::decrypt($request->shipping_id) : 0;
            if($mode == "edit") {
                //Reset the input values
                $remove_array_values = array('_token','mode','shipping_id');
                foreach($remove_array_values as $value) {
                    unset($input[$value]);
                }
                $input['updated_by'] = Auth::user()->id;
                Shipping::where('shipping_id',$shipping_id)->update($input);
            } else {
                $input['created_by'] = Auth::user()->id;
                $input['store_id'] = Auth::user()->store_id;
                Shipping::create($input);
            }
            $success_message = ($mode == "edit") ? "Shipping details updated successfully" : "Shipping details added successfully.";
            $prefix_url = config('app.module_prefix_url');
            return redirect()->route(config('app.prefix_url').'.'.$this->store_url.'.'.$prefix_url.'.shipping.create')->with('message',$success_message);
        } catch (Exception $e) {
            throw $e;
        }
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
