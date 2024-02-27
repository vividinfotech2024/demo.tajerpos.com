<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\StoreAdmin\Product;
use App\Http\Controllers\CommonController;
use Session;
use App\Models\StoreAdmin\Tax;
use App\Models\StoreAdmin\Variants;

class AddToCartController extends Controller
{
    protected $store_url;
    public function __construct() {
        $current_url = request()->path();
        $split_url = explode("/",$current_url);
        $split_url_index = config('app.split_url_index');
        $this->store_url = (!empty($split_url)) ?$split_url[$split_url_index] : '';
    }

    public function addToCart(Request $request) {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity');
        $variants_combination_id = $request->input('product_variants_combination');
        $type = $request->input('type');
        $store_id = CommonController::get_store_id();
        if($type != "" && $type == "add_to_cart_page") {
            Session::forget('cart');
        } 
        if(!empty($productId)) {
            $product_query = Product::where([
                ['store_id', '=', $store_id],
                ['is_deleted', '=', 0],  
                ['status', '=', 1],
            ]);
            if(isset($type) && $type == "add_to_cart_page") 
                $product_query->whereIn('product_id',$productId);
            else 
                $product_query->where('product_id',$productId);
            $product = $product_query->select(['product_name','unit_price','product_id'])->get()->toArray();
        }
        if (!empty($product)) {
            if($type != "" && $type == "add_to_cart_page") {
                $cart_data = $request->input("cart_data");
                if(!empty($cart_data))
                    $cart = array_filter($cart_data);
            } else {
                $cart = session()->get('cart', []);
                if (isset($cart[$productId]) && ((!empty($variants_combination_id) && isset($cart[$productId][$variants_combination_id])) || ($variants_combination_id == ""))) {
                    if($variants_combination_id == "")
                        $cart[$productId]['quantity'] += $quantity;
                    else
                        $cart[$productId][$variants_combination_id]['quantity'] += $quantity;
                } else {
                    if($variants_combination_id == "") {
                        $cart[$productId] = [
                            'product_name' => $product[0]['product_name'],
                            'unit_price' => $product[0]['unit_price'],
                            'quantity' => $quantity,
                            'product_id' => $productId
                        ];
                    } else {
                        $cart[$productId][$variants_combination_id] = [
                            'product_name' => $product[0]['product_name'],
                            'unit_price' => $product[0]['unit_price'],
                            'quantity' => $quantity,
                            'variants_combination_id' => $variants_combination_id,
                            'product_id' => $productId
                        ];
                    }
                    
                }
            }
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
            return response()->json(['success' => trans('customer.product_added_to_cart_success')]);
        }
        else {
            $cart_data = session()->get('cart', []);
            if(empty($cart_data)) {
                $total_quantity = 0;
                session()->put('cart_total_quantity', $total_quantity);
            }
            return response()->json(['error' => trans('customer.product_not_found_error')]);
        }
    }

