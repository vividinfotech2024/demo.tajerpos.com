$(document).ready(function() {
    cart_product_count();
    get_store_details();
    if($(".page-name").val() != "home_page")
        $(".page-loader").hide();
});
var outOfStockTitle = $('<div/>').text(customerTranslations['out_of_stock']).html();
var addToCart = $('<div/>').text(customerTranslations['add_to_cart']).html();

function get_store_details() {
    store_details_url = $(".store-details-url").val();
    $.ajax({
        url: store_details_url,
        type: 'get',
        success: function(response){
            if (response.hasOwnProperty('store_details') && Array.isArray(response.store_details)) {
                var store_details = response.store_details;
                if (store_details.length > 0) {
                    $(".customer-store-phone-number").text(store_details[0].store_phone_number);
                    $(".customer-store-email-id").text(store_details[0].email);
                    $(".customer-store-phone-number").attr("href","tel:" + store_details[0].store_phone_number);
                    $(".customer-store-email-id").attr("href","mailto:" + store_details[0].email);
                    $(".logo-in-customer").attr("src",store_details[0].store_logo);
                    var translationKey = $(".translation-key").val();
                    if(translationKey == "single_product_page_title") {
                        single_product_name = $(".single-product-name").val();
                        var title_tag_content = customerTranslations[translationKey].replace(':company', store_details[0].store_name).replace(':product_name', single_product_name);
                    } else
                        var title_tag_content = customerTranslations[translationKey].replace(':company', store_details[0].store_name);
                    $(".title-content").text(title_tag_content); 
                    var currentYear = new Date().getFullYear();
                    $(".copyright-content").text(customerTranslations['copyrights'].replace(':company', store_details[0].store_name).replace(':year', currentYear));
                    $(".company-name").text(store_details[0].store_name);
                    var address = '';
                    if (store_details[0].store_address)
                        address += store_details[0].store_address + ',';
                    if (store_details[0].city_name)
                        address += store_details[0].city_name + ',';
                    if (store_details[0].state_name)
                        address += store_details[0].state_name + ',';
                    if (store_details[0].country_name)
                        address += store_details[0].country_name;
                    $(".customer-store-address").text(address);
                }
            }
        }
    });
}

function cart_product_count() {
    get_product_count_url = $(".get-product-count-url").val();
    $.ajax({
        url: get_product_count_url,
        type: 'get',
        success: function(response){
            cart_total_quantity = response.cart_total_quantity;
            $(".shopping_cart").find(".shopping-cart-count").removeClass("item_count");
            $(".shopping_cart").find(".shopping-cart-count").text("");
            $(".shopping-bag-details").text(customerTranslations['my_bag']);
            if(cart_total_quantity != null && cart_total_quantity > 0) {
                $(".shopping_cart").find(".shopping-cart-count").addClass("item_count");
                $(".shopping_cart").find(".item_count").text(cart_total_quantity); 
                $(".shopping-bag-details").text(customerTranslations['my_bag']+" ("+cart_total_quantity+" "+customerTranslations['items']+")");
            } else {
                $(".shopping_cart").find(".item_count").text("");
                $(".shopping-bag-details").text(customerTranslations['my_bag']);
            }
        }
    });
}

function showWishlist(_this,product_type,product_id,variants_id,_type = '') {
    wishlist_url = $(".wishlist-url").val();
    $.ajax({
        url: wishlist_url,
        type: 'post',
        data: {_token: CSRF_TOKEN,product_type: product_type,product_id: product_id, variants_id : variants_id},
        success: function(response){
            if(response.wishlist > 0) {
                if(_type == "products-in-home" || _type == "home") 
                    _this.find(".product-wishlist").html('<i data-wishlist-type="remove" data-type="'+_type+'" class="wishlist-icon fas fa-heart"></i>');
                else {
                    if(_type == "view_product" || _type == "product-in-popup")
                        selector = _this.closest("body").find(".product-modal-popup").find(".product-wishlist");
                    else if(_type == "single-product")
                        selector = _this.find(".product-wishlist");
                    else
                        selector = _this.closest("body").find(".product-wishlist");
                    selector.html('<i data-wishlist-type="remove" class="wishlist-icon fas fa-heart"></i> ');
                }
            }   
            else {
                if(_type == "home" || _type == "products-in-home") {
                    _this.find(".product-wishlist").html('<i data-wishlist-type="add" data-type="'+_type+'" class="wishlist-icon far fa-heart"></i>');
                }
                else {
                    if(_type == "view_product" || _type == "product-in-popup")
                        selector = _this.closest("body").find(".product-modal-popup").find(".product-wishlist");
                    else if(_type == "single-product")
                        selector = _this.find(".product-wishlist");
                    else
                        selector = _this.closest("body").find(".product-wishlist");
                    selector.html('<i data-wishlist-type="add" class="wishlist-icon far fa-heart"></i> ');
                }
            }
        }
    });
}

