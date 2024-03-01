<div class="modal fade" id="modal_box" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <span aria-hidden="true"><i class="ion-android-close"></i></span>
            </button>
            <div class="modal_body product-modal-popup">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-5 col-md-5 col-sm-12">
                            <div class="modal_tab product-images-modal-tab">
                                <!-- <div class="tab-content product-details-large">
                                    <div class="tab-pane fade show active" id="tab1" role="tabpanel">
                                        <div class="modal_tab_img">
                                            <a href="#" class="modal-product-url" target="_blank"><img class="modal-product-img" src="" alt=""></a>
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                        </div>
                        <div class="col-lg-7 col-md-7 col-sm-12 single_product single-product-details" style="border:none;">
                            <div class="modal_right">
                                <div class="modal_title mb-10">
                                    <a href="#" class="modal-product-url" target="_blank"><h2 class="modal-product-name"></h2></a>
                                </div>
                                <p><b class="category-name"></b></p>
                                <p><b class="sub-category-name"></b></p>
                                <div class="modal_price mb-10">
                                    <span class="new_price modal-product-price"></span>
                                </div>
                                <div class="modal_description mb-15"><p class="modal-product-description"></p></div>
                                <div class="variants_selects">
                                    <p class="variants-title"></p>
                                    <div class="product-variants-data">
                                    </div>
                                    <div class="modal_add_to_cart">
                                        <form action="#">
                                            <input type="hidden" class="single-product-id product-id" value="">
                                            <input type="hidden" class="single-product-trackable" value="">
                                            <input type="hidden" class="modal-product-unit" value="">
                                            <input type="hidden" class="product-unit" value="">
                                            <input type="hidden" class="single-product-type" value="">
                                            <input type="hidden" class="single-product-variants-combination" value="">
                                            <input type="hidden" class="modal-variant-on-hand" value="">
                                            <input type="hidden" class="variant-on-hand" value="">
                                            <div class="mb-10">
                                                <input min="1" max="100" step="1" value="1" onkeypress="return isNumber(event)" class="quantity add-product-quantity" type="number">
                                            </div>
                                            <div class="d-flex mb-10">
                                                <?php if(auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id): ?>
                                                    <button type="button" title="<?php echo e(__('customer.add_to_wishlist')); ?>" data-page="" class="product-wishlist no-margin-left"><i data-wishlist-type="add" class="wishlist-icon far fa-heart"></i> <?php echo e(__('customer.add_to_wishlist')); ?></button>
                                                <?php endif; ?>
                                                <button type="button" data-type="product-in-popup" class="product-add-to-cart add-to-cart<?php echo e(!(auth()->guard('customer')->check() && session()->has('authenticate_user') && session('authenticate_user')->store_id == $store_id) ? ' no-margin-left' : ''); ?>">
                                                    <?php echo e(__('customer.add_to_cart')); ?>

                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/common/customer/view_popup.blade.php ENDPATH**/ ?>