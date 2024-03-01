<!doctype html>
<html class="no-js" lang="zxx">
    <head>
        <?php echo $__env->make('common.customer.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </head>
    <body>
        <div class="body_overlay"></div>
        <?php echo $__env->make('common.customer.mobile_navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.mini_cart', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.breadcrumbs', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <input type="hidden" class="translation-key" value="view_order_page_title">
        <div class="checkout-area">
            <div class="container">
                <h4 class="mb-3"><?php echo e(__('customer.order_details')); ?></h4>
                <div class="row">
                    <div class="col-lg-3">
                        <p class="mb-2"><?php echo e(__('customer.ordered_on')); ?> <?php echo e((isset($order_details) && !empty($order_details) && isset($order_details[0]['ordered_at'])) ? DateTime::createFromFormat('d-m-Y H:i', $order_details[0]['ordered_at'])->format('d F Y') : ""); ?></p>
                    </div>
                    <div class="col-lg-6">
                        <p class="mb-2"><?php echo e(__('customer.order')); ?> # <?php echo e((isset($order_details) && !empty($order_details) && isset($order_details[0]['order_number'])) ? $order_details[0]['order_number'] : ""); ?></p>
                    </div>
                    <!-- <div class="col-lg-3 text-end">
                        <p class="mb-2 fw-bold"><a class="text-primary" href="#"><i class="fa fa-download"></i> Invoice</a></p>
                    </div> -->
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-4 mb-3">
                                <p class="mb-2 text-dark fw-bold"><?php echo e(__('customer.shipping_address')); ?></p>
                                <?php
                                    $shipping_Address = '';
                                    if(isset($order_details) && !empty($order_details) && isset($order_details[0]['building_name']))
                                        $shipping_Address .= $order_details[0]['building_name'].",<br/>"; 
                                    if(isset($order_details) && !empty($order_details) && isset($order_details[0]['street_name']))
                                        $shipping_Address .= $order_details[0]['street_name'].",<br/>"; 
                                    if(isset($order_details) && !empty($order_details) && isset($order_details[0]['city_name']))
                                        $shipping_Address .= $order_details[0]['city_name'].",";
                                    if(isset($order_details) && !empty($order_details) && isset($order_details[0]['state_name']))
                                        $shipping_Address .= $order_details[0]['state_name'].",<br/>"; 
                                    if(isset($order_details) && !empty($order_details) && isset($order_details[0]['pincode']))
                                        $shipping_Address .= $order_details[0]['pincode'].",<br/>"; 
                                    if(isset($order_details) && !empty($order_details) && isset($order_details[0]['country_name']))
                                        $shipping_Address .= $order_details[0]['country_name'];
                                ?>
                                <p class="mb-0"><?php echo e((isset($order_details) && !empty($order_details) && isset($order_details[0]['customer_name'])) ? $order_details[0]['customer_name'] : ""); ?></p>
                                <p class="mb-0"><?php echo $shipping_Address; ?></p>
                            </div>
                           
                            <div class="col-lg-4 mb-3">
                                <p class="mb-2 text-dark fw-bold"><?php echo e(__('customer.order_summery')); ?></p>
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0"><?php echo e(__('customer.items_subtotal')); ?>: </p>
                                    <p class="mb-0">SAR <?php echo e((isset($order_details) && !empty($order_details) && isset($order_details[0]['sub_total_amount'])) ? number_format($order_details[0]['sub_total_amount'], 2, '.', '') : ""); ?> </p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0"><?php echo e(__('customer.tax')); ?>: </p>
                                    <p class="mb-0">SAR <?php echo e((isset($order_details) && !empty($order_details) && isset($order_details[0]['total_tax_amount'])) ? number_format($order_details[0]['total_tax_amount'], 2, '.', '') : ""); ?> </p>
                                </div>
                                <!-- <div class="d-flex justify-content-between"> 
                                    <p class="mb-0">Total: </p>
                                    <p class="mb-0">SAR 0.00 </p>
                                </div> -->
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0 fw-bold"><?php echo e(__('customer.grand_total')); ?>: </p>
                                    <p class="mb-0 fw-bold">SAR <?php echo e((isset($order_details) && !empty($order_details) && isset($order_details[0]['total_amount'])) ? number_format($order_details[0]['total_amount'], 2, '.', '') : ""); ?> </p>
                                </div>
                            </div>
                             <div class="col-lg-4 mb-3">
                                <p class="mb-2 text-dark fw-bold"><?php echo e(__('customer.payment_details')); ?></p>

                                <div class="d-flex justify-content-between">
                                    <p class="mb-0"><?php echo e(__('customer.transaction_ref')); ?>: </p>
                                    <p class="mb-0"><?php echo e($payment_details->tran_ref); ?></p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0"><?php echo e(__('customer.transaction_time')); ?>: </p>
                                    <p class="mb-0"><?php echo e($payment_details->created_at); ?></p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0"><?php echo e(__('customer.card')); ?>: </p>
                                    <p class="mb-0"><?php echo e($payment_details->payment_method); ?></p>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <p class="mb-0"><?php echo e(__('customer.account')); ?>: </p>
                                    <p class="mb-0"><?php echo e($payment_details->payment_desc); ?></p>
                                </div>
                               
                                <div class="d-flex justify-content-center mt-2">
                                    <?php if($payment_details->response_status == 'A'): ?>
                                    <p class="mb-0" style="color:green" ><strong>Payment successfully</strong> </p>

                                    <?php elseif($payment_details->response_status == 'C'): ?>
                                    <p class="mb-0" style="color:red" ><strong>Payment Cancelled</strong> </p>
                                    <?php else: ?>
                                    <p class="mb-0" style="color:orange" ><strong>Declined - <?php echo e($payment_details->response_message); ?></strong> </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mb-3">
                    <div class="card-body">
                        <h5><?php echo e((isset($order_details) && !empty($order_details) && isset($order_details[0]['status_name'])) ? $order_details[0]['status_name'] : ""); ?></h5> 
                        <!-- <p>Package was handed to resident</p> -->
                        <?php if(isset($order_details) && !empty($order_details)): ?>
                            <?php $__currentLoopData = $order_details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="row mb-2">
                                    <div class="col-lg-9">
                                        <div class="d-md-flex deliery-cont">
                                            <?php $product_image = ""; ?>
                                            <?php if($order['category_image'] != ""): ?>  
                                                <?php 
                                                    $product_images = explode("***",$order['category_image']);
                                                    $product_image = $product_images[0]; 
                                                ?>
                                            <?php endif; ?>
                                            <img style="width:120px;" src="<?php echo e($product_image); ?>" >
                                            <div class="ms-4">
                                                <a class="mb-0" href="<?php echo e(route($store_url.'.customer.single-product', ['id' => Crypt::encrypt($order['product_id'])])); ?>" target="_blank"><?php echo e($order['product_name']); ?></a>
                                                <p class="mb-2 fw-bold">SAR <?php echo e(number_format(($order['sub_total'] / $order['quantity']), 2, '.', '')); ?></p> 
                                                <?php if(!empty($order['product_variants'])): ?>
                                                    <p class="mb-2"><?php echo e($order['variants_name']." : "); ?> <span class="fw-bold"><?php echo e($order['product_variants']); ?></span></p>
                                                <?php endif; ?>
                                                <!-- <button class="btn btn-warning text-white flex-shrink-0">Buy it again</button> -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <!-- <button type="button" class="btn btn-outline-secondary mb-2 w-100">Leave seller feedback</button>
                                        <button type="button" class="btn btn-outline-secondary mb-2 w-100">Write a product review</button>
                                        <button type="button" class="btn btn-outline-secondary mb-2 w-100">Archive order</button> -->
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $__env->make('common.customer.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/customer/orders_details.blade.php ENDPATH**/ ?>