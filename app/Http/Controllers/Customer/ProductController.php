<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StoreAdmin\Product;
use App\Http\Controllers\CommonController;
use Illuminate\Support\Facades\Crypt;
use App\Models\StoreAdmin\Category;
use DB;
use App\Models\StoreAdmin\SubCategory;
use App\Models\Customers\Wishlist;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\StoreAdmin\Variants;

class ProductController extends Controller
{
    protected $store_url;
    public function __construct() {
        $current_url = request()->path();
        $split_url = explode("/",$current_url);
        $split_url_index = config('app.split_url_index');
        $this->store_url = (!empty($split_url)) ?$split_url[$split_url_index] : '';
    }

    public function singleProduct($id,$type = '')
    {
        $store_url = $this->store_url; 
        $store_id = CommonController::get_store_id();
        $product_id = Crypt::decrypt($id);
        $_page = !empty($type) ? Crypt::decrypt($type) : "";
        $product_details = Product::leftJoin('store_category', 'store_products.category_id', '=', 'store_category.category_id')
        ->leftJoin('store_sub_category', 'store_products.sub_category_id', '=', 'store_sub_category.sub_category_id')
        ->leftJoin('store_price', 'store_products.product_id', '=', 'store_price.product_id')
        ->where([
            ['store_products.store_id', '=', $store_id],
            ['store_products.is_deleted', '=', 0],
            ['status_type', '=', 'publish'],
            ['store_category.is_deleted', '=', 0],
            ['store_products.product_id', '=', $product_id]
        ])
        ->whereIn('store_products.product_type', ['online', 'both'])
        ->whereRaw('
            (CASE WHEN (store_products.sub_category_id > 0) 
                THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 
                ELSE TRUE 
            END)
        ')
        ->select('store_products.product_id', 'store_products.category_id', 'product_name', 'type_of_product', 'price', 'store_products.category_image', 'store_products.featured', 'unit', 'trackable', 'product_description', 'category_name', 'sub_category_name','store_products.sub_category_id')
        ->orderBy('store_products.created_at', 'DESC')
        ->first()->toArray();
        $product_variants_combinations = [];
        if(!empty($product_details) && count($product_details) > 0 && $product_details['type_of_product'] == "variant") {
            if(!empty($product_details['product_id'])) {
                $get_product_variants_combinations = Product::leftJoin('store_product_variants_combination', 'store_products.product_id', '=', 'store_product_variants_combination.product_id')
                ->where([
                    ['store_products.store_id', '=', $store_id], 
                    ['store_products.is_deleted', '=', 0],
                    ['status_type', '=', 'publish'],
                    ['store_product_variants_combination.is_deleted', '=', 0]
                ])
                ->whereIn('store_products.product_type', ['online', 'both'])
                ->where('store_products.product_id',$product_details['product_id'])
                ->select('variants_combination_id','variants_combination_name','store_products.product_id','variant_price','on_hand')->get()->toArray();
                if(!empty($get_product_variants_combinations)) {
                    $cart_data = session()->get('cart', []);
                    foreach($get_product_variants_combinations as $key => $variants) {
                        $product_unit = $available_quantity = $variants['on_hand'];
                        if(!empty($cart_data) && isset($cart_data[$variants['product_id']]) && isset($cart_data[$variants['product_id']][$variants['variants_combination_id']])) {
                            $quantity = $cart_data[$variants['product_id']][$variants['variants_combination_id']]['quantity'];
                            if(!empty($product_unit) && is_numeric($product_unit) && $product_unit >= 0)
                            $available_quantity = ($product_unit - $quantity);
                        }
                        $variants['product_available']  = (is_numeric($available_quantity) && ($available_quantity <= 0)) ? "out-of-stock" : "";
                        $product_variants_combinations[$variants['product_id']][$variants['variants_combination_name']] = $variants;
                    }
                }
            }
        }
        $wishlistData = [];
        if(auth()->guard('customer')->check()) {
            if(session()->has('authenticate_user'))
                $user = session('authenticate_user');
            else 
                $user = Auth::guard('customer')->user();
            $wishlistData = Wishlist::where([
                ['customer_id', '=',$user->customer_id],
                ['store_id','=',$user->store_id],
                ['is_deleted','=',0]
            ])
            ->where('product_id', $product_id)
            ->select('wishlist_id','product_id','variants_id')->get()->toArray();
        }
        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => trans('customer.home'), 'url' => route($store_url.'.customer.home')];
        if($_page == "product_page" || $_page == "related_products")
            $breadcrumbs[] = ['name' => trans('customer.products'), 'url' => route($store_url.'.customer.category')];            
        if(!empty($product_details) && isset($product_details['product_name'])) 
            $breadcrumbs[] = ['name' => $product_details['product_name'], 'url' => "#"];
        $cart_data = session()->get('cart', []);
        $product_variants = Variants::select('variants_name')
        ->where([
            ['store_id', '=', $store_id], 
            ['is_deleted', '=', 0], 
            ['product_id', '=', $product_id]
        ])->get()->toArray();
        if(!empty($product_variants) && count($product_variants) > 0) {
            $variants_title = count($product_variants) == 1 ? "Select a" : "Select";
            foreach($product_variants as $index => $variant) {
                if ($index == (count($product_variants) - 1) && $index !== 0) {
                    $variants_title .= ' and ' . $variant['variants_name'];
                } else {
                    $variants_title .= " ".$variant['variants_name'];
                    if($index != (count($product_variants) - 2) && $index != (count($product_variants) - 1))
                        $variants_title .= ",";
                }
            }
        } else 
            $variants_title = "Select a variant";
        return view('customer.single_product',compact('cart_data','store_url','product_variants_combinations','product_details','breadcrumbs','wishlistData','variants_title','store_id'));
    }

    public function categoryProduct($type = '')
    {
        $store_url = $this->store_url; 
        $_page = !empty($type) ? Crypt::decrypt($type) : "";
        $store_id = CommonController::get_store_id();
        $breadcrumbs = [];
        $breadcrumbs[] = ['name' => trans('customer.home'), 'url' => route($store_url.'.customer.home')];
        $breadcrumbs[] = ['name' => trans('customer.products'), 'url' => "#"];
        return view('customer.category_product',compact('store_url','breadcrumbs','store_id'));
    }

    public function getCategoryProduct(Request $request) {
        $store_id = CommonController::get_store_id();
        $category_id = $request->category_id;
        $category_details = Category::where([
            ['store_category.store_id', '=', $store_id], 
            ['store_category.status', '=', 1],
            ['store_category.is_deleted', '=', 0], 
        ])
        ->select(
            'store_category.category_id',
            'store_category.category_name',
            DB::raw('(SELECT COUNT(*) FROM store_products AS sp LEFT JOIN store_sub_category on sp.sub_category_id = store_sub_category.sub_category_id WHERE sp.category_id = store_category.category_id AND sp.is_deleted = 0 AND status_type = "publish" AND (CASE WHEN sp.sub_category_id > 0 THEN store_sub_category.status = 1 AND store_sub_category.is_deleted = 0 ELSE TRUE END)) AS product_count')
        )
        ->orderBy('store_category.created_at', 'DESC')
        ->get();
        $total_product_count = $category_details->sum('product_count');
        $category_details = $category_details->toArray();
        $all_product_query = Product::leftJoin('store_category', 'store_products.category_id', '=', 'store_category.category_id')
        ->leftJoin('store_sub_category', 'store_products.sub_category_id', '=', 'store_sub_category.sub_category_id')
        ->leftJoin('store_price', 'store_products.product_id', '=', 'store_price.product_id')->where([
            ['store_products.store_id', '=', $store_id], 
            ['store_products.is_deleted', '=', 0],
            ['status_type', '=', 'publish'],
            ['store_category.status', '=', 1], 
            ['store_category.is_deleted', '=', 0], 
        ])
        ->whereIn('store_products.product_type', ['online', 'both']);
        if($category_id != 'all')
            $all_product_query->where('store_products.category_id',$category_id);
        $all_product_details = $all_product_query->whereRaw(('case WHEN (store_products.sub_category_id > 0) THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 ELSE TRUE END'))->select('store_products.product_id','store_products.category_id','product_name','type_of_product','price','store_products.category_image')->orderBy('store_products.created_at','DESC')->get();
        return response()->json(['category_details'=>$category_details,'total_product_count'=>$total_product_count,'all_product_details'=>$all_product_details]);
    }

