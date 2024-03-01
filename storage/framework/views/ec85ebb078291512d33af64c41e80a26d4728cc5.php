<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <?php echo $__env->make('common.customer.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
        <style>
            .slick-slide {
                width : 575px;
            }
            .slick-prev-1, .slick-next-1,.slick-prev-1:hover, .slick-prev-1:focus, .slick-next-1:hover, .slick-next-1:focus {
                border: none;
                background: none; 
                font-size: 24px; 
                color: #000000; 
                padding: 0; 
                margin: 0; 
                top: 2px;
                position:absolute;
            }
            .slick-next-1 {
                right: -14px;
            }
            .slick-prev-1 {
                left:-14px;
            }
            .single-product-img {
                width : 400px;
            }
            .truncate-text {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                max-width: 100%;
                display: inline-block;
            }
            .product-add-to-cart:disabled {
                background-color: #000000;
                border-color: #000000 !important;
                color: #ffffff;
                opacity: 1;
            }
        </style>
    </head>
    <body>
        <div class="body_overlay"></div>
        <?php echo $__env->make('common.customer.mobile_navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.mini_cart', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.breadcrumbs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <input type="hidden" class="translation-key" value="single_product_page_title">
        <input type="hidden" class="products_by_category_url" value="<?php echo e(route($store_url.'.customer.products-by-category')); ?>">
        <div class="single_product_section mb-80">
            <div class="container">
                <?php if(isset($product_details) && !empty($product_details)): ?>
                    <?php
                        $category_img_path = !empty($product_details) && !empty($product_details['category_image']) ? explode("***",$product_details['category_image']) : [];
                        $product_unit = $available_quantity = ($product_details['type_of_product'] == "variant" && !empty($product_variants_combinations) && isset($product_variants_combinations[$product_details['product_id']])) ? $product_variants_combinations[$product_details['product_id']][key($product_variants_combinations[$product_details['product_id']])]['on_hand'] : $product_details['unit'];
                        $variants_id = ($product_details['type_of_product'] == "variant" && !empty($product_variants_combinations) && isset($product_variants_combinations[$product_details['product_id']])) ? key($product_variants_combinations[$product_details['product_id']]) : 0;
                    ?>
                    <?php if(!empty($cart_data) && isset($cart_data[$product_details['product_id']])): ?> 
                        <?php if($product_details['type_of_product'] == "variant" && isset($cart_data[$product_details['product_id']][$variants_id])): ?> 
                            <?php 
                                $quantity = $cart_data[$product_details['product_id']][$variants_id]['quantity']; 
                                $variants_on_hand = $available_quantity = ($product_unit - $quantity); 
                            ?>
                            <?php if(!empty($product_unit) && is_numeric($product_unit) && $product_unit >= 0): ?> 
                                <?php $variants_on_hand = $available_quantity = ($product_unit - $quantity); ?>
                            <?php endif; ?>
                        <?php elseif($product_details['type_of_product'] == "single"): ?> 
                            <?php 
                                $quantity = $cart_data[$product_details['product_id']]['quantity']; 
                                $product_unit = $available_quantity = $product_details['unit'] - $quantity; 
                            ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="row single_product"  style="border:none;">
                        <div class="col-lg-6 col-md-6">
                            <div id="carouselExample" class="carousel carousel-dark slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <input type="hidden" class="category-img-count" value="<?php echo e(count($category_img_path)); ?>">
                                    <?php if(isset($category_img_path) && count($category_img_path) > 0): ?>
                                        <?php $__currentLoopData = $category_img_path; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $img_path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <?php $active_class = ($key == 0) ? "active" : "";  ?>
                                            <div class="carousel-item <?php echo e($active_class); ?> text-center">
                                                <img src="<?php echo e($img_path); ?>" class="single-product-img" alt="Product Image">
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev" fdprocessedid="lc2av8">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Previous</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next" fdprocessedid="zyuh9">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                    <span class="visually-hidden">Next</span>
                                </button>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6">
                            <div class="product_details_sidebar single-product-details single_product_details">
                                <input type="hidden" class="single-product-name" value="<?php echo e(!empty($product_details) && !empty($product_details['product_name']) ? $product_details['product_name'] : ''); ?>">
                                <h2 class="product__title"><?php echo e(!empty($product_details) && !empty($product_details['product_name']) ? $product_details['product_name'] : ''); ?></h2>
                                <?php if(!empty($product_details) && !empty($product_details['category_name'])): ?>
                                    <p class="category_title"><?php echo e($product_details['category_name']); ?></p>
                                <?php endif; ?>
                                <?php if(!empty($product_details) && !empty($product_details['sub_category_name'])): ?>
                                    <p class="sub_category_title"><?php echo e($product_details['sub_category_name']); ?></p>
                                <?php endif; ?>
                                <div class="price_box">
                                    <span class="current_price product-price modal-product-price">
                                        <?php if($product_details['type_of_product'] == "variant" && !empty($product_variants_combinations) && isset($product_variants_combinations[$product_details['product_id']])): ?>
                                            <?php $__currentLoopData = $product_variants_combinations[$product_details['product_id']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $product_data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($key == key($product_variants_combinations[$product_details['product_id']])): ?>
                                                    SAR <?php echo e($product_data['variant_price']); ?>

                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        <?php else: ?>
                                            <?php echo e(($product_details['type_of_product'] == "single") ? "SAR ".$product_details['price'] : ""); ?>

                                        <?php endif; ?>
                                    </span>
                                </div>
                                <?php $image_class = (!empty($category_img_path) && count($category_img_path)>11) ? "slick-carousel-1" : ""; ?>
                                <div class="d-flex flex-wrap gap-1 mb-4 <?php echo e($image_class); ?>">
                                    <?php if(!empty($category_img_path) && count($category_img_path)>0): ?>
                                        <?php $__currentLoopData = $category_img_path; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $img_path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <div>
                                                <button type="button" data-index="<?php echo e($key); ?>" class="btn p-1 d-flex border rounded-circle fs-5 text-primary all-product-images" >
                                                    <img src="<?php echo e($img_path); ?>" alt="" class="product-img rounded-circle avatar-xs">
                                                </button>
                                            </div>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php endif; ?>
                                </div>
                                <input type="hidden" class="product-id single-product-id single-page-product-id" value="<?php echo e($product_details['product_id']); ?>">
                                <input type="hidden" class="single-category-id" value="<?php echo e($product_details['category_id']); ?>">
                                <input type="hidden" class="single-sub-category-id" value="<?php echo e($product_details['sub_category_id']); ?>">
                                <input type="hidden" class="single-product-trackable" value="<?php echo e($product_details['trackable']); ?>">
                                <input type="hidden" class="variant-combinations variant-combinations-<?php echo e($product_details['product_id']); ?>" value="<?php echo e((!empty($product_variants_combinations) && array_key_exists($product_details['product_id'],$product_variants_combinations)) ? json_encode($product_variants_combinations[$product_details['product_id']]) : ''); ?>"> 
                                <input type="hidden" class="single-product-type" value="<?php echo e($product_details['type_of_product']); ?>">
                                <input type="hidden" class="modal-variant-on-hand" value="">
                                <input type="hidden" class="variant-on-hand" value="<?php echo e($product_unit); ?>">
                                <input type="hidden" class="product-unit" value="<?php echo e($product_details['unit']); ?>">
                                <input type="hidden" class="modal-product-unit" value="<?php echo e($product_unit); ?>">
                                <?php if( !empty($product_details) && !empty($product_details['type_of_product']) && $product_details['type_of_product'] == "variant"): ?>
                                    <?php if(!empty($variants_title)): ?>
                                        <p class=""><?php echo e($variants_title); ?></p>
                                    <?php endif; ?>
                                    <?php if(!empty($product_variants_combinations) && array_key_exists($product_details['product_id'],$product_variants_combinations)): ?>
                                        <div class="product__variant" data-option="size">
                                            <?php $variants_carousel_class = count($product_variants_combinations[$product_details['product_id']]) > 4 ? "slick-variants-carousel-1" : ""; ?>
                                            <ul class="list-variants <?php echo e($variants_carousel_class); ?>">
                                                <?php $__currentLoopData = $product_variants_combinations[$product_details['product_id']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $variants_combinations): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php 
                                                        $firstKey = key($product_variants_combinations[$product_details['product_id']]); 
                                                        $checked = ($firstKey == $key) ? "checked" : "";
                                                        $checked_style = ($firstKey == $key) ? "background-color: #000;color: #fff;" : "";
                                                    ?>
                                                    <li class="product-variant-dev">
                                                        <?php if($firstKey == $key): ?>
                                                            <input type="hidden" class="single-product-variants-combination" value="<?php echo e($variants_combinations['variants_combination_id']); ?>">
                                                        <?php endif; ?>
                                                        <input type="radio" class="btn-check product-variant" data-type="single-product" name="variants_combination_<?php echo e($variants_combinations['product_id']); ?>" id="product-variant-<?php echo e($variants_combinations['variants_combination_id']); ?>" value="<?php echo e($variants_combinations['variants_combination_id']); ?>" data-option="2" <?php echo e($checked); ?>>
                                                        <label class="product-variant-label <?php echo e($variants_combinations['product_available']); ?>" style="<?php echo e($checked_style); ?>" for="product-variant-<?php echo e($variants_combinations['variants_combination_id']); ?>" data-bs-toggle="tooltip" title="<?php echo e($variants_combinations['variants_combination_name']); ?>" data-variant-combination="<?php echo e($variants_combinations['variants_combination_name']); ?>"><?php echo e($variants_combinations['variants_combination_name']); ?></label>
                                                    </li>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                <div class="product_pro_button quantity1 d-flex mt-4 mb-4">
                                    <div class="pro-qty border product-item">
                                        <input type="text" class="quantity add-product-quantity" value="1">
                                    </div>
                                    <?php if((auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id)): ?>
                                        <?php $found = false; $wishlist_class = "far"; $wishlist_type = "add"; $title = __('customer.add_to_wishlist'); ?>
                                        <?php if(!empty($wishlistData) && isset($wishlistData[0]) && isset($wishlistData[0]['wishlist_id']) && $wishlistData[0]['wishlist_id'] > 0): ?>
                                            <?php $found = true; $wishlist_class = "fas"; $wishlist_type = "remove"; $title = __('customer.remove_from_wishlist'); ?>
                                        <?php endif; ?>
                                        <a class="add_to_cart product-wishlist" title="<?php echo e($title); ?>" style="font-size:20px;" href="#"><i data-wishlist-type="<?php echo e($wishlist_type); ?>" class="wishlist-icon fas fa-heart <?php echo e($wishlist_class); ?>"></i></a>
                                    <?php endif; ?>
                                    <?php if((($product_details['type_of_product'] == "single" && $product_details['trackable'] == 1) || ($product_details['type_of_product'] == "variant" && $available_quantity != "")) && ($available_quantity <= 0)): ?> 
                                        <a class="add_to_cart product-add-to-cart add-to-cart" href="#add-to-cart"> <?php echo e(__('customer.out_of_stock')); ?></a>    
                                    <?php else: ?>
                                        <!-- <a class="add_to_cart product-add-to-cart add-to-cart" href="#add-to-cart"><i class="fa fa-shopping-bag"></i> <?php echo e(__('customer.add_to_cart')); ?></a> -->
                                        <a class="add_to_cart product-add-to-cart add-to-cart" href="#add-to-cart"><?php echo e(__('customer.add_to_cart')); ?></a> 
                                    <?php endif; ?>
                                </div>
                                <?php if(!empty($product_details['product_description'])): ?>
                                    <div class="product_content">
                                        <div class="accordion" id="accordionExample">
                                            <div class="accordion-item">
                                                <p class="accordion-header">
                                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8"><?php echo e(__('customer.description')); ?></button>
                                                </p>
                                                <div id="collapse8" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
                                                    <div class="accordion-body px-2">
                                                        <p><?php echo html_entity_decode($product_details['product_description']); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="product_section mb-80">
            <div class="container">
                <div class="section_title text-center mb-55">
                    <h2><?php echo e(__('customer.related_products')); ?></h2>
                    <p><?php echo e(__('customer.related_products_desc')); ?></p>
                </div>
                <div class="row product_slick slick_navigation slick__activation related-products-data">
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="text-center">
                            <a href="<?php echo e(route($store_url.'.customer.category')); ?>"><button type="button" class="btn btn-link"><?php echo e(__('customer.see_all_products')); ?></button></a>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
        <?php echo $__env->make('common.customer.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.view_popup', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <script>
            $(document).ready(function() {
                var totalImages = $(".category-img-count").val();
                if (totalImages <= 1) 
                    $(".carousel-control-prev, .carousel-control-next").hide();
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
                $('.slick-carousel-1').slick({
                    infinite: true,
                    slidesToShow: 11,
                    slidesToScroll: 1,
                    prevArrow: '<button type="button" class="slick-prev-1">&#8249;</button>',
                    nextArrow: '<button type="button" class="slick-next-1">&#8250;</button>',
                    arrow: false,
                    autoplay: false,
                    adaptiveHeight: true,
                    speed: 300,
                    responsive: [
                        { breakpoint: 768, settings: { slidesToShow: 2 } },
                        { breakpoint: 500, settings: { slidesToShow: 2 } }
                    ]
                }); 
                $('.slick-variants-carousel-1').slick({
                    infinite: true,
                    slidesToShow: 7,
                    slidesToScroll: 1,
                    prevArrow: '<button type="button" class="slick-prev-1">&#8249;</button>',
                    nextArrow: '<button type="button" class="slick-next-1">&#8250;</button>',
                    arrow: false,
                    autoplay: false,
                    adaptiveHeight: true,
                    speed: 300,
                    responsive: [
                        { breakpoint: 768, settings: { slidesToShow: 2 } },
                        { breakpoint: 500, settings: { slidesToShow: 2 } }
                    ]
                }); 
                $(".single_product_details").find(".product-variant-label").each(function() {
                    var $label = $(this);
                    if ($label[0].scrollWidth > $label.innerWidth()) {
                        var text = $label.text();
                        while ($label[0].scrollWidth > $label.innerWidth() && text.length > 15) {
                            text = text.slice(0, -1);
                            $label.text(text + '...');
                        }
                    }
                });
                showProducts($(".single-category-id").val(),$(".related-products-data"),'',$(".single-sub-category-id").val());
            });
            var slickInstance = null;
            function initializeSlick() {
                // slickInstance = $('.related-products-data').slick({
                //     slidesToShow: 4,
                //     slidesToScroll: 1,
                //     arrows: true,
                //     dots: false,
                //     autoplay: false,
                //     speed: 300,
                //     infinite: true,
                //     // responsive: [
                //     //     { breakpoint: 768, settings: { slidesToShow: 2 } },
                //     //     { breakpoint: 500, settings: { slidesToShow: 2 } }
                //     // ]
                // });
                // slickInstance = $('.slick__activation').slick({
                //     prevArrow:
                //         '<button class="prev_arrow"><i class="ion-arrow-left-c"></i></button>',
                //     nextArrow:
                //         '<button class="next_arrow"><i class="ion-arrow-right-c"></i></button>',
                // });
            }
            var product_img_slick = product_img_slick1 = null;
            function productImgSlick() {
                product_img_slick = $('.slick-carousel').slick({
                    infinite: true,
                    slidesToShow: 7,
                    slidesToScroll: 1,
                    prevArrow: '<button type="button" class="slick-prev-1">&#8249;</button>',
                    nextArrow: '<button type="button" class="slick-next-1">&#8250;</button>',
                    arrow: false,
                    autoplay: false,
                    adaptiveHeight: true,
                    speed: 300,
                    responsive: [
                        { breakpoint: 768, settings: { slidesToShow: 2 } },
                        { breakpoint: 500, settings: { slidesToShow: 2 } }
                    ]
                });
                product_img_slick1 = $('.slick-variants-carousel').slick({
                    infinite: true,
                    slidesToShow: 3,
                    slidesToScroll: 1,
                    prevArrow: '<button type="button" class="slick-prev-1">&#8249;</button>',
                    nextArrow: '<button type="button" class="slick-next-1">&#8250;</button>',
                    arrow: false,
                    autoplay: false,
                    adaptiveHeight: true,
                    speed: 300,
                    responsive: [
                        { breakpoint: 768, settings: { slidesToShow: 2 } },
                        { breakpoint: 500, settings: { slidesToShow: 2 } }
                    ]
                });
            }
            function showProducts(category_id, _this, page = '', sub_category_id = '') {
                products_by_category_url = $(".products_by_category_url").val();
                // $(".page-loader").show();
                $.ajax({
                    url: products_by_category_url,
                    type: 'post',
                    data: {
                        _token: CSRF_TOKEN,
                        category_id: category_id,
                        // sub_category_id: sub_category_id,
                        search_text: "",
                        page: page,
                        _type : 'related_products',
                        perPage : 3,
                        product_id : $(".single-page-product-id").val()
                    },
                    success: function (response) {
                        // if (slickInstance) {
                        //     slickInstance.slick('unslick'); 
                        // } 
                        if (product_img_slick) {
                            product_img_slick.slick('unslick');
                        }
                        if (product_img_slick1) {
                            product_img_slick1.slick('unslick');
                        }
                        $(".related-products-data").html(response.product_list_by_category);
                        if(response.product_list_by_category != "") {
                            productImgSlick();
                            _this.closest("body").find(".related-products-data").find(".product-variant-label").each(function() {
                                var $label = $(this);
                                if ($label[0].scrollWidth > $label.innerWidth()) {
                                    var text = $label.text();
                                    while ($label[0].scrollWidth > $label.innerWidth() && text.length > 5) {
                                        text = text.slice(0, -1);
                                        $label.text(text + '...');
                                    }
                                }
                            });
                        }
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                            return new bootstrap.Tooltip(tooltipTriggerEl)
                        });
                        $(".page-loader").hide();
                    }
                });
            }
            $(document).on("click",".all-product-images",function() {
                var index = $(this).data("index");
                $("#carouselExample .carousel-item").removeClass("active");
                $("#carouselExample .carousel-item:eq(" + index + ")").addClass("active");
            });
        </script> 
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/customer/single_product.blade.php ENDPATH**/ ?>