function addWishlist(product_type,product_id,_type,variants_combination_id = '',wishlist_id = '',page) {
    add_wishlist_url = $(".add-wishlist-url").val();
    $.ajax({
        url: add_wishlist_url,
        type: 'post',
        data: {_token: CSRF_TOKEN,product_type: product_type,product_id: product_id,_type : _type, variants_id : variants_combination_id, wishlist_id : wishlist_id},
        success: function(response){
            if(page != "wishlist") {
                toastr.options =
                {
                    "closeButton" : true,
                    "progressBar" : true
                }
                if(response.type == "success") 
                    toastr.success(response.message);
                else
                    toastr.error(response.message);
                if(page == "wishlist_popup") {
                    showWishlistProducts($(".wishlist-details"));
                }
            }
        }
    });
}

$(document).on("click",".product-wishlist",function(e) {
    e.stopImmediatePropagation();
    var _type = $(this).find(".wishlist-icon").attr('data-wishlist-type');
    $(this).closest(".single-product-details").find(".error-variant-title").css("color","#212529"); 
    var wishlist_type = $(this).find(".wishlist-icon").attr('data-type');
    product_type = $(this).closest(".single-product-details").find(".single-product-type").val();
    product_id = $(this).closest(".single-product-details").find(".single-product-id").val();
    _page = $(this).attr("data-page");
    /*variants_combination_id = $(this).closest(".single-product-details").find(".single-product-variants-combination").val();
    if(product_type == "variant" && variants_combination_id == "" && wishlist_type == "home") {
        $(this).closest(".single-product-details").find(".error-variant-title").css("color","red");
        toastr.error("Please pick a variant");
        return false;  
    }*/
    if(_type == "add") {
        if(wishlist_type == "home")
            $(this).html('<i data-wishlist-type="remove" data-type="'+wishlist_type+'" class="wishlist-icon fas fa-heart"></i>');
        else
            $(this).html('<i data-wishlist-type="remove" class="wishlist-icon fas fa-heart"></i> ');
        $(this).closest("body").find(".single-product-details-"+product_id).find(".product-wishlist").html('<i data-wishlist-type="remove" data-type="'+wishlist_type+'" class="wishlist-icon fas fa-heart"></i>');
    }
    else {
        if(wishlist_type == "home")
            $(this).html('<i data-wishlist-type="add" data-type="'+wishlist_type+'" class="wishlist-icon far fa-heart"></i>');
        else 
            $(this).html('<i data-wishlist-type="add" class="wishlist-icon far fa-heart"></i> ');
        $(this).closest("body").find(".single-product-details-"+product_id).find(".product-wishlist").html('<i data-wishlist-type="add" data-type="'+wishlist_type+'" class="wishlist-icon far fa-heart"></i>');
    }
    // addWishlist(product_type,product_id,_type,variants_combination_id);
    addWishlist(product_type,product_id,_type,'','',_page);
});

