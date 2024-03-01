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
        <input type="hidden" class="translation-key" value="account_page_title">
        <div class="account-page-area">
            <div class="container">
                <div class="row">
                    <?php echo $__env->make('common.customer.account_sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                    <div class="col-sm-12 col-md-8 col-lg-8 pb-30">
                        <div class="account-info">
                            <form method="POST" action="<?php echo e(route($store_url.'.customer.profile')); ?>" class="form-element-data">
                            <?php echo csrf_field(); ?>
                                <div class="account-setting-item">
                                    <div class="sub-section-title">
                                        <h3 class="title-tag mb-3"><?php echo e(__('customer.profile')); ?></h3>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-12 col-lg-12">
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('customer.name')); ?><span>*</span></label>
                                                <input type="text" class="form-control required-field form-input-field" name="customer_name" data-label = "<?php echo e(__('customer.name')); ?>" data-error-msg="<?php echo e(__('validation.invalid_name_err')); ?>" data-pattern="^[A-Za-z\u0600-\u06FF. ]+$" onkeypress="return restrictCharacters(event)" data-max="100" value="<?php echo e(!empty($customer_details) && !empty($customer_details[0]->customer_name) ? $customer_details[0]->customer_name : ''); ?>" placeholder="">
                                                <?php if($errors->has('customer_name')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('customer_name')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('customer.email')); ?><span>*</span></label>
                                                <input type="email" class="form-control required-field form-input-field" name="email" data-label = "<?php echo e(__('customer.email')); ?>" data-error-msg="<?php echo e(__('validation.email_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF._%+\-@]+$" onkeypress="return restrictCharacters(event)" data-max="100" value="<?php echo e(!empty($customer_details) && !empty($customer_details[0]->email) ? $customer_details[0]->email : ''); ?>" placeholder="">
                                                <?php if($errors->has('email')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('email')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('customer.phone_number')); ?><span>*</span></label>
                                                <input type="text" class="form-control required-field form-input-field" name="phone_number" data-min="10" data-max="12"  data-pattern="^[0-9]+$" data-error-msg="<?php echo e(__('validation.invalid_numeric_err')); ?>" onkeypress="return restrictCharacters(event)" data-label = "<?php echo e(__('customer.phone_number')); ?>" value="<?php echo e(!empty($customer_details) && !empty($customer_details[0]->phone_number) ? $customer_details[0]->phone_number : ''); ?>" placeholder="">
                                                <?php if($errors->has('phone_number')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('phone_number')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="account-setting-item account-setting-button">
                                        <button class="btn btn-small" id="save-profile-info"><?php echo e(__('customer.save')); ?></button>
                                    </div>
                                </div>
                                <div class="account-setting-item account-setting-button"></div>
                            </form>
                            <form method="POST" action="<?php echo e(route($store_url.'.customer.update-password')); ?>" class="form-element-data">
                            <?php echo csrf_field(); ?>
                                <div class="account-setting-item account-setting-avatar">
                                    <div class="sub-section-title">
                                        <h3 class="title-tag mb-3"><?php echo e(__('customer.reset_password_cart_title')); ?></h3>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6 col-lg-6">
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('customer.current_password')); ?><span>*</span></label>
                                                <div class="input-group">
                                                    <input type="password" data-max="100" data-error-msg="<?php echo e(__('validation.pwd_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF!@#$%^&*_=.,~/<:;?+\-]+$" onkeypress="return restrictCharacters(event)" data-label = "<?php echo e(__('customer.current_password')); ?>" class="form-control input-field required-field form-input-field" name="current_password" placeholder="">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><span class="fa fa-fw fa-eye field_icon user-password"></span></span>
                                                    </div>
                                                </div>
                                                <?php if($errors->has('current_password')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('current_password')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('customer.new_password')); ?><span>*</span></label>
                                                <div class="input-group">
                                                    <input type="password" data-max="100" data-error-msg="<?php echo e(__('validation.pwd_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF!@#$%^&*_=.,~/<:;?+\-]+$" onkeypress="return restrictCharacters(event)" data-label = "<?php echo e(__('customer.new_password')); ?>" class="form-control input-field required-field form-input-field" name="new_password" placeholder="">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><span class="fa fa-fw fa-eye field_icon user-password"></span></span>
                                                    </div>
                                                </div>
                                                <?php if($errors->has('new_password')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('new_password')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('customer.confirm_password')); ?><span>*</span></label>
                                                <div class="input-group">
                                                    <input type="password" data-max="100" data-error-msg="<?php echo e(__('validation.pwd_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF!@#$%^&*_=.,~/<:;?+\-]+$" onkeypress="return restrictCharacters(event)" data-label = "<?php echo e(__('customer.confirm_password')); ?>" class="form-control input-field required-field form-input-field" name="confirm_password" placeholder="">
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"><span class="fa fa-fw fa-eye field_icon user-password"></span></span>
                                                    </div>
                                                </div>
                                                <?php if($errors->has('confirm_password')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('confirm_password')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="account-setting-item account-setting-button">
                                        <button class="btn btn-small" id="save-password"><?php echo e(__('customer.reset_password')); ?></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $__env->make('common.customer.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <script src="<?php echo e(URL::asset('assets/js/validation.js')); ?>"></script>
        <script>
            //Hide and show the password
            $(function () {
                $(document).on("click",".user-password",function() {
                    $(this).toggleClass("fa-eye fa-eye-slash");
                    var type = $(this).hasClass("fa-eye-slash") ? "text" : "password";
                    $(this).closest(".input-field-div").find(".input-field").attr("type", type);
                });
            });
            $(document).on("click","#save-password",function() {
                check_fields = validateFields($(this));
                if(check_fields > 0)
                    return false;
                else
                    return true;
            });
            $(document).on("click","#upload-profile-image",function() {
                check_fields = validateFields($(this));
                if(check_fields > 0)
                    return false;
                else
                    return true;
            }); 
            $(document).on("click","#save-profile-info",function() {
                check_fields = validateFields($(this));
                if(check_fields > 0)
                    return false;
                else
                    return true;
            }); 
        </script>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/customer/dashboard.blade.php ENDPATH**/ ?>