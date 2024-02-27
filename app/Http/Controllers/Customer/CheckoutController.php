<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreAdmin\Product;
use App\Http\Controllers\CommonController;
use Session;
use App\Models\Country;
use App\Models\Customers\Address;
use Illuminate\Support\Facades\Auth;
use App\Models\Customers\ShippingAddress;
use Illuminate\Support\Facades\Crypt;
use App\Models\CashierAdmin\OnlineStoreOrder;
use App\Models\CashierAdmin\OnlineStoreOrderItems;
use App\Models\CashierAdmin\OnlinePayment;
use App\Models\StoreAdmin\VariantsOptionCombination;
use App\Models\CashierAdmin\OnlineOrderStatus;
use DB;
use Carbon\Carbon;
use App\Models\StoreAdmin\Tax;

class CheckoutController extends Controller
{

    protected $store_url;
    protected $redirect_url;
    protected $store_logo;
    public function __construct() {
        $current_url = request()->path();
        $split_url = explode("/",$current_url);
        $split_url_index = config('app.split_url_index');
        $this->store_url = (!empty($split_url)) ?$split_url[$split_url_index] : '';
        $this->store_logo = CommonController::storeLogo();
    }

    public function productCheckout() {
        $store_url = $this->store_url;
        $store_id = CommonController::get_store_id();
        $customer_id = (session()->has('authenticate_user')) ? session('authenticate_user')->customer_id : Auth::guard('customer')->user()->customer_id;
        $cart_data = session()->get('cart', []);
        $all_variant_id = []; $get_quantity = []; $product_ids = []; $product_details = [];$quantity = [];
        if(!empty($cart_data)) {
            foreach($cart_data as $k => $product) {
                $product_ids[] = $k;
                if(!empty($product)) {
                    foreach($product as $key => $val) {
                        if(is_array($val)) {
                            $quantity[$k] = count($product);
                            $all_variant_id[] = $key;
                            $get_quantity[$k][$key] = $val['quantity'];
                        } else {
                            $get_quantity[$k] = $product['quantity'];
                        }
                    }
                }
            }
        }
        if(!empty($product_ids)) {
            $productQuery = Product::leftJoin('store_category', 'store_products.category_id', '=', 'store_category.category_id')
                ->leftJoin('store_sub_category', 'store_products.sub_category_id', '=', 'store_sub_category.sub_category_id')
                ->leftJoin('store_product_tax', 'store_products.product_id', '=', 'store_product_tax.product_id')
                ->leftJoin('store_price', 'store_products.product_id', '=', 'store_price.product_id')
                ->leftJoin('store_product_variants_combination', function ($join) use ($all_variant_id) {
                    $join->on('store_products.product_id', '=', 'store_product_variants_combination.product_id')
                        ->whereIn('store_product_variants_combination.variants_combination_id', $all_variant_id);
                })
                ->where([
                    ['store_products.store_id', '=', $store_id],
                    ['store_products.is_deleted', '=', 0],
                    ['store_products.status_type', '=', 'publish'],
                    ['store_products.status', '=', 1],
                    ['store_category.is_deleted', '=', 0],
                    ['store_category.status', '=', 1],
                ])
                ->whereRaw('case WHEN (store_products.sub_category_id > 0) THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 ELSE TRUE END')
                ->whereRaw('case WHEN (type_of_product = "single" AND trackable = 1) THEN store_products.unit > 0 ELSE TRUE END')
                ->whereIn('store_products.product_id', $product_ids)
                ->orderBy('store_products.category_id', 'desc')
                ->select('product_name', 'store_products.category_id', 'category_name', 'price', 'store_products.product_id', 'store_products.category_image', 'tax_type', 'tax_amount', 'taxable', 'type_of_product', 'unit', 'trackable', 'variants_combination_id', 'variants_combination_name', 'variant_price', 'on_hand')
                ->selectRaw('CASE WHEN (on_hand <= 0 AND on_hand IS NOT NULL AND on_hand != "") THEN "out-of-stock" ELSE "" END as product_available');
                // ->selectRaw("CASE WHEN taxable = 0 AND tax_type = 'incl_of_tax' THEN price + (price * tax_amount / 100) ELSE price END AS price");
            $product_details = $productQuery->get();
            if(!empty($product_details)) {
                $product_array = $product_details->toArray();
                if(!empty($product_array) && !empty($cart_data)) {
                    $cart = array_filter($cart_data, function ($cart_item) use ($product_array) {
                        foreach($cart_item as $item) {
                            if (isset($item['variants_combination_id']) && isset($item['product_id'])) {
                                foreach ($product_array as $product) {
                                    if ($product['variants_combination_id'] == $item['variants_combination_id']) {
                                        return true;
                                    }
                                }
                                return false;
                            }elseif (isset($cart_item['product_id'])) {
                                foreach ($product_array as $product) {
                                    if ($product['product_id'] == $cart_item['product_id']) {
                                        return true;
                                    }
                                }
                                return false;
                            }
                            return true;
                        }
                    });
                    session()->put('cart', $cart);
                    $cart_data = session()->get('cart', []);
                    $total_quantity = 0;
                    foreach ($cart_data as $key => $cart) {
                        if (isset($cart['quantity'])) {
                            $total_quantity += $cart['quantity'];
                        } else {
                            foreach ($cart as $variant) {
                                $total_quantity += $variant['quantity'];
                            }
                        }
                    }
                    session()->put('cart_total_quantity', $total_quantity);
                }
            }
        }
        $address_details = Address::leftJoin('countries', 'customer_address.country_id', '=', 'countries.id')->leftJoin('states', 'customer_address.state_id', '=', 'states.id')->leftJoin('cities', 'customer_address.city_id', '=', 'cities.id')->where([
            ['store_id', '=', $store_id],
            ['customer_id', '=', $customer_id]
        ])->get(['address_id','customer_name','mobile_number','street_name','building_name','customer_address.country_id','customer_address.state_id','customer_address.city_id','pincode','address_type','landmark','countries.name as country_name','states.name as state_name','cities.name as city_name','email_address']);
        $countries = Country::get(['id','name']);
        $tax_details = Tax::where('store_id',$store_id)->get(['tax_percentage','tax_id'])->toArray();
        return view('customer.checkout', compact('store_url','store_id','product_details','quantity','cart_data','get_quantity','countries','address_details','tax_details'));
    }

 

