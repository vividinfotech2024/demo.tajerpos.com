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
        <input type="hidden" class="translation-key" value="login_page_title">
        <div class="login-register-area">
            <div class="container">
                <div class="row">
                    <div class="col-md-10 offset-md-1">
                        <div class="row bg-cover bg-center align-items-center  dark-overlay p-0" style="background-image: url('/assets/customer/images/auth.jpg')">
                            <div class="col-lg-6 p-0">
                                <div class="andro_auth-description dark-overlay-2" >
                                    <div class="andro_auth-description-inner">
                                        <h2><?php echo e(__('customer.welcome_back')); ?></h2>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 p-0">
                                <form  method="POST" action="<?php echo e(route($store_url.'.customer-login')); ?>" autocomplete="off">
                                <?php echo csrf_field(); ?>
                                    <input type="hidden" name="current_url" value="<?php echo e($current_url); ?>">
                                    <div class="login-form andro_auth-form">
                                        <h4 class="login-title text-center mb-2"><?php echo e(__('customer.sign_in')); ?></h4>
                                        <p class="text-center mb-4"><?php echo e(__('customer.login_desc')); ?></p>
                                        <div class="row">
                                            <div class="col-lg-12 input-field-div">
                                                <input type="email" data-max="100" data-error-msg="<?php echo e(__('validation.email_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF._%+\-@]+$" onkeypress="return restrictCharacters(event)" class="required-field form-input-field" data-label = "<?php echo e(__('customer.username')); ?>" placeholder="<?php echo e(__('customer.username')); ?>" name="email" value="">
                                                <?php if($errors->has('email')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('email')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <div class="col-lg-12 input-field-div">
                                                <input type="password" data-max="100" data-error-msg="<?php echo e(__('validation.pwd_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF!@#$%^&*_=.,~/<:;?+\-]+$" onkeypress="return restrictCharacters(event)" class="required-field form-input-field" data-label = "<?php echo e(__('customer.password')); ?>" placeholder="<?php echo e(__('customer.password')); ?>" name="password" value="">
                                                <?php if($errors->has('password')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('password')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <!-- <div class="col-sm-12 pt-1 mt-md-0">
                                                <div class="forgotton-password_info float-end">
                                                    <a href="<?php echo e(route($store_url.'.customer-forget-password')); ?>"><?php echo e(__('customer.forgot_password')); ?>?</a>
                                                </div>
                                            </div> -->
                                            <div class="col-lg-12 pt-4 text-center">
                                                <button class="btn custom-btn md-size" id="customer-login"><?php echo e(__('customer.sign_in')); ?></button>
                                            </div>
                                            <div class="andro_auth-seperator">
                                                <span><?php echo e(__('customer.or')); ?></span>
                                            </div>
                                            <!-- <div class="andro_social-login">
                                                <button type="button" class="andro_social-login-btn facebook"><i class="ion-social-facebook"></i> Continue with Facebook </button>
                                                <button type="button" class="andro_social-login-btn google"><i class="ion-social-google"></i> Continue with Google</button>
                                            </div> -->
                                            <p class="text-center"><?php echo e(__('customer.register_content')); ?> <a href="<?php echo e(route($store_url.'.customer-register')); ?>"><?php echo e(__('customer.create_one')); ?></a> </p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo $__env->make('common.customer.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <?php echo $__env->make('common.customer.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <script src="<?php echo e(URL::asset('assets/js/validation.js')); ?>"></script>
        <script>
            $(document).on("click","#customer-login",function() {
                check_fields = validateFields($(this));
                if(check_fields > 0)
                    return false;
                else
                    return true;
            });
        </script>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/customer/login.blade.php ENDPATH**/ ?>