$(document).on("click",".product-view",function() {
    product_type = $(this).attr("data-product-type"); 
    product_name = $(this).closest(".single_product").find(".product-name").text();
    product_price = $(this).closest(".single_product").find(".product-price").text(); 
    product_id = $(this).closest(".single_product").find(".product-id").val();
    product_trackable = $(this).closest(".single_product").find(".product-trackable").val();
    modal_product_unit = $(this).closest(".single_product").find(".product-unit").val();
    product_category_images = $(this).closest(".single_product").find(".product-category-images").val();
    product_url = $(this).closest(".single_product").find(".single-product-url").attr("href");
    variants_id = $(this).closest(".single_product").find('.single-product-variants-combination').val(); 
    product_description =  $(this).closest(".single_product").find(".single-product-description").val();
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-name").text(product_name);
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-price").text(product_price);
    $(this).closest("body").find(".product-modal-popup").find(".single-product-id").val(product_id);
    $(this).closest("body").find(".product-modal-popup").find(".single-product-type").val(product_type);
    $(this).closest("body").find(".product-modal-popup").find(".single-product-trackable").val(product_trackable);
    $(this).closest("body").find(".product-modal-popup").find(".product-unit").val(modal_product_unit);
    // $(this).closest("body").find(".product-modal-popup").find(".modal-product-unit").val();
    $(this).closest("body").find(".product-modal-popup").find(".add-product-quantity").val(1);
    // $(this).closest("body").find(".product-modal-popup").find(".single-product-variants-combination").val("");
    $(this).closest("body").find(".product-modal-popup").find(".single-product-variants-combination").val(variants_id);
    product_category_images =  (product_category_images != "") ? product_category_images.split('***') : [];
    modal_product_images = "";
    if(product_category_images.length > 0) {
        modal_product_images = "<div class='tab-content product-details-large'>";
        $(product_category_images).each(function(key,val) {
            active_class = (key == 0) ? "show active" : "";
            modal_product_images += '<div class="tab-pane fade '+active_class+'" id="tab'+key+'" role="tabpanel"><div class="modal_tab_img"><a href="'+product_url+'"><img src="'+val+'" alt=""></a></div></div>';
        });
        modal_product_images += "</div>";

        modal_product_images += '<div class="modal_tab_button"><ul class="nav product_navactive owl-carousel" role="tablist">';
        $(product_category_images).each(function(key,val) {
            active_class = (key == 0) ? " active" : "";
            modal_product_images += '<li><a class="nav-link '+active_class+'" data-toggle="tab" href="#tab'+key+'" role="tab" aria-controls="tab'+key+'" aria-selected="false"><img src="'+val+'" alt=""></a></li>';
        });
        modal_product_images += "</ul></div>";
    }
    $(this).closest("body").find(".product-modal-popup").find(".product-images-modal-tab").html(modal_product_images);
    // Destroy the existing Owl Carousel instance
    $('.product_navactive').trigger('destroy.owl.carousel');
    $productCarousel = $('.product_navactive');
    $productCarousel.owlCarousel({
        loop: false,
        nav: true,
        autoplay: false,
        autoplayTimeout: 8000,
        items: 4,
        dots: false,
        mouseDrag: false,
        navText: [
            '<i class="ion-chevron-left"></i>',
            '<i class="ion-chevron-right"></i>',
        ],
        responsiveClass: true,
        responsive: {
            0: {
                items: 1,
            },
            250: {
                items: 2,
            },
            480: {
                items: 3,
            },
            768: {
                items: 4,
            },
        },
    });
    var itemCount = $productCarousel.find('.owl-item').length;
    if (itemCount <= 4) {
        $productCarousel.find('.owl-nav').hide();
    }
    $productCarousel.on('changed.owl.carousel', function(event) {
        var currentItemCount = event.item.count;
        // Hide or show the navigation arrows based on the current item count
        if (currentItemCount <= 4) {
            $productCarousel.find('.owl-nav').hide();
        } else {
            $productCarousel.find('.owl-nav').show();
        }
    });
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-url").attr("href",product_url);    
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-description").html(product_description);
    $(this).closest("body").find(".product-variants-data").html("");
    if(product_type == "variant") {                  
        variant_combinations_data = $(this).closest(".single_product").find(".variant-combinations").val();
        variant_combination_element = "";
        if(variant_combinations_data != "") {
            variant_combinations = $.parseJSON(variant_combinations_data);
            variant_combination_element = '<ul class="list-variants">';
            if(variants_id != "")
                firstKey = variants_id;
            else {
                var keys = Object.keys(variant_combinations);
                var firstKey = keys[0];
            }
            $.each(variant_combinations, function(key, value) {
                checked = (variants_id == value.variants_combination_id) ? "checked" : ""; 
                variant_combination_element += '<li class="product-variant-dev"><input type="radio" class="btn-check product-variant" data-type="product-in-popup" name="product_variant_combination_'+value.product_id+'" id="product-variant-'+value.variants_combination_id+'" value="'+value.variants_combination_id+'"  data-value="'+value.variants_combination_name+'" '+checked+'><label class="btn btn-outline-secondary avatar-xs-1 rounded-4 d-flex product-variant-label product-variant-'+value.variants_combination_id+'" for="product-variant-'+value.variants_combination_id+'">'+value.variants_combination_name+'</label></li>';
            });
            variant_combination_element += '</ul>';
        }
        $(this).closest("body").find(".product-variants-data").html(variant_combination_element);
        variantCombination($(this),variant_combinations_data,'view_product');
    }
    if(product_type == "single") {
        updateQuantity(product_type,product_id,'',$(this).closest("body").find(".product-modal-popup"),'','view_product');
    }
    is_authenticated = $(this).closest("body").find(".is_authenticated").val();
    if(is_authenticated == 1)
        showWishlist($(this),product_type,product_id,$(this).closest("body").find(".product-modal-popup").find(".single-product-variants-combination").val(),'view_product');
    $('#modal_box').modal('show');
});