    /*public function productsByCategory(Request $request) {
        $category_id = !empty($request->category_id) ? $request->category_id : "all";
        $sub_category_id = $request->sub_category_id;
        $sorting_column = $request->sorting_column;
        $sorting_order = $request->sorting_order;
        $store_id = CommonController::get_store_id();
        $store_url = $this->store_url; 
        $page = !empty($request->input('page')) ? $request->input('page') : 1;
        $search_text = $request->search_text;
        $page_type = $request->_type;
        $perPage = $request->perPage;
        $product_id = $request->product_id;
        if($page_type != "related_products") {
            $category_details = Category::select('category_name', 'category_id', 'icon')
                ->where('store_id', $store_id)
                ->where('is_deleted', 0)
                ->where('status', 1)
                ->orderByDesc('category_id')
                ->get();
        }
        $product_details_query = Product::select('store_products.product_id', 'type_of_product', 'product_name', 'store_products.category_id', 'category_name', 'unit_price', 'store_products.category_image', 'store_products.sub_category_id', 'unit', 'trackable', 'tax_amount', 'price', 'product_description','sub_category_name');
        if ($sorting_column == "low_to_high" || $sorting_column == "high_to_low") {
            $product_details_query->selectRaw('
                CASE 
                    WHEN store_products.type_of_product = "variant" THEN 
                        (SELECT variants_combination_name FROM store_product_variants_combination 
                         WHERE product_id = store_products.product_id and store_product_variants_combination.is_deleted = 0
                         ORDER BY CAST(variant_price AS DECIMAL(10,2)) '.$sorting_order.' LIMIT 1)
                    ELSE TRUE
                END as variants_combination_name
            ');
        } 
        $product_details_query->leftJoin('store_category', 'store_category.category_id', '=', 'store_products.category_id')
        ->leftJoin('store_product_variants_combination as spvc', 'store_products.product_id', '=', 'spvc.product_id')
        ->leftJoin('store_sub_category',function($join) {
            $join->on('store_sub_category.sub_category_id', '=', 'store_products.sub_category_id');
        })
        ->leftJoin('store_product_tax', 'store_products.product_id', '=', 'store_product_tax.product_id')
        ->leftJoin('store_price', 'store_products.product_id', '=', 'store_price.product_id')
        ->where('store_products.store_id', $store_id)
        ->where('store_products.is_deleted', 0)
        ->where('store_products.status_type', 'publish')
        ->where('store_products.status', 1)
        ->whereIn('store_products.product_type', ['online', 'both'])
        ->where('store_category.is_deleted', 0)
        ->where('store_category.status', 1)
        ->when($search_text, function ($query) use ($search_text) {
            $query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        })
        ->when($category_id != "all", function ($query) use ($category_id) {
            $query->where('store_products.category_id', $category_id);
        }) 
        ->when($sub_category_id, function ($query) use ($sub_category_id) {
            $query->where('store_products.sub_category_id',$sub_category_id);
        })
        ->when($page_type == "related_products", function ($query) use ($product_id) {
            $query->whereNotIn('store_products.product_id',[$product_id]);
        }) 
        ->whereRaw(('case WHEN (store_products.sub_category_id > 0) THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 ELSE TRUE END'))
        ->distinct('store_products.product_id');
        if (!empty($sorting_column) && !empty($sorting_order)) {
            if ($sorting_column == "low_to_high" || $sorting_column == "high_to_low") {
                $product_details_query->orderByRaw('
                    CASE 
                        WHEN store_products.type_of_product = "variant" THEN 
                            (SELECT CAST(variant_price AS DECIMAL(10,2)) FROM store_product_variants_combination 
                             WHERE product_id = store_products.product_id and store_product_variants_combination.is_deleted = 0
                             ORDER BY CAST(variant_price AS DECIMAL(10,2)) '.$sorting_order.' LIMIT 1)
                        ELSE CAST(price AS DECIMAL(10,2))
                    END '.$sorting_order.'
                ');
            } else {
                $product_details_query->orderBy($sorting_column, $sorting_order);
            }
        } else {
            $product_details_query->orderByDesc('store_products.category_id');
        }
        $product_details = $product_details_query->paginate($perPage);
        $all_product_data = $product_details->total();   
        $totalPages = ceil($all_product_data / $perPage);  
        $productIds = $product_details->map(function ($product) {
            return $product->product_id;
        });
        $product_variants_query = Product::leftJoin('store_product_variants', 'store_products.product_id', '=', 'store_product_variants.product_id')
            ->where('store_products.store_id', $store_id)
            ->where('store_products.is_deleted', 0)
            ->whereIn('store_products.product_type', ['online', 'both'])
            ->where('status_type', 'publish')
            ->where('type_of_product', 'variant')
            ->where('store_product_variants.is_deleted', 0);
        if ($search_text !== "") 
            $product_variants_query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        if ($category_id !== "all") 
            $product_variants_query->where('store_products.category_id', $category_id);
        if (!empty($sub_category_id))
            $product_variants_query->where('store_products.sub_category_id', $sub_category_id);
        if (!empty($productIds)) 
            $product_variants_query->whereIn('store_products.product_id', $productIds);
        $product_variants = $product_variants_query->select('variants_name','store_product_variants.product_id')->get()->toArray();
        $product_variants_collection = [];
        if(!empty($product_variants)) {
            $variantsCollection = new Collection($product_variants);
            $product_variants_collection = $variantsCollection->groupBy('product_id');
        }
        $product_variants_combinations_query = Product::leftJoin('store_product_variants_combination', 'store_products.product_id', '=', 'store_product_variants_combination.product_id')
        ->where('store_products.store_id', $store_id)
        ->where('store_products.is_deleted', 0)
        ->whereIn('store_products.product_type', ['online', 'both'])
        ->where('status_type', 'publish')
        ->where('type_of_product', 'variant')
        ->where('store_product_variants_combination.is_deleted', 0);
        if ($search_text !== "") 
            $product_variants_combinations_query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        if ($category_id !== "all") 
            $product_variants_combinations_query->where('store_products.category_id', $category_id);
        if (!empty($sub_category_id))
            $product_variants_combinations_query->where('store_products.sub_category_id', $sub_category_id);
        if (!empty($productIds)) 
            $product_variants_combinations_query->whereIn('store_products.product_id', $productIds);
        $get_product_variants_combinations = $product_variants_combinations_query->select('variants_combination_id', 'variants_combination_name', 'store_products.product_id', 'variant_price', 'on_hand')->get()->toArray();
        $cart_data = session()->get('cart', []);
        $product_variants_combinations = []; $variants_id = [];
        if(!empty($get_product_variants_combinations)) {
            foreach($get_product_variants_combinations as $key => $variants) {
                $variant_product_unit = $available_variants_quantity = $variants['on_hand'];
                if(!empty($cart_data) && isset($cart_data[$variants['product_id']]) && isset($cart_data[$variants['product_id']][$variants['variants_combination_id']])) {
                    $quantity = $cart_data[$variants['product_id']][$variants['variants_combination_id']]['quantity'];
                    if(!empty($variant_product_unit) && is_numeric($variant_product_unit) && $variant_product_unit >= 0)
                        $available_variants_quantity = ($variant_product_unit - $quantity);
                }
                $variants['product_available'] = (is_numeric($available_variants_quantity) && ($available_variants_quantity <= 0)) ? "out-of-stock" : "";
                $product_variants_combinations[$variants['product_id']][$variants['variants_combination_name']] = $variants;
                if (!isset($variants_id[$variants['product_id']])) {
                    $variants_id[$variants['product_id']] = $variants['variants_combination_id'];
                }
            }
        }
        $wishlistData = [];
        if(!empty($productIds) && auth()->guard('customer')->check()) {
            $productIdsWithVariants = [];
            foreach ($productIds as $productId) {
                if (isset($variants_id[$productId])) {
                    $productIdsWithVariants[$productId] = $variants_id[$productId];
                } else {
                    $productIdsWithVariants[$productId] = null;
                }
            }
            if(session()->has('authenticate_user'))
                $user = session('authenticate_user');
            else 
                $user = Auth::guard('customer')->user();
            $wishlistData = Wishlist::where([
                ['customer_id', '=', $user->customer_id],
                ['store_id','=',$user->store_id],
                ['is_deleted','=',0]
            ])
            ->whereIn('product_id', array_keys($productIdsWithVariants))
            // ->orWhere(function ($query) use ($productIdsWithVariants) {
            //     foreach ($productIdsWithVariants as $productId => $variantId) {
            //         if ($variantId !== null) {
            //             $query->orWhere(function ($subquery) use ($productId, $variantId) {
            //                 $subquery->where('customer_id', Auth::guard('customer')->user()->customer_id)->where('store_id', Auth::guard('customer')->user()->store_id)->where('is_deleted', 0)->where('product_id', $productId)->where('variants_id', $variantId);
            //             });
            //         }
            //     }
            // });
            ->select('wishlist_id','product_id','variants_id')->get()->toArray();
        }   
        $product_variant_details_query = Product::leftJoin('store_product_variants_combination',function($join) {
            $join->on('store_product_variants_combination.product_id', '=', 'store_products.product_id');
            $join->where('store_product_variants_combination.is_deleted', '=', 0);
        })->join('store_category',function($join) {
            $join->on('store_category.category_id', '=', 'store_products.category_id');
        })->leftJoin('store_sub_category',function($join) {
            $join->on('store_sub_category.sub_category_id', '=', 'store_products.sub_category_id');
        })->leftJoin('store_product_tax',function($join) {
            $join->on('store_product_tax.product_id', '=', 'store_products.product_id');
        })->where([
            ['store_products.store_id', '=', $store_id],
            ['store_products.is_deleted', '=', 0],
            ['store_products.status', '=', 1],
            ['store_products.status_type', '=', 'publish'],
            ['store_category.is_deleted', '=', 0],
            ['store_category.status', '=', 1],
        ])
        ->whereIn('store_products.product_type', ['online', 'both']);
        if($search_text != "")
            $product_variant_details_query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        $product_variant_details = $product_variant_details_query->whereRaw(('case WHEN (store_products.sub_category_id > 0) THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 ELSE TRUE END'))
        ->select('store_products.product_id','store_products.category_image','store_products.category_id','store_products.sub_category_id','type_of_product','trackable','variants_combination_id','variants_combination_name','variant_price','on_hand','available','store_product_variants_combination.sku','store_product_variants_combination.barcode','unit','product_name')
        ->get()->toArray();    
        $category_array = array(); $sub_category_array = array(); $category_count = array();$sub_category_count = array();$variant_combinations = []; $variant_combination_data = [];
        if(!empty($product_variant_details)) {
            foreach($product_variant_details as $product) {
                $category_array[$product['category_id']][$product['product_id']][] = $product;
                $category_count[$product['category_id']] = count($category_array[$product['category_id']]);
                if(!empty($product['sub_category_id'])) {
                    $sub_category_array[$product['category_id']][$product['sub_category_id']][$product['product_id']][] = $product;
                    $sub_category_count[$product['category_id']][$product['sub_category_id']] = count($sub_category_array[$product['category_id']][$product['sub_category_id']]);
                }
                if(!empty($product['variants_combination_id'])) {
                    $variant_combinations[$product['product_id']][] = $product;
                    $variant_combination_data[$product['variants_combination_id']] = $product;
                }
            }
        }
        if($category_id != "all" || $page_type == "product_page")  {
            $sub_category_details_query = SubCategory::select('store_sub_category.category_id','sub_category_name','sub_category_id')
            ->where([
                ['store_sub_category.store_id', '=', $store_id],
                ['store_sub_category.is_deleted', '=', 0],
                ['status', '=', 1]
            ]);
            if($category_id != "all" && $page_type != "product_page")
                $sub_category_details_query->where('store_sub_category.category_id',$category_id);
            // if (!empty($sub_category_id))
            //     $sub_category_details_query->where('store_sub_category.sub_category_id', $sub_category_id);
            $sub_category_details = $sub_category_details_query->orderBy('store_sub_category.category_id','desc')->get()->toArray();
        }
        $total_products = 0;
        if(!empty($category_count)) {
            foreach($category_count as $count) {
                $total_products += $count;
            }
        }
        $category_list_html = "";
        if(isset($category_details) && !empty($category_details) && ($total_products > 0))  {
            $active_class =  ($category_id == "all") ? "active" : "";
            if($page_type == "product_page") {
                $organizedSubcategories = array();
                if(!empty($sub_category_details)) {
                    foreach ($sub_category_details as $subcategory) {
                        $organizedSubcategories[$subcategory["category_id"]][] = $subcategory;
                    }
                }
                $active_class = ($category_id == "all") ? "active" : "";
                $category_list_html .= '<ul><li class="category-details '.$active_class.'"><input type="hidden" class="category-id" value="all" tabindex="0"><a href="#01" class="category-name">'.trans("customer.all").' <span>('.($total_products).')</span></a></li>';
                foreach ($category_details as $category) {
                    $isDisplayCategory = 0;
                    if(!empty($category_count) && array_key_exists($category->category_id,$category_count) && ($category_count[$category->category_id] > 0)) {
                        $active_class = ($category_id == $category->category_id) ? "active" : "";
                        $subCategoryDetails = (!empty($organizedSubcategories) && array_key_exists($category->category_id,$organizedSubcategories)) ? $organizedSubcategories[$category->category_id] : [];
                        if(!empty($subCategoryDetails)) {
                            foreach($subCategoryDetails as $key=> $sub_category) {
                                $sc_active_class = ($sub_category_id == $sub_category['sub_category_id']) ? "active" : "";
                                if(!empty($sub_category_count) && array_key_exists($category->category_id,$sub_category_count) && array_key_exists($sub_category['sub_category_id'],$sub_category_count[$category->category_id]) && ($sub_category_count[$category->category_id][$sub_category['sub_category_id']] > 0)) {
                                    if($isDisplayCategory == 0) {
                                        $category_list_html .= '<li class="category-details nested '.$active_class.'"><input type="hidden" class="category-id" value="'.$category->category_id.'"><a href="#01" class="toggle category-name">'.$category->category_name.' <span>('.$category_count[$category->category_id].')</span></a><ul class="nested-list">';
                                        $isDisplayCategory++;
                                    }
                                    $category_list_html.= '<li class="nested-sub-category-list sub-category-list '.$sc_active_class.' sub-category-list-'.$sub_category['sub_category_id'].'" data-sub-category-id="'.$sub_category['sub_category_id'].'">
                                                                <a href="#02" class="sub-category-li nested-sub-category-name">'.$sub_category['sub_category_name'].' <span>('.$sub_category_count[$category->category_id][$sub_category['sub_category_id']].')</span> <span class="product-count-sub-category"></span></a>
                                                            </li>';
                                    if($key == (count($subCategoryDetails) - 1))
                                        $category_list_html.= '</ul></li>';
                                } 
                            }
                            if($isDisplayCategory == 0) {
                                $category_list_html .= '<li class="category-details '.$active_class.'"><input type="hidden" class="category-id" value="'.$category->category_id.'"><a href="#01" class="category-name">'.$category->category_name.' <span>('.$category_count[$category->category_id].')</span></a></li>';
                                $isDisplayCategory++;
                            }
                        }
                        else 
                            $category_list_html .= '<li class="category-details '.$active_class.'"><input type="hidden" class="category-id" value="'.$category->category_id.'"><a href="#01" class="category-name">'.$category->category_name.' <span>('.$category_count[$category->category_id].')</span></a></li>';
                    }
                }
                $category_list_html .= '</ul>';
            } else {
                $category_list_html .= 
                '<div class="col-lg-4 category-details">
                    <div class="single_featured_banner wow fadeInUp '.$active_class.'" data-wow-delay="0.1s" data-wow-duration="1.1s">
                        <div class="featured_banner_text d-flex justify-content-between align-items-center">
                            <input type="hidden" class="category-id" value="all">
                            <h3><a href="#0">'.trans("customer.all").'</a></h3>
                            <span class="all-product-count">('.($total_products).')</span> 
                        </div>
                    </div>
                </div>'; 
                foreach ($category_details as $category) {
                    if(!empty($category_count) && array_key_exists($category->category_id,$category_count) && ($category_count[$category->category_id] > 0)) {
                        $active_class = ($category_id == $category->category_id) ? "active" : "";
                        $category_list_html .= 
                        '<div class="col-lg-4 category-details">
                            <div class="single_featured_banner wow fadeInUp '.$active_class.'" data-wow-delay="0.1s" data-wow-duration="1.1s">
                                <div class="featured_banner_text d-flex justify-content-between align-items-center">
                                    <input type="hidden" class="category-id" value="'.$category->category_id.'">
                                    <h3><a href="#0">'.$category->category_name.'</a></h3>
                                    <span class="category-product-count">('.$category_count[$category->category_id].')</span>
                                </div>
                            </div>
                        </div>'; 
                    }
                }
            }
        }
        $product_list_by_category = "";
        if($category_id != "all" && $page_type != "product_page" && $page_type != "related_products") {
            $product_list_by_category .= 
                '<div class="product_header">
                    <div class="product_tab_button">
                        <ul class="nav featured_banner_inner sub-category-list-details">';
                            if(isset($sub_category_details)) {
                                $product_list_by_category.= '<li class="sub-category-list" data-sub-category-id="all">
                                                                <a class="active sub-category-li" href="#01">'.trans("customer.all").'<span class="all-sub-category-li"></span></a>
                                                            </li>';
                            }
                            if(!empty($sub_category_details)) {
                                foreach($sub_category_details as $sub_category) {
                                    if(!empty($sub_category_count) && array_key_exists($category_id,$sub_category_count) && array_key_exists($sub_category['sub_category_id'],$sub_category_count[$category_id]) && ($sub_category_count[$category_id][$sub_category['sub_category_id']] > 0)) {
                                        $product_list_by_category.= '<li class="sub-category-list sub-category-list-'.$sub_category['sub_category_id'].'" data-sub-category-id="'.$sub_category['sub_category_id'].'">
                                                                    <a href="#02" class="sub-category-li">'.$sub_category['sub_category_name'].' <span class="product-count-sub-category"></span></a>
                                                                </li>';
                                    }
                                }
                            }
            $product_list_by_category .= '</ul></div></div>';
        }
        $productDetailsArray = $product_details->toArray();
        $product_details = $productDetailsArray['data'];
        if (isset($product_details) && !empty($product_details)) {
            $product_list_by_category .= '<div class="tab-content product_container">';
            if (auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id) 
                $isAuthenticated = 1;
            else 
                $isAuthenticated = 0;
            $product_list_by_category .= '<div class="product_gallery"><input type="hidden" class="is_authenticated" value="' . $isAuthenticated . '"><div class="row">';
            foreach ($product_details as $product) {
                $product_images = !empty($product) && !empty($product['category_image']) ? explode("***", $product['category_image']) : [];
                $product_list_by_category .=
                    '<div class="col-lg-4 col-md-4 col-sm-6 sub-category-product-list sub-category-product-' . $product['sub_category_id'] . '">
                        <article class="single_product single-product-details single-product-details-'.$product['product_id'].'">';
                if (!empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) {
                    $product_list_by_category .= '<input type="hidden" class="variant-combinations variant-combinations-' . $product['product_id'] . '" value="' . htmlspecialchars(json_encode($product_variants_combinations[$product['product_id']]), ENT_QUOTES, 'UTF-8') . '">';
                } else {
                    $product_list_by_category .= '<input type="hidden" class="variant-combinations variant-combinations-' . $product['product_id'] . '" value="">';
                }
                $product_id = $product["product_id"];
                if(($sorting_column == "low_to_high" || $sorting_column == "high_to_low") && $product['type_of_product'] == "variant" && $page_type == "product_page")
                    $variants_id = $variants_name = $product['variants_combination_name'];
                else 
                    $variants_id = $variants_name = (!empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) ? key($product_variants_combinations[$product['product_id']]) : "";                     
                $variants_id = ($variants_id) != "" ? ($product_variants_combinations[$product_id][$variants_id]['variants_combination_id']) : "";
                $variants_on_hand = ""; $product_quantity = 1; 
                $product_unit = $available_quantity = ($product['type_of_product'] == "variant" && !empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) ? $product_variants_combinations[$product_id][$variants_name]['on_hand'] : $product['unit'];
                if(!empty($cart_data) && isset($cart_data[$product_id])) {
                    if($product['type_of_product'] == "variant" && isset($cart_data[$product_id][$variants_id])) {
                        $quantity = $cart_data[$product_id][$variants_id]['quantity'];
                        if(!empty($product_unit) && is_numeric($product_unit) && $product_unit >= 0) {
                            $variants_on_hand = $available_quantity = ($product_unit - $quantity);
                        }
                    } 
                    else if($product['type_of_product'] == "single") {
                        $quantity = $cart_data[$product_id]['quantity'];
                        $product_unit = $available_quantity = $product['unit'] - $quantity;
                    }
                }
                $product_list_by_category .= '<figure>
                        <div class="product_thumb">
                            <a class="single-product-url" href="' . route($store_url . '.customer.single-product', Crypt::encrypt($product['product_id'])) . '"><img class="product-image-path" src="' . (!empty($product_images) && count($product_images) > 0 ? $product_images[0] : "") . '" alt=""></a>
                            <div class="action_links2">
                                <ul class="d-flex justify-content-center">';
                                    // if((($product['type_of_product'] == "single" && $product['trackable'] == 1) || ($product['type_of_product'] == "variant" && $available_quantity != "")) && ($available_quantity <= 0)) {
                                    //     $product_list_by_category .= '<li class="add_to_cart"><a href="#01" class="product-add-to-cart add-to-cart" title="'.trans("customer.out_of_stock").'" data-type="products-in-home" disabled><span class="pe-7s-close-circle"></span></a></li>';
                                    // } else 
                                    //     $product_list_by_category .= '<li class="add_to_cart"><a href="#01" class="product-add-to-cart add-to-cart" title="Add to cart" data-type="products-in-home"><span class="pe-7s-shopbag"></span></a></li>';
                                    if (auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id) {
                                        $wishlist_class = "far"; $wishlist_type = "add"; $title = trans("customer.add_to_wishlist");
                                        $productExistsInWishlist = count(array_filter($wishlistData, function ($item) use ($product_id, $variants_id) {
                                            return $item['product_id'] == $product_id && $item['variants_id'] == $variants_id;
                                        })) > 0;
                                        if ($productExistsInWishlist) { 
                                            $wishlist_class = "fas"; $wishlist_type = "remove"; $title = trans("customer.remove_from_wishlist");
                                        }
                                        $product_list_by_category .=  '<li class="wishlist"><a href="#01" title="'.$title.'" class="product-wishlist"><span data-type="products-in-home" data-wishlist-type="'.$wishlist_type.'" class="wishlist-icon fa-heart '.$wishlist_class.'"></span></a></li>';
                                    }
                                    // $product_list_by_category .= '<li class="quick_button"><a href="#01" title="Quick View" data-product-type="' . $product['type_of_product'] . '" class="product-view"><span class="pe-7s-look"></span></a></li>
                                    $product_list_by_category .= '
                                </ul>
                            </div>
                        </div>
                        <figcaption class="product_content text-center">
                            <div class="justify-content-between"> 
                                <h4 class="me-2 mb-2 fw-bold text-center"><a href="' . route($store_url . '.customer.single-product', ['id' => Crypt::encrypt($product['product_id']), 'type' => Crypt::encrypt($page_type)]) . '" class="product-name truncate-text"  data-bs-toggle="tooltip" title="'.$product["product_name"].'">' . $product['product_name'] . '</a></h4>
                                <input type="hidden" class="product-id single-product-id" value="' . $product['product_id'] . '">
                                <input type="hidden" class="product-trackable single-product-trackable" value="' . $product['trackable'] . '">
                                <input type="hidden" class="product-category-name" value="'.$product['category_name'].'">
                                <input type="hidden" class="product-subcategory-name" value="'.$product['sub_category_name'].'">
                                <input type="hidden" class="product-unit" value="' . $product['unit'] . '">
                                <input type="hidden" class="single-product-variants-combination" value="'.$variants_id.'">
                                <input type="hidden" class="variant-on-hand" value="'.$product_unit.'">
                                <input type="hidden" class="modal-variant-on-hand" value="'.$variants_on_hand.'">
                                <input type="hidden" class="modal-product-unit" value="'.$product_unit.'">
                                <input type="hidden" class="single-product-type" value="' . $product['type_of_product'] . '">
                                <input type="hidden" class="single-product-description" value="' . htmlentities($product['product_description'], ENT_QUOTES, 'UTF-8') . '">
                                <input type="hidden" class="add-product-quantity quantity" value="1"> 
                                <input type="hidden" class="product-category-images" value="'.$product['category_image'].'">
                                <div class="price_box mb-2">
                                    <span class="current_price fw-bold product-price modal-product-price">';
                if ($product["type_of_product"] == "variant") {
                    $product_list_by_category .=  (!empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) ? 'SAR '.number_format($product_variants_combinations[$product_id][$variants_name]['variant_price'], 2, '.', '') : "";
                } else {
                    $product_list_by_category .= ($product['type_of_product'] == "single") ? "SAR " . number_format($product['price'], 2, '.', '') : "";
                }
                $product_list_by_category .= '</span>
                                </div>
                            </div>';
                if (!empty($product_images) && count($product_images) > 0) {
                    $image_class = (count($product_images) > 7) ? "slick-carousel" : "";
                    $product_list_by_category .= '<div class="mb-3 d-flex flex-nowrap justify-content-center align-items-center '.$image_class.'">';
                    foreach ($product_images as $product_img) {
                        $product_list_by_category .= '<div>
                            <button type="button" class="btn p-1 d-flex align-items-center justify-content-center border rounded-circle fs-5 text-primary product-images-btn" >
                                <img src="' . $product_img . '" alt="" class="product-img rounded-circle avatar-xs">
                            </button>
                        </div>';
                    }
                    $product_list_by_category .= '</div>';
                }
                if($product['type_of_product'] == "variant" && !empty($product_variants_combinations) && count($product_variants_combinations) > 0 && isset($product_variants_combinations[$product['product_id']]) && !empty(isset($product_variants_combinations[$product['product_id']]))) {
                    $product_list_by_category .= '<div class="mb-3">';
                        if(!empty($product_variants_collection) && isset($product_variants_collection[$product['product_id']]) && count($product_variants_collection[$product['product_id']]) > 0) {
                            $variants = $product_variants_collection[$product['product_id']];
                            $variants_title = count($variants) == 1 ? "Select a" : "Select";
                            foreach($variants as $index => $variant) {
                                if ($index == (count($variants) - 1) && $index !== 0) {
                                    $variants_title .= ' and ' . $variant['variants_name'];
                                } else {
                                    $variants_title .= " ".$variant['variants_name'];
                                    if($index != (count($variants) - 2) && $index != (count($variants) - 1))
                                        $variants_title .= ",";
                                }
                            }
                        } else 
                            $variants_title = "Select a variant";
                    $product_list_by_category .= '<p class="text-center">'.$variants_title.'</p>';
                    $variants_carousel_class = ($page_type == "product_page" && count($product_variants_combinations[$product['product_id']]) > 2) ? "slick-variants-carousel" : (count($product_variants_combinations[$product['product_id']]) > 3) ? "slick-variants-carousel" : "";
                    $product_list_by_category .= '<div class="d-flex flex-wrap gap-2 justify-content-center align-items-center mb-2 '.$variants_carousel_class.'">';
                            foreach($product_variants_combinations[$product['product_id']] as $key => $variant) {
                                $firstKey = ((($sorting_column == "low_to_high" || $sorting_column == "high_to_low") && $product['type_of_product'] == "variant" && $page_type == "product_page")) ? $product['variants_combination_name'] : key($product_variants_combinations[$product['product_id']]);
                                $checked = ($firstKey == $key) ? "checked" : "";
                                $checked_style = ($firstKey == $key) ? "background-color: rgb(108, 117, 125); color: rgb(255, 255, 255); border-color: rgb(108, 117, 125);" : "";
                                $product_list_by_category .= '<div class="product-variant-dev">
                                    <input type="radio" class="btn-check product-variant" data-type="products-in-home" name="product_variant_'.$product['product_id'].'" id="product-variant-'.$variant['variants_combination_id'].'" value="'.$variant["variants_combination_id"].'" '.$checked.'>
                                    <label style="'.$checked_style.'" class="btn btn-outline-secondary avatar-xs-1 rounded-4 d-flex justify-content-center align-items-center product-variant-label '.$variant['product_available'].'" for="product-variant-'.$variant['variants_combination_id'].'" data-bs-toggle="tooltip" data-variant-combination ="'.$variant["variants_combination_name"].'" title="'.$variant["variants_combination_name"].'">'.$variant["variants_combination_name"].'</label>
                                </div>';
                            }
                        $product_list_by_category .= '</div>
                    </div>';
                }
                $btn_style = ($product['type_of_product'] == "single") ? "margin-bottom:84px;" : "";
                $product_list_by_category .= '<div style="'.$btn_style.'">
                    <button type="button" title="'.trans("customer.quick_view").'" data-product-type="' . $product['type_of_product'] . '" class="btn btn-default btn-md rounded border product-quick-view mb-2 mr-5">'.trans("customer.quick_view").'</button>';
                    if((($product['type_of_product'] == "single" && $product['trackable'] == 1) || ($product['type_of_product'] == "variant" && $available_quantity != "")) && ($available_quantity <= 0)) {
                        $product_list_by_category .= '<button type="button" class="btn btn-dark btn-md rounded border product-add-to-cart add-to-cart mb-2" data-type="products-in-home" disabled>'.trans("customer.out_of_stock").'</button>';
                    } else 
                        $product_list_by_category .= '<button type="button" class="btn btn-dark btn-md rounded border product-add-to-cart add-to-cart mb-2" data-type="products-in-home">'.trans("customer.add_to_cart").'</button>';
                    $product_list_by_category .= '
                </div>';
                $product_list_by_category .= '</figcaption>
                    </figure>
                </article>
            </div>';
            }
            $product_list_by_category .= '</div></div></div>';
        }  
        if(!empty(trim($search_text)) && $product_list_by_category == "") {
            $product_list_by_category = "<h6>No results for ".$search_text."</h6><p>Search instead for ".$search_text."</p>";
        } else {
            if($product_list_by_category == "" && $page_type != "related_products") {
                $product_list_by_category = "<p class='text-center'>Sorry, no products available right now. Check back later for updates!</p>";
                $product_available = 0;
            }
            if($product_list_by_category == "" && $page_type == "related_products")
                $product_list_by_category = "<p style='margin-bottom:30px;' class='text-center'>We're sorry, but this product currently doesn't have any related products.</p>";
        }
        return response()->json(['product_list_by_category'=>$product_list_by_category,'category_list_html'=>$category_list_html,'totalPages' => $totalPages,'currentPage' => $page,'status'=>200]);
    }*/