    public function placeorder(Request $request) {
        $address_id = (!empty($request->address_id)) ? $request->address_id : 0;
        $store_id = CommonController::get_store_id();
        $cart_data = $request->cart_data;
        $store_url = $this->store_url;
        $customer_id = (session()->has('authenticate_user')) ? session('authenticate_user')->customer_id : Auth::guard('customer')->user()->customer_id;
        if(!empty($cart_data)) {
            $store_order_status = OnlineOrderStatus::select('order_status_id')->where([
                ['store_id', '=', $store_id],
                ['is_deleted', '=', 0]
            ])->orderBy('order_number','asc')->limit(1)->get()->toArray();
            $status_id = !empty($store_order_status) ? $store_order_status[0]['order_status_id'] : 0;
            $placeorder_data = array_filter(json_decode($cart_data));
            $subtotal = 0; $totalAmount = 0; $totalTaxAmount = 0; $no_of_products = 0;
            if(!empty($placeorder_data)) {
                $online_order = [];
                $online_order['customer_id'] = $customer_id;
                $online_order['store_id'] = $store_id;
                $online_order['address_id'] = $address_id;
                foreach ($placeorder_data as $item) {
                    $no_of_products += count((array)$item);
                    foreach ($item as $variant) {
                        $quantity = $variant->quantity;
                        $productPrice = $variant->product_price;
                        $taxAmount = $variant->tax_amount; 
                        $subtotal += ($quantity * $productPrice) - $taxAmount;
                        $totalAmount += ($quantity * $productPrice);
                        $totalTaxAmount += $taxAmount;
                    }
                }
                $online_order['sub_total_amount'] = $subtotal;
                $online_order['total_amount'] = $totalAmount;
                $online_order['tax_amount'] = $totalTaxAmount;
                $online_order['discount_amount'] = $request->discount_amount;
                $online_order['discount_id'] = $request->discount_id;
                $online_order['no_of_products'] = $no_of_products;
                $online_order['online_order_status'] = $status_id;
                $online_order['created_by'] = $customer_id;
                $online_order['updated_by'] = $customer_id;
                $order_id = OnlineStoreOrder::create($online_order)->online_order_id;
                $insert_payment = [];
                $insert_payment['order_id'] = $order_id;
                // $insert_payment['payment_method'] = !empty($request->payment_method) ? $request->payment_method : 'cash';
                $insert_payment['amount'] = $totalAmount;
                $insert_payment['created_by'] = $customer_id;
                $insert_payment['customer_id'] = $customer_id;
                $insert_payment['store_id'] = $store_id;
                $payment_id = OnlinePayment::create($insert_payment)->online_payment_id;
                $update_order = array();
                $update_order['order_number'] = "ORDER".sprintf("%03d",$order_id);
                $update_order['payment_id'] = $payment_id;
                OnlineStoreOrder::where('online_order_id',$order_id)->update($update_order);
                foreach ($placeorder_data as $product_id => $item) {
                    foreach ($item as $variant) {
                        $product_variants = [];
                        $product_variants['store_id'] = $store_id;
                        $product_variants['customer_id'] = $customer_id;
                        $product_variants['order_id'] = $order_id;
                        $product_variants['product_id'] = $product_id;
                        $product_variants['variants_id'] = ($variant->variants_id != "") ? $variant->variants_id : 0;
                        $product_variants['product_variants'] = ($variant->variant_combination_name != "-" && $variant->variant_combination_name != "") ? $variant->variant_combination_name : "";
                        $product_variants['quantity'] = $variant->quantity;
                        $product_variants['sub_total'] = $variant->quantity * $variant->product_price;
                        $product_variants['tax_amount'] = $variant->tax_amount;
                        $product_variants['created_by'] = $customer_id;
                        $product_variants['updated_by'] = $customer_id;
                        $online_order_items_id = OnlineStoreOrderItems::create($product_variants)->online_order_items_id;
                        if(!empty($variant->variants_id)) {
                            $product_details = VariantsOptionCombination::where([
                                ['store_id', '=', $store_id],
                                ['variants_combination_id', '=', $variant->variants_id]
                            ])->get(['on_hand']);
                            $get_unit = (!empty($product_details) && !empty($product_details[0]['on_hand'])) ? $product_details[0]['on_hand'] : 0;
                            $unit = $get_unit - $variant->quantity;
                            $update_product = array();
                            $update_product['on_hand'] = ($unit > 0) ? $unit : 0;
                            VariantsOptionCombination::where('variants_combination_id',$variant->variants_id)->update($update_product);
                        } else {
                            $product_details = Product::where([
                                ['store_id', '=', $store_id],
                                ['product_id', '=', $product_id]
                            ])->get(['unit']);
                            $get_unit = !empty($product_details) ? $product_details[0]['unit'] : 0;
                            $unit = $get_unit - $variant->quantity;
                            $update_product = array();
                            $update_product['unit'] = ($unit > 0) ? $unit : 0;
                            Product::where('product_id',$product_id)->update($update_product);
                        }
                        
                    }
                }
            }
        }
        Session::forget('cart');
        Session::forget('cart_total_quantity');
        
        $cus_name = Auth::guard('customer')->user()->customer_name;
        $cus_phone = Auth::guard('customer')->user()->customer_phone_number; 
        $cus_email = Auth::guard('customer')->user()->customer_email; 
        
        $cus_details = DB::table('instore_customers')
        ->select('instore_customers.*')
        ->where('customer_id',$customer_id)
        ->first();


        $cus_address = DB::table('customer_address')
        ->select('customer_address.*','countries.name as country_name','countries.currency','countries.iso2','states.name as state_name','cities.name as city_name')
        ->join('countries','countries.id','=','customer_address.country_id')
        ->join('states','states.id','=','customer_address.state_id')
        ->join('cities','cities.id','=','customer_address.city_id')
        ->where('store_id',$store_id)
        ->where('customer_id',$customer_id)
        ->where('address_id',$address_id)
        ->first();


        $gateway = DB::table('store_payment_credentials')
        ->select('*')
        ->where('store_id',$store_id) //as of now we are hide for payment using all Stores
        ->where('status',1) 
        ->where('is_deleted',0) 
        ->first();

        $cart_id = "CART_".$order_id;   
        $ord_id = Crypt::encrypt($order_id);

        $redirect = url($store_url . '/customer/payment/response', ['id' =>$ord_id]);
        $redirect  = str_replace("https","http",$redirect);


        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://secure.paytabs.sa/payment/request',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "profile_id": "'.$gateway->client_id.'",
            "tran_type": "sale",
            "tran_class": "ecom",
            "cart_id": "'.$cart_id.'",
            "cart_currency": "SAR",
            "cart_amount": "'.$totalAmount.'",
            "cart_description": "Online Order Payment",
            "paypage_lang": "en",
            "customer_details": {
                "name": "'.$cus_name.'",
                "email": "'.$cus_details->email.'",
                "phone": "'.$cus_phone.'",
                "street1": "'.$cus_address->street_name.'",
                "city": "'.$cus_address->state_name.'",
                "state": "'.$cus_address->state_name.'",
                "country": "'.$cus_address->iso2.'",
                "zip": "'.$cus_address->pincode.'"
            },
            "shipping_details": {
                "name": "'.$cus_name.'",
                "email": "'.$cus_details->email.'",
                "phone": "'.$cus_phone.'",
                "street1": "'.$cus_address->street_name.'",
                "city": "'.$cus_address->state_name.'",
                "state": "'.$cus_address->state_name.'",
                "country": "'.$cus_address->iso2.'",
                "zip": "'.$cus_address->pincode.'"
            },
            "callback": "'.$redirect.'",
            "return": "'.$redirect.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Authorization: SRJNHD96GB-JHJMMTKJ6B-TK22TNRRKJ'
          ),
        ));
        
        $response = curl_exec($curl);
        curl_close($curl);  

        $result = json_decode($response,TRUE);
        // Add Trans History
        DB::table('store_payment_transactions')->insert([
            "store_id" => $store_id,
            "order_id" => $order_id,
            "customer_id" => $customer_id,
            "tran_ref" => $result['tran_ref'],
            "trans_type" => $result['tran_type'],
            "cart_amount" => $result['cart_amount'],
            "ip" => $result['customer_details']['ip'],
            "trace" => $result['trace']
        ]);

        // return redirect()->route($this->store_url.'.customer.orders.index')->with('message',"Product ordered successfully");

        return  redirect($result['redirect_url']);
    }

    public function paymentresponse($id){
        $store_url = $this->store_url;
        $store_id = CommonController::get_store_id();
        $customer_id = (session()->has('authenticate_user')) ? session('authenticate_user')->customer_id : Auth::guard('customer')->user()->customer_id;
        $store_logo = $this->store_logo;
        $ord_id = Crypt::decrypt($id);
        $trans_data =  DB::table('store_payment_transactions')
                       ->select('store_payment_transactions.*')
                       ->where('order_id',$ord_id)
                       ->first();                       
        $tran_ref = $trans_data->tran_ref;
        $gateway = DB::table('store_payment_credentials')
        ->select('*')
        ->where('store_id',$store_id) //as of now we are hide for payment using all Stores
        ->where('status',1) 
        ->where('is_deleted',0)
        ->first();

        // Get payment Response form paytabs.
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://secure.paytabs.sa/payment/query',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
            "profile_id": 106571,
            "tran_ref": "'.$trans_data->tran_ref.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: SRJNHD96GB-JHJMMTKJ6B-TK22TNRRKJ'
          ),
        ));
        
        $response = curl_exec($curl);        
        curl_close($curl);
        $result = json_decode($response,TRUE);  


        $data_fetch = DB::table('store_payment_responses')->where('order_id',$ord_id)->get()->count();

        if($data_fetch == 0){
 // Add Trans Response
 DB::table('store_payment_responses')->insert([
    "store_id" => $store_id,
    "order_id" => $ord_id,
    "customer_id" => $customer_id,
    "tran_ref" => $result['tran_ref'],
    "tran_type" => $result['tran_type'],
    "cart_id" => $result['cart_id'],
    "cart_desc" => $result['cart_description'],
    "cart_currency" => $result['cart_currency'],
    "cart_amount" => $result['cart_amount'],
    "tran_currency" => $result['tran_currency'],
    "tran_total" => $result['tran_total'],
    "cus_name" => $result['customer_details']['name'],
    "cus_email" => $result['customer_details']['email'],
    "cus_street1" => $result['customer_details']['street1'],
    "cus_city" => $result['customer_details']['city'],
    "cus_state" => $result['customer_details']['state'],
    "cus_country" => $result['customer_details']['country'],
    "cus_zip" => $result['customer_details']['zip'],
    "cus_ip" => $result['customer_details']['ip'],
    "ship_name" => $result['shipping_details']['name'],
    "ship_email" => $result['shipping_details']['email'],
    "ship_street1" => $result['shipping_details']['street1'],
    "ship_city" => $result['shipping_details']['city'],
    "ship_state" => $result['shipping_details']['state'],
    "ship_country" => $result['shipping_details']['country'],
    "ship_zip" => $result['shipping_details']['zip'],
    "response_status" => $result['payment_result']['response_status'],
    "response_code" => $result['payment_result']['response_code'],
    "response_message" => $result['payment_result']['response_message'],
    // "transaction_time" => "",
    "payment_method" => $result['payment_info']['payment_method'],
    "card_type" => $result['payment_info']['card_type'],
    "card_scheme" => $result['payment_info']['card_scheme'],
    "payment_desc" => $result['payment_info']['payment_description'],
    "expiry_month" => $result['payment_info']['expiryMonth'],
    "expiry_year" => $result['payment_info']['expiryYear'],
    "merchant_id" => $result['merchantId'],
    "trace" => $result['trace']

]);


$date_i = date($result['payment_result']['transaction_time']);

DB::table('online_store_order_details')->where('online_order_id',$ord_id)->update([
    "payment_status" => $result['payment_result']['response_status'],
    "payment_code"  => $result['payment_result']['response_code'],
    "payment_message" => $result['payment_result']['response_message'],
    // "payment_time" => $date_i,
    "payment_ref" =>   $result['tran_ref']

]);




        }

       

        $resp = [];
        $resp['name'] = $result['customer_details']['name'];
        $resp['order_id'] = $ord_id;
        $resp['amount'] = $result['tran_total'];
        $resp['status'] = $result['payment_result']['response_status'];
        $resp['code'] = $result['payment_result']['response_code'];
        $resp['message'] = $result['payment_result']['response_message'];
        $resp['time'] = $result['payment_result']['transaction_time'];
        $resp['ref'] = $result['tran_ref'];
        $resp['account'] = $result['payment_info']['payment_description'];

       $red_url =  route($this->store_url .'.customer.orders.show',Crypt::encrypt($ord_id));
