<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title><?php echo e(!empty($store_details) && !empty($store_details[0]->store_name) ? $store_details[0]->store_name : 'TajerPOS'); ?> | <?php echo e(__('store-admin.login_title')); ?></title>
        <meta http-equiv="x-ua-compatible" content="ie=edge" />
        <meta name="description" content="" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <!-- Favicon -->
        <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(URL::asset('assets/store/images/theme/favicon.png')); ?>" />
        <!-- Template CSS -->
        <link rel="stylesheet" href="<?php echo e(URL::asset('assets/store/css/vendors_css.css')); ?>">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
        <style>
            body,html {
                height: 100% !important;
            }
            body.rtl{
                text-align: right !important;
                direction: rtl;
			}
			.rtl .text-right{text-align: left !important;}
            .form-control{    
                background: #0f4161 !important;
                border: 1px solid #cfcfcf;    
                border-radius: 0px;
                color: #fff !important;
                height: 47px;
            }
            .input-group-text {
                background: #0f4161;
                color: #fff;
                border: 1px solid #cfcfcf;
                font-size: 20px;
            }
            .form-control::-webkit-input-placeholder {
                color: #e9e9e9;
            }
            body::before{
                content: "";
                background: rgb(2 76 120 / 84%);
                height: 100%;
                position: absolute;
                width: 100%;
                top: 0;
            }
            .site-language {
                background: #ff7200;
                border: 1px solid #ff7200;
                padding: 4px 10px;
                border-radius: 7px;
                color: #ffffff;
                font-size: 14px;
                top: 15px;
                position: absolute;
                right: 15px;
            }
            .rtl .site-language{
                right: unset;
                left: 15px;
            }
        </style>
    </head>
    <?php
        $logo_image = !empty($store_details) && !empty($store_details[0]->store_logo) ? $store_details[0]->store_logo : URL::asset('assets/store/images/logo.png');
        $background_image = !empty($store_details) && !empty($store_details[0]->store_background_image) ? $store_details[0]->store_background_image : URL::asset('assets/store/images/login-bg.jpg');
    ?>
    <body style="background:url('<?php echo e($background_image); ?>') no-repeat center center /cover;">  
        <div class="container-fluid h-100">
            <div class="text-right">
                <?php echo $__env->make('common.language', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            </div>
            <div class="row justify-content-center align-items-center h-100">
                <div class="col-md-4">&nbsp;</div>
                <div class="col-md-4">
                    <form method="POST" action="<?php echo e(route(config('app.prefix_url').'.'.$store_url)); ?>" novalidate>
                    <?php echo csrf_field(); ?>
                        <div class="" style="">
                            <center><a href="#"><img src="<?php echo e($logo_image); ?>" class="logo mb-2" alt="<?php echo e(!empty($store_details) && !empty($store_details[0]->store_name) ? $store_details[0]->store_name : 'eMonta'); ?>" style=""/></a></center>
                            <h2 class="text-center" style="color:#fff;"><b><?php echo e(__('store-admin.welcome_message')); ?> <?php echo e(!empty($store_details) && !empty($store_details[0]->store_name) ? $store_details[0]->store_name : 'eMonta'); ?></b></h2>
                            <p class="text-center mb-4" style="color:#c7c7c7;"><?php echo e(__('store-admin.login_prompt')); ?></p>
                            <br/>
                            <input type="hidden" name="store_url" value="<?php echo e(config('app.prefix_url').'.'.$store_url); ?>">
                            <div>
                                <div class="input-field-div mb-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><img src="<?php echo e(URL::asset('assets/store/images/1-01.png')); ?>"></div>
                                        </div>
                                        <input type="email" data-max="100" class="required-field form-input-field form-control input-group-field" data-error-msg="<?php echo e(__('validation.email_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF._%+\-@]+$" onkeypress="return restrictCharacters(event)" data-page="login" data-label = "<?php echo e(__('store-admin.email')); ?>" id="inlineFormInputGroup" name="email" value="<?php echo e(old('email')); ?>" placeholder="<?php echo e(__('store-admin.email_placeholder')); ?>" required autocomplete="email" autofocus>
                                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback error-message" role="alert" style="display:block;">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <span class="error error-message"></span>
                                </div>
                                <div class="input-field-div mb-4">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <div class="input-group-text"><img src="<?php echo e(URL::asset('assets/store/images/1-02.png')); ?>"></div>
                                        </div>
                                        <input type="password" data-max="100" data-label = "<?php echo e(__('store-admin.password')); ?>" data-error-msg="<?php echo e(__('validation.pwd_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF!@#$%^&*_=.,~/<:;?+\-]+$" onkeypress="return restrictCharacters(event)" class="required-field form-input-field form-control input-group-field" data-page="login" id="inlineFormInputGroup" placeholder="<?php echo e(__('store-admin.password_placeholder')); ?>" name="password" required autocomplete="password">
                                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                            <span class="invalid-feedback error-message" role="alert" style="display:block;">
                                                <strong><?php echo e($message); ?></strong>
                                            </span>
                                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                    </div>
                                    <span class="error error-message"></span>
                                </div>
                            </div>
                        </div>
                        <br/>
                        <div class="text-center mx-4"><button class="btn btn-md rounded-0" id="submit-login" style="color: #fff;border: 2px solid #fff;padding: 4px 30px;font-weight: 700;"><?php echo e(__('store-admin.sign_in_button')); ?></button></div>
                    </form>
                </div>
                <div class="col-md-4">&nbsp;</div>
            </div>
        </div>
        <script>
            window.langTranslations = <?php echo json_encode(trans('validation'), 15, 512) ?>;
        </script>
        <!-- Vendor JS -->
        <script src="<?php echo e(URL::asset('assets/store/js/vendors.min.js')); ?>"></script>
        <script src="<?php echo e(URL::asset('assets/js/validation.js')); ?>"></script>
        <script src="<?php echo e(URL::asset('assets/js/common.js')); ?>"></script>
        <script>
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $(document).on("click","#submit-login",function() {
                check_fields = validateFields($(this));
                if(check_fields > 0)
                    return false;
                else
                    return true;
            });
        </script>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/store/login.blade.php ENDPATH**/ ?>