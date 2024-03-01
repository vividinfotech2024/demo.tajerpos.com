<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <?php echo $__env->make('common.customer.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/> -->
        <style>
            .cart-variants {
                cursor : pointer;
            }
            .mb-15 {
                margin-bottom: 15px !important;
            }
            .mt-20 {
                margin-top: 20px !important;
            }
        </style>
    </head>
    <body>
        <div class="body_overlay"></div>
        <?php echo $__env->make('common.customer.mobile_navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.mini_cart', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.breadcrumbs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <input type="hidden" class="translation-key" value="view_cart_page_title">
        <div class="cart-area">
            <div class="container">
                <div class="cko-progress-tracker">
                    <div class="step-1" id="checkout-progress" data-current-step="1">
                        <div class="progress-bar">
                            <div class="step step-1 current">
                                <a href="<?php echo e(route($store_url.'.customer.view-cart')); ?>">
                                <span> 1</span>
                                <div class="step-label"><?php echo e(__('customer.bag')); ?></div>
                                </a>
                            </div>
                            <div class="step step-2">
                                <span> 2</span>
                                <div class="step-label"><?php echo e(__('customer.sign_in')); ?></div>
                            </div>
                            <div class="step step-3">
                                <span> 3</span>
                                <div class="step-label"><?php echo e(__('customer.delivery_and_payment')); ?></div>
                            </div>
                            <div class="step step-4">
                                <span> 4</span>
                                <div class="step-label"><?php echo e(__('customer.confirmation')); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-5 shopping-bag-list">
                    <input type="hidden" class="global-tax-percentage" value="<?php echo e(isset($tax_details) && count($tax_details) > 0 ? $tax_details[0]['tax_percentage'] : 0); ?>">
                    <div class="col-md-7">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="shopping-cart-count item_count shopping-bag-details"><?php echo e(__('customer.my_bag')); ?></h5>
                            </div>
                            <div class="col-md-4 text-end">
                                <?php if((auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id)): ?>
                                    <a href="<?php echo e(route($store_url.'.customer.wishlist.index')); ?>"><i class='fa fa-plus'></i> <?php echo e(__('customer.add_from_wishlist')); ?></a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="shopping-cart-body">
                            <?php if(isset($product_details) && !empty($product_details) && count($product_details) > 0): ?>
                                <?php $__currentLoopData = $product_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <hr/>
                                    <?php if($product->type_of_product == "variant"): ?>
                                        <div class="<?php echo e(($product->product_available == 'out-of-stock') ? 'out-of-stock-cart' : ''); ?> product-cart-list product-cart-list-<?php echo e($product->product_id); ?>-<?php echo e($product->variants_combination_id); ?>" data-element="product-cart-list-<?php echo e($product->product_id); ?>-<?php echo e($product->variants_combination_id); ?>">
                                            <?php if($product->product_available == "out-of-stock"): ?>
                                                <div class="mb-15">
                                                    <span class="text-danger"><b><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?php echo e(__('customer.out_of_stock')); ?></b></span><br/>
                                                    <span><?php echo e(__('customer.unavailable_products')); ?></span>
                                                </div>
                                            <?php elseif($product->on_hand != "" && $product->on_hand < $get_quantity[$product->product_id][$product->variants_combination_id]): ?>
                                                <div class="mb-15 unavailable-error-message"> 
                                                    <span class="text-danger"><?php echo e(__('customer.quantity_adjusted',['on_hand' => $product->on_hand])); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex gap-3 justify-content-between mb-3">
                                                <input type="hidden" class="product-name" value="<?php echo e($product->product_name); ?>"> 
                                                <input type="hidden" class="select-variants" value="<?php echo e($product->variants_combination_id); ?>"> 
                                                <input type="hidden" class="variants-quantity" value="<?php echo e($product->on_hand); ?>">  
                                                <input type="hidden" class="selected-combination-name" value="<?php echo e($product->variants_combination_name); ?>">
                                                <input type="hidden" class="variants-combination-json variant-combinations-<?php echo e($product->product_id); ?>" value="<?php echo e((isset($variants_combinations_data) && !empty($variants_combinations_data) && isset($variants_combinations_data[$product->product_id]) ? json_encode($variants_combinations_data[$product->product_id]) : '')); ?>">
                                                <div class="d-flex gap-3">
                                                    <div class="">
                                                        <?php 
                                                            $category_image = !empty($product->category_image) ? explode("***",$product->category_image) : [];
                                                        ?>
                                                        <?php if(count($category_image) > 0): ?>
                                                            <img style="width: 70px;" src="<?php echo e($category_image[0]); ?>" alt="Product Image">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="">
                                                        <p class="mb-2 fw-bold"><a href="<?php echo e(route($store_url . '.customer.single-product', ['id' => Crypt::encrypt($product->product_id), 'type' => Crypt::encrypt('product_page')])); ?>" class="product-name"><?php echo e($product['product_name']); ?></a></p>
                                                        <p class="fw-bolder mb-2 single-product-price">SAR <?php echo e(number_format((float)($product->variant_price), 2, '.', '')); ?></p>
                                                        <input type="hidden" class="variants-title" value="<?php echo e((isset($product_variants_title) && !empty($product_variants_title) && isset($product_variants_title[$product->product_id])) ? $product_variants_title[$product->product_id] : ''); ?>">                                                   
                                                        <?php if($product->product_available == "out-of-stock"): ?>
                                                            <p class="mb-0">
                                                                <?php echo e((isset($product_variants_title) && !empty($product_variants_title) && isset($product_variants_title[$product->product_id])) ? $product_variants_title[$product->product_id] : ""); ?> : <?php echo e($product->variants_combination_name); ?>

                                                            </p>
                                                        <?php else: ?>
                                                            <p class="mb-0 cart-variants" data-bs-toggle="modal" data-bs-target="#cart-variants-modal">
                                                                <?php echo e((isset($product_variants_title) && !empty($product_variants_title) && isset($product_variants_title[$product->product_id])) ? $product_variants_title[$product->product_id] : ""); ?> : <?php echo e($product->variants_combination_name); ?> <i class="fa fa-chevron-down" aria-hidden="true"></i>
                                                            </p> 
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="product_pro_button quantity1 mb-3">
                                                        <?php if($product->product_available != "out-of-stock"): ?>
                                                            <div class="pro-qty border number product-item">
                                                                <input type="hidden" class="product-price" value="<?php echo e($product->variant_price); ?>">
                                                                <input type="hidden" class="product-unit-price" value="<?php echo e($product->variant_price); ?>">
                                                                <input type="hidden" class="product-quantity" value="<?php echo e($get_quantity[$product->product_id][$product->variants_combination_id]); ?>">
                                                                <input type="hidden" class="product-id" value="<?php echo e($product->product_id); ?>">
                                                                <input type="hidden" class="tax-type" value="<?php echo e($product->tax_type); ?>"> 
                                                                <input type="hidden" class="tax-amount" value="<?php echo e($product->tax_amount); ?>">
                                                                <input type="hidden" class="get-product-quantity" value="<?php echo e($product->variant_quantity); ?>">
                                                                <input type="hidden" class="trackable" value="<?php echo e($product->trackable); ?>">
                                                                <input type="hidden" class="product-unit" value="<?php echo e($product->unit); ?>"> 
                                                                <input type="hidden" class="type-of-product" value="<?php echo e($product->type_of_product); ?>">
                                                                <input type="text" name="product_item[<?php echo e($product->category_id); ?>][<?php echo e($product->product_id); ?>][]" value="<?php echo e(($product->on_hand != '' && $product->on_hand < $get_quantity[$product->product_id][$product->variants_combination_id]) ? $product->on_hand :  $get_quantity[$product->product_id][$product->variants_combination_id]); ?>" class="quantity" onkeypress="return isNumber(event)" style="height: 35px;">
                                                                <input type="hidden" name="product_amount[<?php echo e($product->category_id); ?>][<?php echo e($product->product_id); ?>][]" class="total-product-price" value="<?php echo e($get_quantity[$product->product_id][$product->variants_combination_id] * $product->variant_price); ?>">
                                                                <input type="hidden" name="product_tax_amount[<?php echo e($product->category_id); ?>][<?php echo e($product->product_id); ?>][]" class="product-tax-amount" value="">
                                                                <input type="hidden" name="no_of_products" class="no-of-products" value="">
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <ul class="d-flex justify-content-center mb-0"> 
                                                        <li class="delete-product-item mr-30">
                                                            <a href="#01" title="Delete" class="text-danger mb-0">
                                                                <?php echo ($product->product_available == "out-of-stock") ? __('customer.remove_from_bag') : '<i class="fa fa-trash-o"></i> '.__('customer.delete'); ?>

                                                            </a>
                                                        </li>
                                                        <?php if((auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id)): ?>
                                                            <li class="wishlist move-to-wishlist"><a href="#01" title="<?php echo e(__('customer.move_to_wishlist')); ?>" class="text-primary mb-0"><i data-wishlist-type="add" class="wishlist-icon fa fa-heart-o"></i> <?php echo e(__('customer.move_to_wishlist')); ?></a></li> 
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="<?php echo e(($product->trackable == 1 && $product->unit <= 0) ? 'out-of-stock-cart' : ''); ?> product-cart-list">
                                            <?php if($product->trackable == 1 && $product->unit <= 0): ?>
                                                <div class="mb-15">
                                                    <span class="text-danger"><b><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <?php echo e(__('customer.out_of_stock')); ?></b></span><br/>
                                                    <span><?php echo e(__('customer.unavailable_products')); ?></span>
                                                </div>
                                            <?php elseif($product->trackable == 1 && $product->unit < $get_quantity[$product->product_id]): ?>
                                                <div class="mb-15 unavailable-error-message"> 
                                                    <span class="text-danger"><?php echo e(__('customer.quantity_adjusted',['on_hand' => $product->unit])); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="d-flex gap-3 justify-content-between mb-3">
                                                <input type="hidden" class="product-name" value="<?php echo e($product->product_name); ?>"> 
                                                <div class="d-flex gap-3">
                                                    <div class="">
                                                        <?php 
                                                            $category_image = !empty($product->category_image) ? explode("***",$product->category_image) : [];
                                                        ?>
                                                        <?php if(count($category_image) > 0): ?>
                                                            <img style="width: 70px;" src="<?php echo e($category_image[0]); ?>" alt="Cart Thumbnail">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="">
                                                        <p class="mb-2 fw-bold"><a href="<?php echo e(route($store_url . '.customer.single-product', ['id' => Crypt::encrypt($product->product_id), 'type' => Crypt::encrypt('product_page')])); ?>" class="product-name"><?php echo e($product['product_name']); ?></a></p>
                                                        <p class="fw-bolder mb-2 single-product-price">SAR <?php echo e(number_format((float)($product->price), 2, '.', '')); ?></p>
                                                    </div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="product_pro_button quantity1 mb-3">
                                                        <?php if(($product->trackable == 1 && $product->unit > 0) || $product->trackable == 0): ?>
                                                            <div class="pro-qty border number product-item">
                                                                <input type="hidden" class="product-price" value="<?php echo e($product->price); ?>">
                                                                <input type="hidden" class="product-unit-price" value="<?php echo e($product->price); ?>">
                                                                <input type="hidden" class="product-quantity" value="<?php echo e($get_quantity[$product->product_id]); ?>">
                                                                <input type="hidden" class="product-id" value="<?php echo e($product->product_id); ?>">
                                                                <input type="hidden" class="tax-type" value="<?php echo e($product->tax_type); ?>"> 
                                                                <input type="hidden" class="tax-amount" value="<?php echo e($product->tax_amount); ?>">
                                                                <input type="hidden" class="get-product-quantity" value="<?php echo e($product->unit); ?>">
                                                                <input type="hidden" class="trackable" value="<?php echo e($product->trackable); ?>">
                                                                <input type="hidden" class="product-unit" value="<?php echo e($product->unit); ?>"> 
                                                                <input type="hidden" class="type-of-product" value="<?php echo e($product->type_of_product); ?>">
                                                                <input type="text" name="product_item[<?php echo e($product->category_id); ?>][<?php echo e($product->product_id); ?>][]" value="<?php echo e(($product->trackable == 1 && $product->unit < $get_quantity[$product->product_id]) ? $product->unit : $get_quantity[$product->product_id]); ?>" class="quantity" onkeypress="return isNumber(event)" style="height: 35px;">
                                                                <input type="hidden" name="product_amount[<?php echo e($product->category_id); ?>][<?php echo e($product->product_id); ?>][]" class="total-product-price" value="<?php echo e($get_quantity[$product->product_id] * $product->price); ?>">
                                                                <input type="hidden" name="product_tax_amount[<?php echo e($product->category_id); ?>][<?php echo e($product->product_id); ?>][]" class="product-tax-amount" value="">
                                                                <input type="hidden" name="no_of_products" class="no-of-products" value="">
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <ul class="d-flex justify-content-center mb-0"> 
                                                        <li class="delete-product-item mr-30">
                                                            <a href="#01" title="Delete" class="text-danger mb-0">
                                                                <?php echo ($product->trackable == 1 && $product->unit <= 0) ? __('customer.remove_from_bag') : '<i class="fa fa-trash-o"></i> '.__('customer.delete'); ?>

                                                            </a>
                                                        </li>
                                                        <?php if((auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id)): ?>
                                                            <li class="wishlist move-to-wishlist"><a href="#01" title="<?php echo e(__('customer.move_to_wishlist')); ?>" class="text-primary mb-0"><i data-wishlist-type="add" class="wishlist-icon fa fa-heart-o"></i> <?php echo e(__('customer.move_to_wishlist')); ?></a></li> 
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php else: ?>
                                <p class='empty-row text-center'><?php echo e(__('customer.empty_bag_desc')); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-4">
                        <!-- <h5 class="mb-5">have a promo code?</h5>
                        <div class="coupon-all w-100 ">
                            <div class="coupon w-100 d-flex mb-4">
                                <input id="coupon_code" class="input-text w-75" name="coupon_code" value="" placeholder="Coupon code" type="text">
                                <input class="button mt-xxs-30 w-25" name="apply_coupon" value="Apply"type="submit">
                            </div>
                        </div> -->
                        <h5 class="mb-3"><?php echo e(__('customer.order_summary')); ?></h5>
                        <div class="cart-page-total">
                            <table class="table table-borderless align-middle mb-0">
                                <tbody>
                                <tr>
                                    <td class="fw-semibold" colspan="2"><?php echo e(__('customer.sub_total')); ?> :</td>
                                    <td class="fw-semibold text-end sub-total-amount"></td>
                                </tr>
                                <!-- <tr>
                                    <td colspan="2">Discount <span class="text-muted">(STEEX30)</span> : </td>
                                    <td class="text-end">- ₹681.89</td>
                                </tr>
                                <tr>
                                    <td colspan="2">Shipping Charge :</td>
                                    <td class="text-end">₹49.99</td>
                                </tr> -->
                                <tr>
                                    <td colspan="2"><?php echo e(__('customer.estimated_tax')); ?> (<?php echo e(isset($tax_details) && count($tax_details) > 0 && isset($tax_details[0]['tax_percentage']) && !empty($tax_details[0]['tax_percentage']) ? preg_replace('/\.?0+%?$/', '%', $tax_details[0]['tax_percentage']) : "0"); ?>): </td>
                                    <td class="text-end total-tax-amount"></td>
                                </tr>
                                <tr class="border-top border-dashed">
                                    <th colspan="2"><?php echo e(__('customer.grand_total')); ?> :</th>
                                    <td class="text-end">
                                        <span class="fw-semibold total-cart-amount"></span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="w-100 proceed-to-checkout-btn">
                                <?php 
                                    $route_url = (auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id) ? route($store_url.'.customer.checkout') : url($store_url.'/customer-login/'.Crypt::encrypt("placeorder"));
                                ?>
                                <a class="w-100 text-center" href="<?php echo e($route_url); ?>"><?php echo e(__('customer.proceed_to_checkout')); ?></a>
                            </div>
                        </div>
                        <hr/>
                        <div class="text-center">
                            <img src="<?php echo e(URL::asset('assets/customer/images/others/paypal.png')); ?>" alt="payments">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal" id="cart-variants-modal">
            <div class="modal-dialog modal-sm custom-modal product-cart-list single-product-details">
                <div class="modal-content">
                    <div class="modal-header">
                        <h6 class="modal-title modal-product-title"></h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <span class="modal-variant-title"></span>
                        <div class="product-variants-data mb-15 mt-20"></div>
                        <span><b><?php echo e(__('customer.select_quantity')); ?></b></span>
                        <div class="product_pro_button quantity1 mb-3 mt-20">
                            <div class="pro-qty border number product-item">
                                <!-- <input type="hidden" class="product-quantity" value="">-->
                                <input type="hidden" class="single-product-variants-combination" value=""> 
                                <input type="hidden" class="product-id" value="">
                                <input type="hidden" class="type-of-product single-product-type" value="">
                                <input type="hidden" class="variants-quantity" value="">
                                <input type="hidden" class="product-price" value="">
                                <input type="hidden" class="selected-element-class" value="">
                                <input type="text" name="" value="1" class="quantity" data-type="product-in-popup" onkeypress="return isNumber(event)" style="height: 35px;">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark w-100 cart-update"><?php echo e(__('customer.update')); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $__env->make('common.customer.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <script>
            function validateQuantity(_this) {
                quantity = _this.closest(".product-cart-list").find(".quantity").val(); 
                type_of_product = _this.closest(".product-cart-list").find(".type-of-product").val();
                if(type_of_product == "single")
                    product_unit = _this.closest(".product-cart-list").find(".product-unit").val(); 
                else if(type_of_product == "variant")
                    product_unit = _this.closest(".product-cart-list").find(".variants-quantity").val();
                trackable = _this.closest(".product-cart-list").find(".trackable").val(); 
                if((type_of_product == "single" && trackable == 1 || type_of_product == "variant") && product_unit != "" && product_unit != undefined && (parseInt(quantity) > parseInt(product_unit))) {
                    _this.closest(".product-cart-list").find(".quantity").val(product_unit); 
                    toastr.options =
                    {
                        "closeButton" : true,
                        "progressBar" : true
                    } 
                    toastr.error(customerTranslations['quantity_exceeds_stock'].replace(":unit",product_unit));
                }
            }
            function addProductToCart(_this,product_variants_combination = '',quantity = '') {
                variants_combination_array = []; product_ids = []; variant_ids = [];  total_cart_quantity = 0; var totalQuantity = 0;
                _this.closest("body").find(".shopping-bag-list").find(".shopping-cart-body .product-cart-list").each(function() {
                    variants = $(this).find(".select-variants").val();
                    if (variants == product_variants_combination) {
                        totalQuantity = parseInt(totalQuantity) + parseInt($(this).find(".quantity").val());
                        quantity = totalQuantity;
                    }
                    else {
                        quantity = parseInt($(this).find(".quantity").val());
                    }
                    product_id = $(this).find(".product-id").val();
                    total_cart_quantity = parseInt(total_cart_quantity) + quantity;
                    variant_array = {}; 
                    if(variants != undefined) {
                        variant_array[variants] = {};
                        variants_details = {};
                        variants_details.variants_combination_id = variants;
                        variants_details.quantity = quantity;
                        variants_details.product_id = product_id;
                        variant_array[variants] = variants_details;
                        if((variants_combination_array.length > 0) && (product_id in variants_combination_array)) {
                            variants_combination_array[product_id][variants] = variant_array[variants];
                            variant_ids.push(variants);
                        } else {
                            variants_combination_array[product_id] = variant_array;
                            product_ids.push(product_id);
                            variant_ids.push(variants);
                        }
                    } else {
                        variants_details = {};
                        variants_details.quantity = quantity;
                        variants_combination_array[product_id] = variants_details;
                        product_ids.push(product_id);
                    }
                });
                add_to_cart_url = $(".add-to-cart-url").val();
                $.ajax({
                    url: add_to_cart_url,
                    type: "POST",
                    data: {_token: CSRF_TOKEN,cart_data: variants_combination_array, product_id : product_ids, variant_ids : variant_ids, total_cart_quantity : total_cart_quantity,type : "add_to_cart_page"},
                    dataType: 'json',
                    success: function (response) {
                        /*toastr.options =
                        {
                            "closeButton" : true,
                            "progressBar" : true
                        }
                        toastr.success("Success! Your changes have been updated.");*/
                        console.log(response);                
                    },
                    error: function (data) {
                        console.log(data);
                    }
                });      
            }

            $(document).ready(function() {
                calculateAmount();
                hideShowBtn();
            });
            function calculateAmount() {
                total_price = 0; product_cart_price = 0; total_tax_price = 0; sub_total_amount = 0;
                $(".product-cart-list").filter(function() {
                    return !($(this).closest("#cart-variants-modal").length || $(this).closest(".out-of-stock-cart").length);
                }).each(function() {
                    variants = $(this).find(".select-variants").val();
                    if(variants == undefined || variants != "") {
                        price = $(this).find(".total-product-price").val();
                        tax_amount = $(this).closest("body").find(".global-tax-percentage").val();
                        quantity = $(this).find(".product-quantity").val();
                        tax_amount = (tax_amount != "") ? price * (tax_amount / 100) : 0;
                        sub_total = price - tax_amount;
                        sub_total_amount = (parseFloat(sub_total_amount) + parseFloat(sub_total));
                        total_tax_amount = parseFloat(tax_amount);
                        tax_amount = parseFloat(tax_amount);
                        total_amount = parseFloat(price);
                        $(this).find(".product-tax-amount").val(tax_amount.toFixed(2));
                        product_cart_price = (parseFloat(product_cart_price) + parseFloat(price));
                        total_price = total_price+parseFloat(price); 
                        total_tax_price = total_tax_price + parseFloat(total_tax_amount); 
                    }
                });
                $(".total-cart-amount").text("SAR "+total_price.toFixed(2));
                $(".total-tax-amount").text("SAR "+total_tax_price.toFixed(2));
                $(".sub-total-amount").text("SAR "+sub_total_amount.toFixed(2));
            }
            $('.minus').click(function () {
                var input_quantity = $(this).closest(".product-item").find(".quantity");
                quantity = parseFloat(input_quantity.val()) - 1;
                input_quantity.val((quantity > 0) ? quantity : 1);
                input_quantity.change();
                return false;
            });
            $('.plus').click(function () {
                var input_quantity = $(this).closest(".product-item").find(".quantity");
                input_quantity.val(parseFloat(input_quantity.val()) + 1);
                input_quantity.change();
                return false;
            });
            $(document).on("change keyup",".quantity",function(event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                validateQuantity($(this));
                _type = $(this).data("type");
                $(this).closest(".product-cart-list").find(".unavailable-error-message").html("");
                if(_type == "product-in-popup") {
                    product_id = $(this).closest(".single-product-details").find(".product-id").val();
                    variant_combinations_data = $(this).closest("body").find(".variant-combinations-"+product_id).val();
                    cartUpdateQuantity($(this),variant_combinations_data);
                } else {
                    variant_id = $(this).closest(".product-cart-list").find(".select-variants").val();
                    total_quantity = $(this).val();
                    $(".quantity").each(function() {
                        variants = $(this).closest(".product-cart-list").find(".select-variants").val();
                        if(variants == undefined || variants != "") {
                            quantity = $(this).val();
                            $(this).closest(".product-cart-list").find(".product-quantity").val(quantity);
                            item_price = $(this).closest(".product-item").find(".product-price").val();
                            total_price = parseFloat(quantity * item_price);
                            $(this).closest(".product-cart-list").find(".single-product-price").text("SAR "+parseFloat(item_price).toFixed(2));
                            $(this).closest(".product-cart-list").find(".total-product-price").val(total_price);
                        }
                    });
                    calculateAmount();
                    var totalQuantity = 0;
                    $(this).closest(".shopping-cart-body").find('.product-cart-list').each(function() {
                        var variant = $(this).find('.select-variants').val();
                        var quantity = parseInt($(this).find('.quantity').val());
                        if (variant == variant_id) 
                            totalQuantity += quantity;
                    });
                    if(totalQuantity > 0)
                        total_quantity = totalQuantity;
                    addProductToCart($(this),variant_id,total_quantity);
                    cart_product_count();
                }
            });
            $(document).on("click",".delete-product-item",function(event) {
                event.preventDefault();
                $(this).closest(".product-cart-list").prev().remove();
                $(this).closest(".product-cart-list").remove();
                calculateAmount();
                addProductToCart($(".delete-product-item"));
                cart_product_count();
                cart_list = $(".shopping-bag-list").find(".product-cart-list").length; 
                if(cart_list <= 0) {
                    $(".shopping-bag-list").find(".shopping-cart-body").html("<p class='empty-row text-center'>"+customerTranslations['empty_bag_desc']+"</p>");
                }
                hideShowBtn();
            });
            function hideShowBtn() {
                cart_list = $(".shopping-bag-list").find(".product-cart-list").filter(function() {
                    return !($(this).closest("#cart-variants-modal").length || $(this).closest(".out-of-stock-cart").length)
                }).length;
                if(cart_list <= 0)
                    $(".proceed-to-checkout-btn").addClass("dnone");
                else
                    $(".proceed-to-checkout-btn").removeClass("dnone");
            }
            $(document).on("click",".move-to-wishlist",function(event) {
                event.preventDefault();
                product_type = $(this).closest(".product-cart-list").find(".type-of-product").val();
                product_id = $(this).closest(".product-cart-list").find(".product-id").val();
                variants_combination_id = $(this).closest(".product-cart-list").find(".select-variants").val();
                addWishlist(product_type,product_id,"add",variants_combination_id,'','wishlist');
                $(this).closest(".product-cart-list").find('.delete-product-item').click(); 
            }); 
            $(document).on("click",".cart-variants",function() {
                element = $(this).closest(".product-cart-list").data("element");
                product_name = $(this).closest(".product-cart-list").find(".product-name").val();
                $("#cart-variants-modal").find(".modal-product-title").text(product_name);
                variants_title = $(this).closest(".product-cart-list").find(".variants-title").val();
                selected_combination = $(this).closest(".product-cart-list").find(".selected-combination-name").val();
                $("#cart-variants-modal").find(".modal-variant-title").html("<b> Select "+variants_title+"</b>");
                variants_combination = $(this).closest(".product-cart-list").find(".variants-combination-json").val();
                quantity =  $(this).closest(".product-cart-list").find(".quantity").val();
                variants_on_hand =  $(this).closest(".product-cart-list").find(".variants-quantity").val();
                product_id =  $(this).closest(".product-cart-list").find(".product-id").val();
                type_of_product =  $(this).closest(".product-cart-list").find(".type-of-product").val();
                variants_id =  $(this).closest(".product-cart-list").find(".select-variants").val();
                product_price =  $(this).closest(".product-cart-list").find(".product-price").val();
                $("#cart-variants-modal").find(".quantity").val(quantity);
                $("#cart-variants-modal").find(".variants-quantity").val(variants_on_hand);
                $("#cart-variants-modal").find(".product-id").val(product_id);
                $("#cart-variants-modal").find(".type-of-product").val(type_of_product);
                $("#cart-variants-modal").find(".single-product-variants-combination").val(variants_id);
                $("#cart-variants-modal").find(".product-price").val(product_price);
                $("#cart-variants-modal").find(".selected-element-class").val(element);
                variants_combination = $.parseJSON(variants_combination);
                variants_html = '';
                if(variants_combination.length > 0) {
                    variants_html += '<ul class="list-variants">';
                    $(variants_combination).each(function(key,val) {
                        checked = (selected_combination == val.variants_combination_name) ? "checked" : "";
                        variants_html += '<li class="product-variant-dev"><input type="radio" class="btn-check product-variant" data-page = "cart" data-type="product-in-popup" name="product_variant_combination_'+val.product_id+'" id="variant-combination-'+val.variants_combination_id+'" value="'+val.variants_combination_id+'" data-value="'+val.variants_combination_name+'" '+checked+'><label class="btn btn-outline-secondary avatar-xs-1 rounded-4 d-flex product-variant-label variant-combination-'+val.variants_combination_id+' '+val.product_available+'" for="variant-combination-'+val.variants_combination_id+'" data-variant-combination="'+val.variants_combination_name+'">'+val.variants_combination_name+'</label></li>';
                    });
                    variants_html += '</ul>';
                }
                $(this).closest("body").find("#cart-variants-modal").find(".product-variants-data").html(variants_html);
            });
            $(document).on("click",".cart-update",function(event) {
                event.preventDefault();
                event.stopImmediatePropagation();
                validateQuantity($(this).closest(".product-cart-list").find(".quantity"));
                variant_id = $(this).closest(".product-cart-list").find(".single-product-variants-combination").val();
                product_id = $(this).closest(".product-cart-list").find(".product-id").val();
                total_quantity = parseFloat($(this).closest(".product-cart-list").find(".quantity").val());
                product_price = parseFloat($(this).closest(".product-cart-list").find(".product-price").val());
                selected_element_class = $("#cart-variants-modal").find(".selected-element-class").val();
                variant_combination = $(this).closest(".single-product-details").find("#variant-combination-"+variant_id).closest(".product-variant-dev").find(".product-variant-label").text();
                product_unit = $(this).closest(".product-cart-list").find(".variants-quantity").val();
                if(($(this).closest("body").find(".product-cart-list-"+product_id+"-"+variant_id).length > 0) && (selected_element_class != "product-cart-list-"+product_id+"-"+variant_id)) {
                    _element = $(".product-cart-list-"+product_id+"-"+variant_id);
                    _element.find(".select-variants").val(variant_id);
                    _element.find(".selected-combination-name").val(variant_combination);
                    total_quantity = parseInt(total_quantity) + parseInt(_element.find(".quantity").val());
                    _element.find(".single-product-price").text("SAR "+(product_price).toFixed(2));
                    _element.find(".variants-quantity").val(product_unit);
                    if(product_unit != "" && product_unit != undefined && (parseInt(total_quantity) > parseInt(product_unit))) {
                        _element.find(".quantity").val(product_unit); 
                        _element.find(".product-quantity").val(product_unit);
                        _element.find(".total-product-price").val(parseFloat(product_unit) * parseFloat(product_price));
                        toastr.options =
                        {
                            "closeButton" : true,
                            "progressBar" : true
                        }
                        toastr.error(customerTranslations['quantity_exceeds_stock'].replace(":unit",product_unit));
                        // toastr.error("Quantity Limit Reached: Maximum "+product_unit+" allowed"); 
                    } else {
                        _element.find(".quantity").val(total_quantity); 
                        _element.find(".product-quantity").val(total_quantity); 
                        _element.find(".total-product-price").val(parseFloat(total_quantity) * parseFloat(product_price));
                        _element.find(".variants-quantity").val(product_unit);
                    }
                    $("."+selected_element_class).prev().remove();
                    $("."+selected_element_class).remove();
                } else {
                    _element = $("."+selected_element_class); 
                    _element.find(".select-variants").val(variant_id);
                    _element.find(".selected-combination-name").val(variant_combination);
                    _element.find(".single-product-price").text("SAR "+(product_price).toFixed(2));
                    _element.find(".quantity").val(total_quantity);
                    _element.find(".product-quantity").val(total_quantity);
                    _element.find(".total-product-price").val(parseFloat(total_quantity) * parseFloat(product_price));
                    variants_title = _element.find(".variants-title").val();
                    _element.find(".cart-variants").html(variants_title +" : "+variant_combination+'<i class="fa fa-chevron-down" aria-hidden="true"></i>');
                    _element.data("element","product-cart-list-"+product_id+"-"+variant_id);
                    _element.addClass("product-cart-list-"+product_id+"-"+variant_id).removeClass(selected_element_class);
                    _element.find(".variants-quantity").val(product_unit);
                }
                calculateAmount(); 
                addProductToCart($(this),variant_id,total_quantity);
                cart_product_count();
                $("#cart-variants-modal").modal('hide');
            });
        </script>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/customer/view_cart.blade.php ENDPATH**/ ?>