$(document).on("change keyup",".add-product-quantity",function() {
    product_type = $(this).closest(".single-product-details").find(".single-product-type").val();
    product_id = $(this).closest(".single-product-details").find(".single-product-id").val();
    _type = $(this).closest(".single-product-details").find(".product-variant:checked").data("type");
    if(product_type == "variant") {
        variant_combinations_data = $(this).closest("body").find(".variant-combinations-"+product_id).val();
        variantCombination($(this),variant_combinations_data,_type);
    } else {
        product_unit = $(".variant-combinations-"+product_id).closest(".single_product").find(".product-unit").val();
        updateQuantity(product_type,product_id,'',$(this).closest(".single_product"),parseFloat(product_unit),_type);
    }
});

function updateQuantity(product_type,product_id,variant_id = '',_this,product_unit = '',_type = '',is_add_to_cart = '') {
    get_product_quantity_url = $(".get-product-quantity-url").val();
    product_quantity = parseFloat(_this.find(".add-product-quantity").val());
    product_trackable = _this.find(".single-product-trackable").val();
    on_hand_product = ""; available_quantity = 0;
    $.ajax({
        url: get_product_quantity_url,
        type: 'post',
        async : false,
        data: {_token: CSRF_TOKEN,product_type: product_type,product_id: product_id,variant_id: variant_id},
        success: function(response){
            quantity = parseFloat(response.quantity);
            selector = _this;
            if(product_type == "single") {
                // if(product_unit == '' || product_unit == undefined) {
                    // product_unit = parseFloat(selector.find(".modal-product-unit").val());
                    product_unit = parseFloat(selector.find(".product-unit").val());
                // }
                available_quantity = product_unit - quantity;
                selector.find(".modal-product-unit").val(available_quantity);
            } else {
                // on_hand_product = parseFloat(selector.find(".modal-variant-on-hand").val());
                on_hand_product = parseFloat(selector.find(".variant-on-hand").val());
                if(on_hand_product != "" || on_hand_product == 0) {
                    available_quantity = on_hand_product - quantity;
                    selector.find(".modal-variant-on-hand").val(available_quantity);
                }
            }
            if(((product_type == "single" && product_trackable == 1) || (product_type == "variant" && (on_hand_product != "" || on_hand_product == 0))) && (product_quantity > available_quantity)) {
                if(_type == "wishlist") {
                    selector.find(".product-add-to-cart").html('<a href="#" title="'+outOfStockTitle+'"><span class="pe-7s-close-circle"></span></a>');
                    selector.find(".product-add-to-cart").prop("disabled",true); 
                } else {
                    if(product_type == "variant") {
                        if(_type == "product-in-popup") {
                            selector.find(".variant-combination-"+variant_id).addClass("out-of-stock");
                        }
                        else
                            selector.find("#product-variant-"+variant_id).closest(".product-variant-dev").find(".product-variant-label").addClass("out-of-stock");
                    }
                    error_message = "";
                    if(is_add_to_cart == "add_to_cart" || available_quantity == 0) {
                        selector.find(".add-product-quantity").val(1);
                        selector.find(".product-add-to-cart").text(outOfStockTitle);
                        selector.find(".product-add-to-cart").prop("disabled",true);
                        if(available_quantity == 0 && is_add_to_cart != "add_to_cart")
                            error_message = customerTranslations['product_not_available'];
                    } else {
                        selector.find(".add-product-quantity").val(available_quantity); 
                        error_message = customerTranslations['quantity_exceeds_stock'].replace(":unit",available_quantity);
                    }
                    if(error_message != "") {
                        toastr.options =
                        {
                            "closeButton" : true,
                            "progressBar" : true
                        }
                        toastr.error(error_message);
                    }
                }
            } else {
                if(_type == "wishlist") {
                    selector.find(".product-add-to-cart").html('<a href="#add-to-cart" title="'+addToCart+'"><span class="pe-7s-shopbag"></span></a>');
                    selector.find(".product-add-to-cart").prop("disabled",false);
                } else {
                    selector.find(".product-add-to-cart").text(addToCart);
                    selector.find(".product-add-to-cart").prop("disabled",false);
                }
            }
        }
    });
}