// echo $red_url; exit;
            

        return view('customer.paymentresponse',compact('store_logo','trans_data','resp','store_url','red_url'));

    }


    public function couponCodeDetails(Request $request)
    {
        $discountsQuery = DB::table('store_discount')
        ->leftJoin('store_product_discount', function ($join) {
            $join->on('store_discount.discount_id', '=', 'store_product_discount.discount_id')
                ->where('store_product_discount.is_deleted', '=', 0)
                ->where('store_product_discount.status', '=', 1);
        })
        ->where('store_discount.discount_method', '=', 'code')
        ->where('store_discount.discount_name', '=', $request->coupon_code)
        ->where('store_discount.discount_valid_from', '<=', Carbon::now())
        ->where(function ($query) {
            $query->where('store_discount.store_type', 'online')
                ->orWhere('store_discount.store_type','both');
        })
        ->where(function ($query) {
            $query->where('store_discount.discount_valid_to', '>=', Carbon::now())
                ->orWhereNull('store_discount.discount_valid_to');
        })
        ->select('store_discount.discount_id','product_discount_type','discount_value','discount_type','product_discount_id','product_id','variant_id','min_require_type','min_value','max_discount_uses','max_value','once_per_order');
        $discounts = $discountsQuery->get()->toArray();
        return response()->json(['store_discount' =>$discounts]);
    }
}