    public function viewCart() {
        $store_url = $this->store_url;
        $store_id = CommonController::get_store_id();
        $cart_data = session()->get('cart', []);
        $all_variants = []; $variant_id = []; $get_quantity = []; $product_ids = []; $product_details = [];$variant_combinations = []; $variant_combination_data = []; $quantity = [];
        if(!empty($cart_data)) {
            foreach($cart_data as $k => $product) {
                $product_ids[] = $k;
                if(!empty($product)) {
                    foreach($product as $key => $val) {
                        if(is_array($val)) {
                            $quantity[$k] = count($product);
                            $variant_id[$k][] = $key;
                            $all_variants[] = $key;
                            $get_quantity[$k][$key] = $val['quantity'];
                        } else {
                            $get_quantity[$k] = $product['quantity'];
                        }
                    }
                }
            }
        }       
        $product_variants_title = []; $product_variants_collection = []; $variants_combinations_data = [];
        if(!empty($product_ids)) {
            $product_details = Product::leftJoin('store_category', 'store_products.category_id', '=', 'store_category.category_id')->leftJoin('store_sub_category', 'store_products.sub_category_id', '=', 'store_sub_category.sub_category_id')->leftJoin('store_product_tax', 'store_products.product_id', '=', 'store_product_tax.product_id')->leftJoin('store_price', 'store_products.product_id', '=', 'store_price.product_id')
            ->leftJoin('store_product_variants_combination', function ($join) use ($all_variants) {
                $join->on('store_products.product_id', '=', 'store_product_variants_combination.product_id')
                    ->whereIn('store_product_variants_combination.variants_combination_id',$all_variants);
            })->where([
                ['store_products.store_id', '=', $store_id],
                ['store_products.is_deleted', '=', 0],
                ['store_products.status_type', '=', 'publish'],
                ['store_products.status', '=', 1],
                ['store_category.is_deleted', '=', 0],
                ['store_category.status', '=', 1],
            ])
            ->whereRaw(('case WHEN (store_products.sub_category_id > 0) THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 ELSE TRUE END'))
            ->whereRaw(('case WHEN (store_products.type_of_product = "variant") THEN store_product_variants_combination.is_deleted = 0 ELSE TRUE END'))
            // ->whereRaw('case WHEN (type_of_product = "single" AND trackable = 1) THEN store_products.unit > 0 ELSE TRUE END')
            ->whereIn('store_products.product_id',$product_ids)->orderBy('store_products.category_id','desc')
            ->select('product_name','store_products.category_id','category_name','price','store_products.product_id','store_products.category_image','tax_type','tax_amount','taxable','type_of_product','unit','trackable','variants_combination_name','variants_combination_id','variant_price','on_hand')
            ->selectRaw('CASE WHEN (on_hand <= 0 AND on_hand IS NOT NULL AND on_hand != "") THEN "out-of-stock" ELSE "" END as product_available')
            ->get();
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
                $variants_product_id = collect($product_details->toArray())->filter(function ($item) {
                    return $item['type_of_product'] === 'variant';
                })->pluck('product_id')->all();
                if(!empty($variants_product_id)) {
                    $product_variants = Variants::select('variants_name','product_id')
                    ->where([
                        ['store_id', '=', $store_id], 
                        ['is_deleted', '=', 0]
                    ])
                    ->whereIn('product_id',$variants_product_id)
                    ->get()->toArray();
                    if(!empty($product_variants) && count($product_variants) > 0) {
                        $product_variants_array = array_reduce($product_variants, function ($carry, $item) {
                            $productId = $item['product_id'];
                            if (!isset($carry[$productId])) {
                                $carry[$productId] = [];
                            }
                            $carry[$productId][] = $item;
                            return $carry;
                        }, []);
                        if(!empty($product_variants_array)) {
                            foreach($product_variants_array as $product_variants) {
                                foreach($product_variants as $index => $variant) {
                                    if(!isset($product_variants_title[$variant['product_id']]))
                                        $variants_title = "";
                                    if ($index == (count($product_variants) - 1) && $index !== 0) {
                                        $variants_title .= ' and ' . $variant['variants_name'];
                                    } else {
                                        $variants_title .= " ".$variant['variants_name'];
                                        if($index != (count($product_variants) - 2) && $index != (count($product_variants) - 1))
                                            $variants_title .= ",";
                                    }
                                    $product_variants_title[$variant['product_id']] = $variants_title;
                                }
                            }
                        }
                    }
                    $product_variants_combinations = Product::leftJoin('store_product_variants_combination', 'store_products.product_id', '=', 'store_product_variants_combination.product_id')
                        ->where([
                            ['store_products.store_id', '=', $store_id], 
                            ['store_products.is_deleted', '=', 0],
                            ['status_type', '=', 'publish'],
                            ['store_product_variants_combination.is_deleted', '=', 0],
                        ])
                        ->whereIn('store_products.product_id',$variants_product_id)
                        ->select('variants_combination_id','variants_combination_name','store_products.product_id','variant_price','on_hand')
                        ->selectRaw('CASE WHEN (on_hand <= 0 AND on_hand IS NOT NULL AND on_hand != "") THEN "out-of-stock" ELSE "" END as product_available')
                        ->get()->toArray();
                    if(!empty($product_variants_combinations)) {
                        $variants_combinations_data = array_reduce($product_variants_combinations, function ($carry, $item) {
                            $productId = $item['product_id'];
                            if (!isset($carry[$productId])) {
                                $carry[$productId] = [];
                            }
                            $carry[$productId][] = $item;
                            return $carry;
                        }, []);
                    }
                }
                
            }
        }
        $tax_details = Tax::where('store_id',$store_id)->get(['tax_percentage','tax_id'])->toArray();
        return view('customer.view_cart', compact('store_url','product_details','quantity','cart_data','variant_id','get_quantity','tax_details','product_variants_title','variants_combinations_data','store_id'));
    }

    public function quantityBySession(Request $request) {
        $cart_data = session()->get('cart', []);
        $product_type = $request->product_type;
        $product_id = $request->product_id;
        $variant_id = $request->variant_id;
        $quantity = 0;
        if(!empty($cart_data) && isset($cart_data[$product_id])) {
            if($product_type == "variant" && isset($cart_data[$product_id][$variant_id])) {
                $quantity = $cart_data[$product_id][$variant_id]['quantity'];
            } else if($product_type == "single") {
                $quantity = $cart_data[$product_id]['quantity'];
            }
        }
        return response()->json(['quantity'=>$quantity]);
    }
    public function getProductCount() {
        $cart_total_quantity = session()->get('cart_total_quantity');
        return response()->json(['cart_total_quantity'=>$cart_total_quantity]);
    }
}