$(document).on("click",".add-to-cart",function(e) {
    e.stopImmediatePropagation();
    _this = $(this);
    _this.closest(".single-product-details").find(".error-variant-title").css("color","#212529"); 
    _type = _this.data("type");
    if(_type == "wishlist") 
        $(this).html('<a href="#add-to-cart" title="'+addToCart+'"><span class="pe-7s-shopbag"></span></a>');
    else
        $(this).text(addToCart);
    product_type = $(this).closest(".single-product-details").find(".single-product-type").val();
    productId = $(this).closest(".single-product-details").find(".single-product-id").val();
    quantity = $(this).closest(".single-product-details").find(".quantity").val();
    product_variants_combination = $(this).closest(".single-product-details").find(".single-product-variants-combination").val();
    if(_type == "products-in-home" && product_type == "variant" && product_variants_combination == "") {
        $(this).closest(".single-product-details").find(".error-variant-title").css("color","red"); 
        toastr.error(customerTranslations['pick_variant']);
        return false;
    } else {
        add_to_cart_url = $(".add-to-cart-url").val();
        on_hand_product = (product_type == "variant") ? $(this).closest(".single-product-details").find(".modal-variant-on-hand").val() : $(this).closest(".single-product-details").find(".modal-product-unit").val();                
        product_trackable = $(this).closest(".single-product-details").find(".single-product-trackable").val();
        if(((product_type == "single" && product_trackable == 1) || (product_type == "variant" && (on_hand_product != "" || on_hand_product == 0))) && (parseInt(quantity) > parseInt(on_hand_product))) {
            if(_type == "wishlist") 
                $(this).html('<a href="#" title="'+outOfStockTitle+'"><span class="pe-7s-close-circle"></span></a>');
            else {
                $(this).text(outOfStockTitle); 
            }
            toastr.options =
            {
                "closeButton" : true,
                "progressBar" : true
            }
            toastr.error(customerTranslations['product_not_available']);
            return false;
        } else {
            $.ajax({
                url: add_to_cart_url,
                type: 'POST',
                data: {_token: CSRF_TOKEN,product_id: productId,quantity: quantity,product_variants_combination:product_variants_combination},
                success: function (response) {
                    if(response.success != undefined) {
                        toastr.options =
                        {
                            "closeButton" : true,
                            "progressBar" : true
                        }
                        toastr.success(customerTranslations['product_added_to_cart_success']);
                        $(this).closest(".single-product-details").find(".quantity").val(1);
                        cart_product_count();
                        updateQuantity(product_type,productId,product_variants_combination,_this.closest(".single-product-details"),'',_type,'add_to_cart');
                        _page = _this.closest("body").find(".page-name").val();
                        if(_type == "product-in-popup" && (_page == "home_page" || _page == "category_product")) {
                            category_id = _this.closest("body").find(".pagination").find("li.current").attr("data-category-id");
                            pagination_page_no = _this.closest("body").find(".pagination").find("li.current").attr("data-page-no");
                            showProducts(category_id,$(".product-list-by-category"),pagination_page_no);
                        }
                    }
                    if(response.error != undefined) {
                        toastr.options =
                        {
                            "closeButton" : true,
                            "progressBar" : true
                        }
                        toastr.error(customerTranslations['product_not_found_error']);
                    }
                },
                error: function (xhr, status, error) {
                    // Handle the error, such as displaying an error message
                    console.log('Error adding product to cart:', error);
                }
            });       
        }
    }
});

$(document).on("click",".product-images-btn",function(event) {
    event.preventDefault();
    product_img_path = $(this).find(".product-img").attr("src");
    $(this).closest(".single_product").find(".product-image-path").attr("src",product_img_path);
});  

$(document).on("click",".product-variant",function(event) {
    event.preventDefault();
    _type = $(this).attr("data-type");
    _page = $(this).data("page");
    _this = $(this);
    if(_type == "product-in-popup" || _type == "single-product") {
        $(this).closest(".single-product-details").find(".product-variant-label").css({"background-color":"#fff","color":"#000","border-color":"#000"});
        $(this).closest(".product-variant-dev").find(".product-variant-label").css({"background-color":"#000","color":"#fff","border-color":"#000"});
    } else {
        $(this).closest(".single-product-details").find(".product-variant-label").css({"background-color":"#ffffff","color":"#6c757d","border-color":"#6c757d"});
        $(this).closest(".product-variant-dev").find(".product-variant-label").css({"background-color":"#6c757d","color":"#fff","border-color":"#6c757d"});
    }
    $(this).closest(".single-product-details").find(".error-variant-title").css("color","#212529"); 
    product_id = $(this).closest(".single-product-details").find(".product-id").val();
    variant_combinations_data = $(this).closest("body").find(".variant-combinations-"+product_id).val();
    product_type = $(this).closest(".single-product-details").find(".single-product-type").val();
    variants_id = $(this).closest(".single-product-details").find(".product-variant:checked").val();
    $(this).closest(".single-product-details").find(".single-product-variants-combination").val(variants_id);
    if(_page == "cart") {
        cartUpdateQuantity(_this,variant_combinations_data);
    } else {
        $(".page-loader").show();
        variantCombination($(this),variant_combinations_data,_type);
        is_authenticated = $(this).closest("body").find(".is_authenticated").val();
        if(is_authenticated == 1)
            showWishlist($(this).closest(".single-product-details"),product_type,product_id,$(this).closest(".single-product-details").find(".single-product-variants-combination").val(),_type);
        $(".page-loader").hide();
    }
});