    public function productsByCategory(Request $request) {
        $category_id = !empty($request->category_id) ? $request->category_id : "all";
        $sub_category_id = $request->sub_category_id;
        $sorting_column = $request->sorting_column;
        $sorting_order = $request->sorting_order;
        $store_id = CommonController::get_store_id();
        $store_url = $this->store_url; 
        $page = !empty($request->input('page')) ? $request->input('page') : 1;
        $search_text = $request->search_text;
        $page_type = $request->_type;
        $perPage = $request->perPage;
        $product_id = $request->product_id;
        if($page_type != "related_products") {
            $category_details = Category::select('category_name', 'category_id', 'icon')
                ->where('store_id', $store_id)
                ->where('is_deleted', 0)
                ->where('status', 1)
                ->orderByDesc('category_id')
                ->get();
        }
        $product_details_query = Product::select('store_products.product_id', 'type_of_product', 'product_name', 'store_products.category_id', 'category_name', 'unit_price', 'store_products.category_image', 'store_products.sub_category_id', 'unit', 'trackable', 'tax_amount', 'price', 'product_description','sub_category_name');
        if ($sorting_column == "low_to_high" || $sorting_column == "high_to_low") {
            $product_details_query->selectRaw('
                CASE 
                    WHEN store_products.type_of_product = "variant" THEN 
                        (SELECT variants_combination_name FROM store_product_variants_combination 
                         WHERE product_id = store_products.product_id and store_product_variants_combination.is_deleted = 0
                         ORDER BY CAST(variant_price AS DECIMAL(10,2)) '.$sorting_order.' LIMIT 1)
                    ELSE TRUE
                END as variants_combination_name
            ');
        } 
        $product_details_query->leftJoin('store_category', 'store_category.category_id', '=', 'store_products.category_id')
        ->leftJoin('store_product_variants_combination as spvc', 'store_products.product_id', '=', 'spvc.product_id')
        ->leftJoin('store_sub_category',function($join) {
            $join->on('store_sub_category.sub_category_id', '=', 'store_products.sub_category_id');
        })
        ->leftJoin('store_product_tax', 'store_products.product_id', '=', 'store_product_tax.product_id')
        ->leftJoin('store_price', 'store_products.product_id', '=', 'store_price.product_id')
        ->where('store_products.store_id', $store_id)
        ->where('store_products.is_deleted', 0)
        ->where('store_products.status_type', 'publish')
        ->where('store_products.status', 1)
        ->whereIn('store_products.product_type', ['online', 'both'])
        ->where('store_category.is_deleted', 0)
        ->where('store_category.status', 1)
        ->when($search_text, function ($query) use ($search_text) {
            $query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        })
        ->when($category_id != "all", function ($query) use ($category_id) {
            $query->where('store_products.category_id', $category_id);
        }) 
        ->when($sub_category_id, function ($query) use ($sub_category_id) {
            $query->where('store_products.sub_category_id',$sub_category_id);
        })
        ->when($page_type == "related_products", function ($query) use ($product_id) {
            $query->whereNotIn('store_products.product_id',[$product_id]);
        }) 
        ->whereRaw(('case WHEN (store_products.sub_category_id > 0) THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 ELSE TRUE END'))
        ->distinct('store_products.product_id');
        if (!empty($sorting_column) && !empty($sorting_order)) {
            if ($sorting_column == "low_to_high" || $sorting_column == "high_to_low") {
                $product_details_query->orderByRaw('
                    CASE 
                        WHEN store_products.type_of_product = "variant" THEN 
                            (SELECT CAST(variant_price AS DECIMAL(10,2)) FROM store_product_variants_combination 
                             WHERE product_id = store_products.product_id and store_product_variants_combination.is_deleted = 0
                             ORDER BY CAST(variant_price AS DECIMAL(10,2)) '.$sorting_order.' LIMIT 1)
                        ELSE CAST(price AS DECIMAL(10,2))
                    END '.$sorting_order.'
                ');
            } else {
                $product_details_query->orderBy($sorting_column, $sorting_order);
            }
        } else {
            $product_details_query->orderByDesc('store_products.category_id');
        }
        $product_details = $product_details_query->paginate($perPage);
        $all_product_data = $product_details->total();   
        $totalPages = ceil($all_product_data / $perPage);  
        $productIds = $product_details->map(function ($product) {
            return $product->product_id;
        });
        $product_variants_query = Product::leftJoin('store_product_variants', 'store_products.product_id', '=', 'store_product_variants.product_id')
            ->where('store_products.store_id', $store_id)
            ->where('store_products.is_deleted', 0)
            ->whereIn('store_products.product_type', ['online', 'both'])
            ->where('status_type', 'publish')
            ->where('type_of_product', 'variant')
            ->where('store_product_variants.is_deleted', 0);
        if ($search_text !== "") 
            $product_variants_query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        if ($category_id !== "all") 
            $product_variants_query->where('store_products.category_id', $category_id);
        if (!empty($sub_category_id))
            $product_variants_query->where('store_products.sub_category_id', $sub_category_id);
        if (!empty($productIds)) 
            $product_variants_query->whereIn('store_products.product_id', $productIds);
        $product_variants = $product_variants_query->select('variants_name','store_product_variants.product_id')->get()->toArray();
        $product_variants_collection = [];
        if(!empty($product_variants)) {
            $variantsCollection = new Collection($product_variants);
            $product_variants_collection = $variantsCollection->groupBy('product_id');
        }
        $product_variants_combinations_query = Product::leftJoin('store_product_variants_combination', 'store_products.product_id', '=', 'store_product_variants_combination.product_id')
        ->where('store_products.store_id', $store_id)
        ->where('store_products.is_deleted', 0)
        ->whereIn('store_products.product_type', ['online', 'both'])
        ->where('status_type', 'publish')
        ->where('type_of_product', 'variant')
        ->where('store_product_variants_combination.is_deleted', 0);
        if ($search_text !== "") 
            $product_variants_combinations_query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        if ($category_id !== "all") 
            $product_variants_combinations_query->where('store_products.category_id', $category_id);
        if (!empty($sub_category_id))
            $product_variants_combinations_query->where('store_products.sub_category_id', $sub_category_id);
        if (!empty($productIds)) 
            $product_variants_combinations_query->whereIn('store_products.product_id', $productIds);
        $get_product_variants_combinations = $product_variants_combinations_query->select('variants_combination_id', 'variants_combination_name', 'store_products.product_id', 'variant_price', 'on_hand')->get()->toArray();
        $cart_data = session()->get('cart', []);
        $product_variants_combinations = []; $variants_id = [];
        if(!empty($get_product_variants_combinations)) {
            foreach($get_product_variants_combinations as $key => $variants) {
                $variant_product_unit = $available_variants_quantity = $variants['on_hand'];
                if(!empty($cart_data) && isset($cart_data[$variants['product_id']]) && isset($cart_data[$variants['product_id']][$variants['variants_combination_id']])) {
                    $quantity = $cart_data[$variants['product_id']][$variants['variants_combination_id']]['quantity'];
                    if(!empty($variant_product_unit) && is_numeric($variant_product_unit) && $variant_product_unit >= 0)
                        $available_variants_quantity = ($variant_product_unit - $quantity);
                }
                $variants['product_available'] = (is_numeric($available_variants_quantity) && ($available_variants_quantity <= 0)) ? "out-of-stock" : "";
                $product_variants_combinations[$variants['product_id']][$variants['variants_combination_name']] = $variants;
                if (!isset($variants_id[$variants['product_id']])) {
                    $variants_id[$variants['product_id']] = $variants['variants_combination_id'];
                }
            }
        }
        $wishlistData = [];
        if(!empty($productIds) && auth()->guard('customer')->check()) {
            $productIdsWithVariants = [];
            foreach ($productIds as $productId) {
                if (isset($variants_id[$productId])) {
                    $productIdsWithVariants[$productId] = $variants_id[$productId];
                } else {
                    $productIdsWithVariants[$productId] = null;
                }
            }
            if(session()->has('authenticate_user'))
                $user = session('authenticate_user');
            else 
                $user = Auth::guard('customer')->user();
            $wishlistData = Wishlist::where([
                ['customer_id', '=', $user->customer_id],
                ['store_id','=',$user->store_id],
                ['is_deleted','=',0]
            ])
            ->whereIn('product_id', array_keys($productIdsWithVariants))
            // ->orWhere(function ($query) use ($productIdsWithVariants) {
            //     foreach ($productIdsWithVariants as $productId => $variantId) {
            //         if ($variantId !== null) {
            //             $query->orWhere(function ($subquery) use ($productId, $variantId) {
            //                 $subquery->where('customer_id', Auth::guard('customer')->user()->customer_id)->where('store_id', Auth::guard('customer')->user()->store_id)->where('is_deleted', 0)->where('product_id', $productId)->where('variants_id', $variantId);
            //             });
            //         }
            //     }
            // });
            ->select('wishlist_id','product_id','variants_id')->get()->toArray();
        }   
        $product_variant_details_query = Product::leftJoin('store_product_variants_combination',function($join) {
            $join->on('store_product_variants_combination.product_id', '=', 'store_products.product_id');
            $join->where('store_product_variants_combination.is_deleted', '=', 0);
        })->join('store_category',function($join) {
            $join->on('store_category.category_id', '=', 'store_products.category_id');
        })->leftJoin('store_sub_category',function($join) {
            $join->on('store_sub_category.sub_category_id', '=', 'store_products.sub_category_id');
        })->leftJoin('store_product_tax',function($join) {
            $join->on('store_product_tax.product_id', '=', 'store_products.product_id');
        })->where([
            ['store_products.store_id', '=', $store_id],
            ['store_products.is_deleted', '=', 0],
            ['store_products.status', '=', 1],
            ['store_products.status_type', '=', 'publish'],
            ['store_category.is_deleted', '=', 0],
            ['store_category.status', '=', 1],
        ])
        ->whereIn('store_products.product_type', ['online', 'both']);
        if($search_text != "")
            $product_variant_details_query->where('store_products.product_name', 'LIKE', '%' . $search_text . '%');
        $product_variant_details = $product_variant_details_query->whereRaw(('case WHEN (store_products.sub_category_id > 0) THEN store_sub_category.is_deleted = 0 AND store_sub_category.status = 1 ELSE TRUE END'))
        ->select('store_products.product_id','store_products.category_image','store_products.category_id','store_products.sub_category_id','type_of_product','trackable','variants_combination_id','variants_combination_name','variant_price','on_hand','available','store_product_variants_combination.sku','store_product_variants_combination.barcode','unit','product_name')
        ->get()->toArray();    
        $category_array = array(); $sub_category_array = array(); $category_count = array();$sub_category_count = array();$variant_combinations = []; $variant_combination_data = [];
        if(!empty($product_variant_details)) {
            foreach($product_variant_details as $product) {
                $category_array[$product['category_id']][$product['product_id']][] = $product;
                $category_count[$product['category_id']] = count($category_array[$product['category_id']]);
                if(!empty($product['sub_category_id'])) {
                    $sub_category_array[$product['category_id']][$product['sub_category_id']][$product['product_id']][] = $product;
                    $sub_category_count[$product['category_id']][$product['sub_category_id']] = count($sub_category_array[$product['category_id']][$product['sub_category_id']]);
                }
                if(!empty($product['variants_combination_id'])) {
                    $variant_combinations[$product['product_id']][] = $product;
                    $variant_combination_data[$product['variants_combination_id']] = $product;
                }
            }
        }
        if($category_id != "all" || $page_type == "product_page")  {
            $sub_category_details_query = SubCategory::select('store_sub_category.category_id','sub_category_name','sub_category_id')
            ->where([
                ['store_sub_category.store_id', '=', $store_id],
                ['store_sub_category.is_deleted', '=', 0],
                ['status', '=', 1]
            ]);
            if($category_id != "all" && $page_type != "product_page")
                $sub_category_details_query->where('store_sub_category.category_id',$category_id);
            // if (!empty($sub_category_id))
            //     $sub_category_details_query->where('store_sub_category.sub_category_id', $sub_category_id);
            $sub_category_details = $sub_category_details_query->orderBy('store_sub_category.category_id','desc')->get()->toArray();
        }
        $total_products = 0;
        if(!empty($category_count)) {
            foreach($category_count as $count) {
                $total_products += $count;
            }
        }
        $category_list_html = "";
        if(isset($category_details) && !empty($category_details) && ($total_products > 0))  {
            $active_class =  ($category_id == "all") ? "active" : "";
            if($page_type == "product_page") {
                $organizedSubcategories = array();
                if(!empty($sub_category_details)) {
                    foreach ($sub_category_details as $subcategory) {
                        $organizedSubcategories[$subcategory["category_id"]][] = $subcategory;
                    }
                }
                $active_class = ($category_id == "all") ? "active" : "";
                $category_list_html .= '<ul><li class="category-details '.$active_class.'"><input type="hidden" class="category-id" value="all" tabindex="0"><a href="#01" class="category-name">'.trans("customer.all").' <span>('.($total_products).')</span></a></li>';
                foreach ($category_details as $category) {
                    $isDisplayCategory = 0;
                    if(!empty($category_count) && array_key_exists($category->category_id,$category_count) && ($category_count[$category->category_id] > 0)) {
                        $active_class = ($category_id == $category->category_id) ? "active" : "";
                        $subCategoryDetails = (!empty($organizedSubcategories) && array_key_exists($category->category_id,$organizedSubcategories)) ? $organizedSubcategories[$category->category_id] : [];
                        if(!empty($subCategoryDetails)) {
                            foreach($subCategoryDetails as $key=> $sub_category) {
                                $sc_active_class = ($sub_category_id == $sub_category['sub_category_id']) ? "active" : "";
                                if(!empty($sub_category_count) && array_key_exists($category->category_id,$sub_category_count) && array_key_exists($sub_category['sub_category_id'],$sub_category_count[$category->category_id]) && ($sub_category_count[$category->category_id][$sub_category['sub_category_id']] > 0)) {
                                    if($isDisplayCategory == 0) {
                                        $category_list_html .= '<li class="category-details nested '.$active_class.'"><input type="hidden" class="category-id" value="'.$category->category_id.'"><a href="#01" class="toggle category-name">'.$category->category_name.' <span>('.$category_count[$category->category_id].')</span></a><ul class="nested-list">';
                                        $isDisplayCategory++;
                                    }
                                    $category_list_html.= '<li class="nested-sub-category-list sub-category-list '.$sc_active_class.' sub-category-list-'.$sub_category['sub_category_id'].'" data-sub-category-id="'.$sub_category['sub_category_id'].'">
                                                                <a href="#02" class="sub-category-li nested-sub-category-name">'.$sub_category['sub_category_name'].' <span>('.$sub_category_count[$category->category_id][$sub_category['sub_category_id']].')</span> <span class="product-count-sub-category"></span></a>
                                                            </li>';
                                    if($key == (count($subCategoryDetails) - 1))
                                        $category_list_html.= '</ul></li>';
                                } 
                            }
                            if($isDisplayCategory == 0) {
                                $category_list_html .= '<li class="category-details '.$active_class.'"><input type="hidden" class="category-id" value="'.$category->category_id.'"><a href="#01" class="category-name">'.$category->category_name.' <span>('.$category_count[$category->category_id].')</span></a></li>';
                                $isDisplayCategory++;
                            }
                        }
                        else 
                            $category_list_html .= '<li class="category-details '.$active_class.'"><input type="hidden" class="category-id" value="'.$category->category_id.'"><a href="#01" class="category-name">'.$category->category_name.' <span>('.$category_count[$category->category_id].')</span></a></li>';
                    }
                }
                $category_list_html .= '</ul>';
            } else {
                $category_list_html .= 
                '<div class="col-lg-4 category-details">
                    <div class="single_featured_banner wow fadeInUp '.$active_class.'" data-wow-delay="0.1s" data-wow-duration="1.1s">
                        <div class="featured_banner_text d-flex justify-content-between align-items-center">
                            <input type="hidden" class="category-id" value="all">
                            <h3><a href="#0">'.trans("customer.all").'</a></h3>
                            <span class="all-product-count">('.($total_products).')</span> 
                        </div>
                    </div>
                </div>'; 
                foreach ($category_details as $category) {
                    if(!empty($category_count) && array_key_exists($category->category_id,$category_count) && ($category_count[$category->category_id] > 0)) {
                        $active_class = ($category_id == $category->category_id) ? "active" : "";
                        $category_list_html .= 
                        '<div class="col-lg-4 category-details">
                            <div class="single_featured_banner wow fadeInUp '.$active_class.'" data-wow-delay="0.1s" data-wow-duration="1.1s">
                                <div class="featured_banner_text d-flex justify-content-between align-items-center">
                                    <input type="hidden" class="category-id" value="'.$category->category_id.'">
                                    <h3><a href="#0">'.$category->category_name.'</a></h3>
                                    <span class="category-product-count">('.$category_count[$category->category_id].')</span>
                                </div>
                            </div>
                        </div>'; 
                    }
                }
            }
        }
        $product_list_by_category = "";
        if($category_id != "all" && $page_type != "product_page" && $page_type != "related_products") {
            $product_list_by_category .= 
                '<div class="product_header">
                    <div class="product_tab_button">
                        <ul class="nav featured_banner_inner sub-category-list-details">';
                            if(isset($sub_category_details)) {
                                $product_list_by_category.= '<li class="sub-category-list" data-sub-category-id="all">
                                                                <a class="active sub-category-li" href="#01">'.trans("customer.all").'<span class="all-sub-category-li"></span></a>
                                                            </li>';
                            }
                            if(!empty($sub_category_details)) {
                                foreach($sub_category_details as $sub_category) {
                                    if(!empty($sub_category_count) && array_key_exists($category_id,$sub_category_count) && array_key_exists($sub_category['sub_category_id'],$sub_category_count[$category_id]) && ($sub_category_count[$category_id][$sub_category['sub_category_id']] > 0)) {
                                        $product_list_by_category.= '<li class="sub-category-list sub-category-list-'.$sub_category['sub_category_id'].'" data-sub-category-id="'.$sub_category['sub_category_id'].'">
                                                                    <a href="#02" class="sub-category-li">'.$sub_category['sub_category_name'].' <span class="product-count-sub-category"></span></a>
                                                                </li>';
                                    }
                                }
                            }
            $product_list_by_category .= '</ul></div></div>';
        }
        $productDetailsArray = $product_details->toArray();
        $product_details = $productDetailsArray['data'];
        if (isset($product_details) && !empty($product_details)) {
            $product_list_by_category .= '<div class="tab-content product_container">';
            if (auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id) 
                $isAuthenticated = 1;
            else 
                $isAuthenticated = 0;
            $product_list_by_category .= '<div class="product_gallery"><input type="hidden" class="is_authenticated" value="' . $isAuthenticated . '"><div class="row">';
            foreach ($product_details as $product) {
                $product_images = !empty($product) && !empty($product['category_image']) ? explode("***", $product['category_image']) : [];
                $product_list_by_category .=
                    '<div class="col-lg-4 col-md-4 col-sm-6 sub-category-product-list sub-category-product-' . $product['sub_category_id'] . '">
                        <article class="single_product single-product-details single-product-details-'.$product['product_id'].'">';
                if (!empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) {
                    $product_list_by_category .= '<input type="hidden" class="variant-combinations variant-combinations-' . $product['product_id'] . '" value="' . htmlspecialchars(json_encode($product_variants_combinations[$product['product_id']]), ENT_QUOTES, 'UTF-8') . '">';
                } else {
                    $product_list_by_category .= '<input type="hidden" class="variant-combinations variant-combinations-' . $product['product_id'] . '" value="">';
                }
                $product_id = $product["product_id"];
                if(($sorting_column == "low_to_high" || $sorting_column == "high_to_low") && $product['type_of_product'] == "variant" && $page_type == "product_page")
                    $variants_id = $variants_name = $product['variants_combination_name'];
                else 
                    $variants_id = $variants_name = (!empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) ? key($product_variants_combinations[$product['product_id']]) : "";                     
                $variants_id = ($variants_id) != "" ? ($product_variants_combinations[$product_id][$variants_id]['variants_combination_id']) : "";
                $variants_on_hand = ""; $product_quantity = 1; 
                $product_unit = $available_quantity = ($product['type_of_product'] == "variant" && !empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) ? $product_variants_combinations[$product_id][$variants_name]['on_hand'] : $product['unit'];
                if(!empty($cart_data) && isset($cart_data[$product_id])) {
                    if($product['type_of_product'] == "variant" && isset($cart_data[$product_id][$variants_id])) {
                        $quantity = $cart_data[$product_id][$variants_id]['quantity'];
                        if(!empty($product_unit) && is_numeric($product_unit) && $product_unit >= 0) {
                            $variants_on_hand = $available_quantity = ($product_unit - $quantity);
                        }
                    } 
                    else if($product['type_of_product'] == "single") {
                        $quantity = $cart_data[$product_id]['quantity'];
                        $product_unit = $available_quantity = $product['unit'] - $quantity;
                    }
                }
                $product_list_by_category .= '<figure>
                        <div class="product_thumb">';
                            if((($product['type_of_product'] == "single" && $product['trackable'] == 1) || ($product['type_of_product'] == "variant" && $available_quantity != "")) && ($available_quantity <= 0)) {
                                $product_list_by_category .= '<div class="outofstock_button"><a href="#" title="">'.trans("customer.out_of_stock").'</a></div>';
                            }
                            $product_list_by_category .= '<a class="single-product-url" href="' . route($store_url . '.customer.single-product', Crypt::encrypt($product['product_id'])) . '"><img class="product-image-path" src="' . (!empty($product_images) && count($product_images) > 0 ? $product_images[0] : "") . '" alt=""></a>
                            <div class="quickview_button"><a href="#" title="'.trans("customer.quick_view").'" data-product-type="' . $product['type_of_product'] . '" class="product-quick-view"><span class="pe-7s-shopbag"></span> '.trans("customer.quick_view").'</a></div>
                            <div class="action_links2">
                                <ul class="d-flex justify-content-center">';
                                    // if((($product['type_of_product'] == "single" && $product['trackable'] == 1) || ($product['type_of_product'] == "variant" && $available_quantity != "")) && ($available_quantity <= 0)) {
                                    //     $product_list_by_category .= '<li class="add_to_cart"><a href="#01" class="product-add-to-cart add-to-cart" title="'.trans("customer.out_of_stock").'" data-type="products-in-home" disabled><span class="pe-7s-close-circle"></span></a></li>';
                                    // } else 
                                    //     $product_list_by_category .= '<li class="add_to_cart"><a href="#01" class="product-add-to-cart add-to-cart" title="Add to cart" data-type="products-in-home"><span class="pe-7s-shopbag"></span></a></li>';
                                    if (auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id) {
                                        $wishlist_class = "far"; $wishlist_type = "add"; $title = trans("customer.add_to_wishlist");
                                        $productExistsInWishlist = count(array_filter($wishlistData, function ($item) use ($product_id, $variants_id) {
                                            return $item['product_id'] == $product_id && $item['variants_id'] == $variants_id;
                                        })) > 0;
                                        if ($productExistsInWishlist) { 
                                            $wishlist_class = "fas"; $wishlist_type = "remove"; $title = trans("customer.remove_from_wishlist");
                                        }
                                        $product_list_by_category .=  '<li class="wishlist"><a href="#01" title="'.$title.'" class="product-wishlist"><span data-type="products-in-home" data-wishlist-type="'.$wishlist_type.'" class="wishlist-icon fa-heart '.$wishlist_class.'"></span></a></li>';
                                    }
                                    // $product_list_by_category .= '<li class="quick_button"><a href="#01" title="Quick View" data-product-type="' . $product['type_of_product'] . '" class="product-view"><span class="pe-7s-look"></span></a></li>
                                    $product_list_by_category .= '
                                </ul>
                            </div>
                        </div>
                        <figcaption class="product_content text-center">
                            <div class="justify-content-between"> 
                                <h4 class="me-2 mb-2 fw-bold text-center"><a href="' . route($store_url . '.customer.single-product', ['id' => Crypt::encrypt($product['product_id']), 'type' => Crypt::encrypt($page_type)]) . '" class="product-name truncate-text"  data-bs-toggle="tooltip" title="'.$product["product_name"].'">' . $product['product_name'] . '</a></h4>
                                <input type="hidden" class="product-id single-product-id" value="' . $product['product_id'] . '">
                                <input type="hidden" class="product-trackable single-product-trackable" value="' . $product['trackable'] . '">
                                <input type="hidden" class="product-category-name" value="'.$product['category_name'].'">
                                <input type="hidden" class="product-subcategory-name" value="'.$product['sub_category_name'].'">
                                <input type="hidden" class="product-unit" value="' . $product['unit'] . '">
                                <input type="hidden" class="single-product-variants-combination" value="'.$variants_id.'">
                                <input type="hidden" class="variant-on-hand" value="'.$product_unit.'">
                                <input type="hidden" class="modal-variant-on-hand" value="'.$variants_on_hand.'">
                                <input type="hidden" class="modal-product-unit" value="'.$product_unit.'">
                                <input type="hidden" class="single-product-type" value="' . $product['type_of_product'] . '">
                                <input type="hidden" class="single-product-description" value="' . htmlentities($product['product_description'], ENT_QUOTES, 'UTF-8') . '">
                                <input type="hidden" class="add-product-quantity quantity" value="1"> 
                                <input type="hidden" class="product-category-images" value="'.$product['category_image'].'">
                                <div class="price_box mb-2">
                                    <span class="current_price fw-bold product-price modal-product-price">';
                if ($product["type_of_product"] == "variant") {
                    $product_list_by_category .=  (!empty($product_variants_combinations) && isset($product_variants_combinations[$product['product_id']])) ? 'SAR '.number_format($product_variants_combinations[$product_id][$variants_name]['variant_price'], 2, '.', '') : "";
                } else {
                    $product_list_by_category .= ($product['type_of_product'] == "single") ? "SAR " . number_format($product['price'], 2, '.', '') : "";
                }
                $product_list_by_category .= '</span>
                                </div>
                            </div>';
                if (!empty($product_images) && count($product_images) > 0) {
                    $image_class = (count($product_images) > 7) ? "slick-carousel" : "";
                    $product_list_by_category .= '<div class="mb-3 d-flex flex-nowrap justify-content-center align-items-center '.$image_class.'">';
                    foreach ($product_images as $product_img) {
                        $product_list_by_category .= '<div>
                            <button type="button" class="btn p-1 d-flex align-items-center justify-content-center border rounded-circle fs-5 text-primary product-images-btn" >
                                <img src="' . $product_img . '" alt="" class="product-img rounded-circle avatar-xs">
                            </button>
                        </div>';
                    }
                    $product_list_by_category .= '</div>';
                }
                // if($product['type_of_product'] == "variant" && !empty($product_variants_combinations) && count($product_variants_combinations) > 0 && isset($product_variants_combinations[$product['product_id']]) && !empty(isset($product_variants_combinations[$product['product_id']]))) {
                //     $product_list_by_category .= '<div class="mb-3">';
                //         if(!empty($product_variants_collection) && isset($product_variants_collection[$product['product_id']]) && count($product_variants_collection[$product['product_id']]) > 0) {
                //             $variants = $product_variants_collection[$product['product_id']];
                //             $variants_title = count($variants) == 1 ? "Select a" : "Select";
                //             foreach($variants as $index => $variant) {
                //                 if ($index == (count($variants) - 1) && $index !== 0) {
                //                     $variants_title .= ' and ' . $variant['variants_name'];
                //                 } else {
                //                     $variants_title .= " ".$variant['variants_name'];
                //                     if($index != (count($variants) - 2) && $index != (count($variants) - 1))
                //                         $variants_title .= ",";
                //                 }
                //             }
                //         } else 
                //             $variants_title = "Select a variant";
                //     $product_list_by_category .= '<p class="text-center">'.$variants_title.'</p>';
                //     $variants_carousel_class = ($page_type == "product_page" && count($product_variants_combinations[$product['product_id']]) > 2) ? "slick-variants-carousel" : (count($product_variants_combinations[$product['product_id']]) > 3) ? "slick-variants-carousel" : "";
                //     $product_list_by_category .= '<div class="d-flex flex-wrap gap-2 justify-content-center align-items-center mb-2 '.$variants_carousel_class.'">';
                //             foreach($product_variants_combinations[$product['product_id']] as $key => $variant) {
                //                 $firstKey = ((($sorting_column == "low_to_high" || $sorting_column == "high_to_low") && $product['type_of_product'] == "variant" && $page_type == "product_page")) ? $product['variants_combination_name'] : key($product_variants_combinations[$product['product_id']]);
                //                 $checked = ($firstKey == $key) ? "checked" : "";
                //                 $checked_style = ($firstKey == $key) ? "background-color: rgb(108, 117, 125); color: rgb(255, 255, 255); border-color: rgb(108, 117, 125);" : "";
                //                 $product_list_by_category .= '<div class="product-variant-dev">
                //                     <input type="radio" class="btn-check product-variant" data-type="products-in-home" name="product_variant_'.$product['product_id'].'" id="product-variant-'.$variant['variants_combination_id'].'" value="'.$variant["variants_combination_id"].'" '.$checked.'>
                //                     <label style="'.$checked_style.'" class="btn btn-outline-secondary avatar-xs-1 rounded-4 d-flex justify-content-center align-items-center product-variant-label '.$variant['product_available'].'" for="product-variant-'.$variant['variants_combination_id'].'" data-bs-toggle="tooltip" data-variant-combination ="'.$variant["variants_combination_name"].'" title="'.$variant["variants_combination_name"].'">'.$variant["variants_combination_name"].'</label>
                //                 </div>';
                //             }
                //         $product_list_by_category .= '</div>
                //     </div>';
                // }
                // $btn_style = ($product['type_of_product'] == "single") ? "margin-bottom:84px;" : "";
                $product_list_by_category .= '<div>';
                    // <button type="button" title="'.trans("customer.quick_view").'" data-product-type="' . $product['type_of_product'] . '" class="btn btn-default btn-md rounded border product-quick-view mb-2 mr-5">'.trans("customer.quick_view").'</button>';
                    // if((($product['type_of_product'] == "single" && $product['trackable'] == 1) || ($product['type_of_product'] == "variant" && $available_quantity != "")) && ($available_quantity <= 0)) {
                    //     $product_list_by_category .= '<button type="button" class="btn btn-dark btn-md rounded border product-add-to-cart add-to-cart mb-2" data-type="products-in-home" disabled>'.trans("customer.out_of_stock").'</button>';
                    // } else 
                    //     $product_list_by_category .= '<button type="button" class="btn btn-dark btn-md rounded border product-add-to-cart add-to-cart mb-2" data-type="products-in-home">'.trans("customer.add_to_cart").'</button>';
                    $product_list_by_category .= '
                </div>';
                $product_list_by_category .= '</figcaption>
                    </figure>
                </article>
            </div>';
            }
            $product_list_by_category .= '</div></div></div>';
        }  
        if(!empty(trim($search_text)) && $product_list_by_category == "") {
            $product_list_by_category = "<h6>No results for ".$search_text."</h6><p>Search instead for ".$search_text."</p>";
        } else {
            if($product_list_by_category == "" && $page_type != "related_products") {
                $product_list_by_category = "<p class='text-center'>Sorry, no products available right now. Check back later for updates!</p>";
                $product_available = 0;
            }
            if($product_list_by_category == "" && $page_type == "related_products")
                $product_list_by_category = "<p style='margin-bottom:30px;' class='text-center'>We're sorry, but this product currently doesn't have any related products.</p>";
        }
        return response()->json(['product_list_by_category'=>$product_list_by_category,'category_list_html'=>$category_list_html,'totalPages' => $totalPages,'currentPage' => $page,'status'=>200]);
    }

    public function variantsByProduct(Request $request) {
        $store_id = CommonController::get_store_id();
        $product_id = $request->product_id;
        $product_variants = Variants::select('variants_name')
        ->where([
            ['store_id', '=', $store_id], 
            ['is_deleted', '=', 0], 
            ['product_id', '=', $product_id]
        ])
        ->get()->toArray();
        if(!empty($product_variants) && count($product_variants) > 0) {
            $variants_title = count($product_variants) == 1 ? "Select a" : "Select";
            foreach($product_variants as $index => $variant) {
                if ($index == (count($product_variants) - 1) && $index !== 0) {
                    $variants_title .= ' and ' . $variant['variants_name'];
                } else {
                    $variants_title .= " ".$variant['variants_name'];
                    if($index != (count($product_variants) - 2) && $index != (count($product_variants) - 1))
                        $variants_title .= ",";
                }
            }
        } else 
            $variants_title = "Select a variant";
        $product_variants_combinations = Product::leftJoin('store_product_variants_combination', 'store_products.product_id', '=', 'store_product_variants_combination.product_id')
        ->where([
            ['store_products.store_id', '=', $store_id], 
            ['store_products.is_deleted', '=', 0],
            ['status_type', '=', 'publish'],
            ['store_product_variants_combination.is_deleted', '=', 0],
            ['store_products.product_id', '=', $product_id]
        ])
        ->whereIn('store_products.product_type', ['online', 'both'])
        ->select('variants_combination_id','variants_combination_name','store_products.product_id','variant_price','on_hand')->get()->toArray();
        if(!empty($product_variants_combinations)) {
            $cart_data = session()->get('cart', []);
            foreach($product_variants_combinations as $key => $variants) {
                $product_unit = $available_quantity = $variants['on_hand'];
                if(!empty($cart_data) && isset($cart_data[$product_id]) && isset($cart_data[$product_id][$variants['variants_combination_id']])) {
                    $quantity = $cart_data[$product_id][$variants['variants_combination_id']]['quantity'];
                    if(!empty($product_unit) && is_numeric($product_unit) && $product_unit >= 0)
                    $available_quantity = ($product_unit - $quantity);
                }
                $product_variants_combinations[$key]['product_available']  = (is_numeric($available_quantity) && ($available_quantity <= 0)) ? "out-of-stock" : "";
            }
        }
        return response()->json(['product_variants_combinations'=>$product_variants_combinations, 'variants_title'=>$variants_title]);
    }
}
