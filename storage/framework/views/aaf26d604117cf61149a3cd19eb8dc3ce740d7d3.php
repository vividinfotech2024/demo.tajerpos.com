<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>">
  <head>
    <meta charset="utf-8" />
    <title>TajerPOS | <?php echo e(__('admin.login_title')); ?></title>
    <meta http-equiv="x-ua-compatible" content="ie=edge" />
    <meta name="description" content="" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo e(URL::asset('assets/imgs/theme/favicon.png')); ?>" />
    <!-- Template CSS -->
    <link href="<?php echo e(URL::asset('assets/css/main.css?v=1.1')); ?>" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
    <link href="<?php echo e(URL::asset('assets/css/page-loader.css')); ?>" rel="stylesheet" type="text/css" />
    <style>
      body,html {
        height: 100%;
      }
      .form-control{
        background:#fff !important;padding: 10px 0px;
        border-bottom: 1px solid #a9a9a9 !important; border:0px;border-radius:0px;
        height: 49px;
        font-size: 15px;
      }
      .btn{
        background-color: #00a386;
        color: #ffffff;   border-radius: 30px !important;
        padding: 10px 29px;
        font-weight: 600;
        letter-spacing: 1px;
      }
      .input-group-text{
        background: #ffffff;
        color: #a9a9a9;
        border-bottom: 1px solid #a9a9a9 !important;
        font-size: 20px;
        border: 0px;
        padding: 14px 10px;
        border-radius: 0px;width: 38px;
      }
      .spinner {
        background-color: #01c293;
      }
    </style>
  </head> 
  <?php
    $bgImage = app()->getLocale() == 'ar' ? 'login-bg-2.jpg' : 'login-bg.jpg';
  ?>
  <body style="background: url('assets/imgs/theme/<?php echo e($bgImage); ?>') no-repeat <?php echo e(app()->getLocale() == 'ar' ? 'right' : 'left'); ?> / cover;">
    <div class="page-loader"><div class="spinner"></div></div>  
    <?php echo $__env->make('common.language', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <div class="container-fluid h-100">
      <div class="row justify-content-center align-items-center h-100">
        <div class="col-lg-10">
          <div class="" style="">
            <div class="row align-items-center">
              <div class="col-md-6"></div>
              <div class="col-md-5" >
                <form method="POST" action="<?php echo e(route(config('app.prefix_url').'.super-admin')); ?>" novalidate> 
                <?php echo csrf_field(); ?>
                  <div style="">
                    <center><a href="#" ><img src="<?php echo e((!empty($moduleLogos) && isset($moduleLogos['company_logo'])) ? $moduleLogos['company_logo'] : URL::asset('assets/cashier-admin/images/tajer-logo.png')); ?>" class="logo" alt="eMonta" /></a></center> 
                    <h2 class="text-center"><?php echo e(__('admin.welcome_message')); ?></h2>
                    <p class="text-center mb-2"><?php echo e(__('admin.login_prompt')); ?></p>
                    <br/>
                    <div>
                      <div class="input-field-div mb-4">
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <div class="input-group-text"><i class="fa fa-user-circle-o"></i></div> 
                          </div>
                          <input type="email" data-max="100" data-label = "<?php echo e(__('admin.email')); ?>" data-error-msg="<?php echo e(__('validation.email_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF._%+\-@]+$" onkeypress="return restrictCharacters(event)" class="required-field form-input-field form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> input-group-field" name="email" value="<?php echo e(old('email')); ?>" required id="inlineFormInputGroup" placeholder="<?php echo e(__('admin.email_placeholder')); ?>" autocomplete="email" autofocus>
                          <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="invalid-feedback error-message" role="alert">
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
                            <div class="input-group-text"><i class="fa fa-lock"></i></div>
                          </div>
                          <input type="password" data-max="100" data-label="<?php echo e(__('admin.password')); ?>" data-error-msg="<?php echo e(__('validation.pwd_invalid_msg')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF!@#$%^&*_=.,~/<:;?+\-]+$" onkeypress="return restrictCharacters(event)" class="required-field form-input-field form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?> input-group-field" id="inlineFormInputGroup" placeholder="<?php echo e(__('admin.password_placeholder')); ?>" name="password" required autocomplete="password">
                          <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <span class="invalid-feedback error-message" role="alert">
                              <strong><?php echo e($message); ?></strong>
                            </span>
                          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                        <span class="error error-message"></span>
                      </div>
                      <div class="d-flex justify-content-between align-items-center">
                        <button class="btn" id="submit-login"><?php echo e(__('admin.sign_in_button')); ?></button>
                      </div>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script src="<?php echo e(URL::asset('assets/js/vendors/jquery-3.6.0.min.js')); ?>"></script>
    <script src="<?php echo e(URL::asset('assets/js/vendors/bootstrap.bundle.min.js')); ?>"></script>
    <script>
      window.langTranslations = <?php echo json_encode(trans('validation'), 15, 512) ?>;
    </script>
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
      $(document).ready(function() {
        $(".page-loader").hide();
      });
    </script>
  </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/auth/login.blade.php ENDPATH**/ ?>