function cartUpdateQuantity(_this,variant_combinations_data) {
    quantity = _this.closest(".single-product-details").find(".quantity").val();
    variant_combinations_data = $.parseJSON(variant_combinations_data);
    variants_id = _this.closest(".single-product-details").find(".single-product-variants-combination").val();
    variant_combination = _this.closest(".single-product-details").find("#variant-combination-"+variants_id).closest(".product-variant-dev").find(".product-variant-label").text();
    $(variant_combinations_data).each(function(key,val) {
        if(val['variants_combination_name'] == variant_combination) {
            _this.closest(".single-product-details").find(".product-price").val(val['variant_price']);
            _this.closest(".single-product-details").find(".variants-quantity").val(val['on_hand']);
            if(val['on_hand'] != "" && $.isNumeric(val['on_hand']) && (parseInt(quantity) > parseInt(val['on_hand']))) {
                _this.closest(".single-product-details").find(".cart-update").text(outOfStockTitle);
                _this.closest(".single-product-details").find(".cart-update").prop("disabled",true);
            } else {
                _this.closest(".single-product-details").find(".cart-update").text("Update");
                _this.closest(".single-product-details").find(".cart-update").prop("disabled",false);
            }
        }
    });
}

function variantCombination(_this,variant_combinations_data,type='') {
    product_quantity = _this.closest(".single_product").find(".add-product-quantity").val();
    product_id = _this.closest(".single_product").find(".single-product-id").val();
    product_type = _this.closest(".single_product").find(".single-product-type").val();
    variants_id = _this.closest(".single_product").find(".single-product-variants-combination").val();
    if(type == "product-in-popup") {
        // variant_combination = _this.closest(".single_product").find("#variant-combination-"+variants_id).closest(".product-variant-dev").find(".product-variant-label").text();
        variant_combination = _this.closest(".single_product").find("#variant-combination-"+variants_id).closest(".product-variant-dev").find(".product-variant-label").data("variant-combination");
    }
    else {
        variants_id = _this.closest(".single_product").find(".single-product-variants-combination").val();
        // variant_combination = _this.closest(".single_product").find("#product-variant-"+variants_id).closest(".product-variant-dev").find(".product-variant-label").text();
        variant_combination = _this.closest(".single_product").find("#product-variant-"+variants_id).closest(".product-variant-dev").find(".product-variant-label").data("variant-combination");
    }
    if(type == "products-in-home" || type == "single-product")
        selector = _this.closest(".single_product");
    else
        selector = _this.closest("body").find(".product-modal-popup");  
    if(variant_combinations_data != "") {
        variant_combinations_data = $.parseJSON(variant_combinations_data);
        if(variant_combination in variant_combinations_data){
            product_price = variant_combinations_data[variant_combination]['variant_price'];
            product_price = (product_price > 0) ? parseFloat(product_price).toFixed(2) : product_price;
            on_hand_product = variant_combinations_data[variant_combination]['on_hand'];
            selector.find(".modal-product-price").text("SAR "+product_price);
            selector.find(".single-product-variants-combination").val(variant_combinations_data[variant_combination]['variants_combination_id']);
            selector.find(".modal-variant-on-hand").val(on_hand_product);
            selector.find(".variant-on-hand").val(on_hand_product);
            updateQuantity(product_type,product_id,variant_combinations_data[variant_combination]['variants_combination_id'],selector,'',type);
        } else {
            // if(type == "products-in-home") {
            //     selector.find(".product-add-to-cart").html('<a href="#" title="'+outOfStockTitle+'"><span class="pe-7s-close-circle"></span></a>');
            // } else {
            //     selector.find(".product-add-to-cart").text(outOfStockTitle);
            // }
            selector.find(".product-add-to-cart").text(outOfStockTitle);
            selector.find(".product-add-to-cart").prop("disabled",true);
        }
    }
}

$(document).on("click",".product-quick-view",function() {
    _this = $(this);
    $(".page-loader").show();
    page_type = $(this).attr("data-page-type");
    product_id = $(this).closest(".single_product").find(".single-product-id").val();
    product_type = $(this).closest(".single_product").find(".single-product-type").val();
    product_name = $(this).closest(".single_product").find(".product-name").text();
    product_price = $(this).closest(".single_product").find(".product-price").text(); 
    product_category_images = $(this).closest(".single_product").find(".product-category-images").val();
    product_category_images =  (product_category_images != "") ? product_category_images.split('***') : [];
    product_url = $(this).closest(".single_product").find(".single-product-url").attr("href");
    product_description =  $(this).closest(".single_product").find(".single-product-description").val();
    product_unit = $(this).closest(".single_product").find(".product-unit").val();
    modal_product_unit = $(this).closest(".single_product").find(".modal-product-unit").val();
    product_trackable = $(this).closest(".single_product").find(".single-product-trackable").val();
    category_name = $(this).closest(".single_product").find(".product-category-name").val();                
    sub_category_name = $(this).closest(".single_product").find(".product-subcategory-name").val();
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-name").text(product_name);
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-price").text(product_price);
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-url").attr("href",product_url);    
    $(this).closest("body").find(".product-modal-popup").find(".single-product-id").val(product_id);
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-unit").val(modal_product_unit);
    $(this).closest("body").find(".product-modal-popup").find(".product-unit").val(product_unit);
    // $(this).closest("body").find(".product-modal-popup").find(".modal-product-unit").val();
    $(this).closest("body").find(".product-modal-popup").find(".modal-product-description").html(product_description);
    $(this).closest("body").find(".product-modal-popup").find(".single-product-trackable").val(product_trackable);
    $(this).closest("body").find(".product-modal-popup").find(".single-product-type").val(product_type);
    $(this).closest("body").find(".product-modal-popup").find(".category-name").text(category_name);
    $(this).closest("body").find(".product-modal-popup").find(".sub-category-name").text(sub_category_name);
    $(this).closest("body").find(".product-modal-popup").find(".add-product-quantity").val(1);
    checked_variants_id = (page_type != "wishlist") ? $(this).closest(".single_product").find('.single-product-variants-combination').val() : "";
    modal_product_images = "";
    if(product_category_images.length > 0) {
        modal_product_images = "<div class='tab-content product-details-large'>";
        $(product_category_images).each(function(key,val) {
            active_class = (key == 0) ? "show active" : "";
            modal_product_images += '<div class="tab-pane fade '+active_class+'" id="tab'+key+'" role="tabpanel"><div class="modal_tab_img"><a href="'+product_url+'"><img src="'+val+'" alt=""></a></div></div>';
        });
        modal_product_images += "</div>";

        modal_product_images += '<div class="modal_tab_button"><ul class="nav product_navactive owl-carousel" role="tablist">';
        $(product_category_images).each(function(key,val) {
            active_class = (key == 0) ? " active" : "";
            modal_product_images += '<li><a class="nav-link '+active_class+'" data-toggle="tab" href="#tab'+key+'" role="tab" aria-controls="tab'+key+'" aria-selected="false"><img src="'+val+'" alt=""></a></li>';
        });
        modal_product_images += "</ul></div>";
    }
    $(this).closest("body").find(".product-modal-popup").find(".product-images-modal-tab").html(modal_product_images);
    $(this).closest("body").find(".product-modal-popup").find(".product-wishlist").html('<i data-wishlist-type="remove" class="wishlist-icon fas fa-heart"></i> ');
    $(this).closest("body").find(".product-modal-popup").find(".product-wishlist").attr("data-page","wishlist_popup");
    $(this).closest("body").find(".product-variants-data").html("");
    _this.closest("body").find(".product-modal-popup").find(".variants-title").text("");
    $('.product_navactive').trigger('destroy.owl.carousel');
    $productCarousel = $('.product_navactive');
    $productCarousel.owlCarousel({
        loop: false,
        nav: true,
        autoplay: false,
        autoplayTimeout: 8000,
        items: 4,
        dots: false,
        mouseDrag: false,
        navText: [
            '<i class="ion-chevron-left"></i>',
            '<i class="ion-chevron-right"></i>',
        ],
        responsiveClass: true,
        responsive: {
            0: {
                items: 1,
            },
            250: {
                items: 2,
            },
            480: {
                items: 3,
            },
            768: {
                items: 4,
            },
        },
    });
    var itemCount = $productCarousel.find('.owl-item').length;
    if (itemCount <= 4) {
        $productCarousel.find('.owl-nav').hide();
    }
    $productCarousel.on('changed.owl.carousel', function(event) {
        var currentItemCount = event.item.count;
        // Hide or show the navigation arrows based on the current item count
        if (currentItemCount <= 4) {
            $productCarousel.find('.owl-nav').hide();
        } else {
            $productCarousel.find('.owl-nav').show();
        }
    });
    if(product_type == "variant") {
        $.ajax({
            url: $(".variants-by-product").val(),
            type: 'get',
            "data":{product_id: product_id},
            success: function (response) {
                variants_html = "";
                if(response.product_variants_combinations.length > 0) {
                    _this.closest("body").find(".product-modal-popup").find(".variants-title").text(response.variants_title);
                    variants_scroll_class = (response.product_variants_combinations.length > 4) ? "popup-variants-carousel" : "";
                    variants_html += '<ul class="list-variants '+variants_scroll_class+'">';
                    variant_products = {};
                    $(response.product_variants_combinations).each(function(key,val) {  
                        variant_products[val.variants_combination_name] = val;
                        checked = (page_type == "wishlist") ? (key == 0) ? "checked" : "" : (checked_variants_id == val.variants_combination_id) ? "checked" : "";
                        checked_style = (page_type == "wishlist") ? (key == 0) ? "background-color: #000;color: #fff;" : "" : (checked_variants_id == val.variants_combination_id) ? "background-color: #000;color: #fff;" : "";
                        label_style = (val.variants_combination_name).length <= 5 ? "width:41px" : "width:auto";
                        variants_html += '<li class="product-variant-dev"><input type="radio" class="btn-check product-variant" data-type="product-in-popup" name="product_variant_combination_'+val.product_id+'" id="variant-combination-'+val.variants_combination_id+'" value="'+val.variants_combination_id+'" data-value="'+val.variants_combination_name+'" '+checked+'><label style="'+checked_style+' '+label_style+'" class="btn btn-outline-secondary avatar-xs-1 rounded-4 d-flex product-variant-label variant-combination-'+val.variants_combination_id+' '+val.product_available+'" for="variant-combination-'+val.variants_combination_id+'" data-bs-toggle="tooltip" title="'+val.variants_combination_name+'" data-variant-combination="'+val.variants_combination_name+'">'+val.variants_combination_name+'</label></li>';
                        if((key == 0 && page_type == "wishlist") || (checked_variants_id == val.variants_combination_id))  { 
                            _this.closest("body").find(".product-modal-popup").find(".single-product-variants-combination").val((page_type != "wishlist") ? checked_variants_id : val.variants_combination_id);
                            _this.closest("body").find(".product-modal-popup").find(".variant-on-hand").val(val.on_hand);
                            if(val.product_available == "out-of-stock") {
                                _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").text(outOfStockTitle);
                                _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").prop("disabled",true);
                            } else {
                                _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").text(addToCart);
                                _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").prop("disabled",false);
                            }
                        }
                    });
                    variants_html += '</ul>';
                    _this.closest("body").find(".variant-combinations-"+product_id).val(JSON.stringify(variant_products));
                }
                _this.closest("body").find(".product-variants-data").html(variants_html);
                $('.popup-variants-carousel').slick({
                    infinite: true,
                    slidesToShow: 6,
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
                _this.closest("body").find(".product-variants-data").find(".product-variant-label").each(function() {
                    var $label = $(this);
                    if ($label[0].scrollWidth > $label.innerWidth()) {
                        var text = $label.text();
                        while ($label[0].scrollWidth > $label.innerWidth() && text.length > 15) {
                            text = text.slice(0, -1);
                            $label.text(text + '...');
                        }
                    }
                });
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
                var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                });
                // $(".page-loader").hide();
            }
        });
    } else if(product_type == "single") {
        if(modal_product_unit <= 0 && product_trackable == 1) {
            _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").text(outOfStockTitle);
            _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").prop("disabled",true);
        } else {
            _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").text(addToCart);
            _this.closest("body").find(".product-modal-popup").find(".product-add-to-cart").prop("disabled",false);
        }
    }
    if(page_type != "wishlist") {
        is_authenticated = $(this).closest("body").find(".is_authenticated").val();
        if(is_authenticated == 1)
            showWishlist($(this),product_type,product_id,$(this).closest("body").find(".product-modal-popup").find(".single-product-variants-combination").val(),'view_product');
    }
    $(".page-loader").hide();
    $('#modal_box').modal('show');
});

