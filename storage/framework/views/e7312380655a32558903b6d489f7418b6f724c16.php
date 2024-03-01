<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo e(__('store-admin.profile_title',['company' => Auth::user()->company_name])); ?></title>
        <?php echo $__env->make('common.cashier_admin.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <style>
            .img-md {
                width: 112px;
                height: 112px;
            }
        </style>
    </head>
    <body class="hold-transition light-skin sidebar-mini theme-danger fixed">
        <div class="wrapper">
            <?php echo $__env->make('common.cashier_admin.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <?php echo $__env->make('common.cashier_admin.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <div class="content-wrapper" >
                <div class="container-full">
                    <section class="content ">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h4 class="mb-0"><?php echo e(__('store-admin.your_profile_details')); ?></h4>
                            </div>
                            <div class="card-body">
                                <form  method="POST" action="<?php echo e(route(config('app.prefix_url').'.'.$store_url.'.'.config('app.module_prefix_url').'.profile')); ?>" class="form-element-data" enctype="multipart/form-data">
                                <?php echo csrf_field(); ?>
                                    <?php
                                        $profile_image = !empty($cashier_admin_details) && !empty($cashier_admin_details[0]->profile_image) ? $cashier_admin_details[0]->profile_image : '';
                                    ?>
                                    <input type="hidden" name="user_id" class="user-id" value="<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->id) ? Crypt::encrypt($cashier_admin_details[0]->id) : ''); ?>">
                                    <input type="hidden" class="email-path" value="<?php echo e(route('email-exist')); ?>">
                                    <input type="hidden" class="is_admin" value="3">
                                    <input type="hidden" name="store_id" class="store-id" value="<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->store_id) ? Crypt::encrypt($cashier_admin_details[0]->store_id) : ''); ?>">
                                    <input type="hidden" class="state-list-url" value="<?php echo e(route('state-list')); ?>">
                                    <input type="hidden" class="city-list-url" value="<?php echo e(route('city-list')); ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('store-admin.name')); ?><span>*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                                                    </div>
                                                    <input type="text" data-label = "<?php echo e(__('store-admin.name')); ?>" data-error-msg="<?php echo e(__('validation.invalid_name_err')); ?>" data-pattern="^[A-Za-z\u0600-\u06FF. ]+$" onkeypress="return restrictCharacters(event)" data-max="100" name="name" value = "<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->name) ? $cashier_admin_details[0]->name : ''); ?>" class="form-control required-field form-input-field auth-user-name">
                                                </div>
                                                <?php if($errors->has('name')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('name')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <?php if(!empty(Auth::user()->is_admin) && Auth::user()->is_admin == 2): ?> 
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(__('store-admin.store_name')); ?><span>*</span></label>
                                                    <input type="text" data-label = "<?php echo e(trans('store-admin.store_name')); ?>" data-error-msg="<?php echo e(__('validation.invalid_company_name_err')); ?>" data-max="100" data-pattern="^[',\-A-Za-z\u0600-\u06FF0-9 .&()]+$" onkeypress="return restrictCharacters(event)" name="store_name" value = "<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->company_name) ? $cashier_admin_details[0]->company_name : ''); ?>" class="form-control required-field form-input-field" >
                                                    <?php if($errors->has('store_name')): ?>
                                                        <span class="text-danger error-message"><?php echo e($errors->first('store_name')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="error error-message"></span>
                                                </div>
                                            <?php endif; ?>
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('store-admin.email_address')); ?></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                                    </div>
                                                    <input type="email" data-label = "<?php echo e(__('store-admin.email_address')); ?>" data-type="cashier-admin" name="email" value = "<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->email) ? $cashier_admin_details[0]->email : ''); ?>" class="form-control required-field form-input-field email-field" disabled>
                                                </div>
                                                <?php if($errors->has('email')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('email')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <div class="mb-4 input-field-div">
                                                <label class="form-label"><?php echo e(__('store-admin.phone_number')); ?><span>*</span></label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                                    </div>
                                                    <input type="text" data-label = "<?php echo e(__('store-admin.phone_number')); ?>" data-min="10" data-max="12" name="phone_number" value = "<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->phone_number) ? $cashier_admin_details[0]->phone_number : ''); ?>" data-pattern="^[0-9]+$" data-error-msg="<?php echo e(__('validation.invalid_numeric_err')); ?>" onkeypress="return restrictCharacters(event)" class="form-control required-field form-input-field">
                                                </div>
                                                <?php if($errors->has('phone_number')): ?>
                                                    <span class="text-danger error-message"><?php echo e($errors->first('phone_number')); ?></span>
                                                <?php endif; ?>
                                                <span class="error error-message"></span>
                                            </div>
                                            <?php if(!empty(Auth::user()->is_admin) && Auth::user()->is_admin == 2): ?> 
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(trans('store-admin.building_name')); ?><span>*</span></label>
                                                    <input type="text" data-label = "<?php echo e(trans('store-admin.building_name')); ?>" data-max="100" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" name="building_name" value = "<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->building_name) ? $cashier_admin_details[0]->building_name : ''); ?>" class="form-control required-field form-input-field">
                                                    <?php if($errors->has('building_name')): ?>
                                                        <span class="text-danger error-message"><?php echo e($errors->first('building_name')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="error error-message"></span>
                                                </div>
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(trans('store-admin.street_name')); ?><span>*</span></label>
                                                    <input type="text" data-label = "<?php echo e(trans('store-admin.street_name')); ?>" data-max="100" name="street_name" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" value = "<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->street_name) ? $cashier_admin_details[0]->street_name : ''); ?>" class="form-control required-field form-input-field">
                                                    <?php if($errors->has('street_name')): ?>
                                                        <span class="text-danger error-message"><?php echo e($errors->first('street_name')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="error error-message"></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if(!empty(Auth::user()->is_admin) && Auth::user()->is_admin == 3): ?> 
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(__('store-admin.profile')); ?> (100 x 100)</label>
                                                    <div class="input-upload my-file">             
                                                        <input type="hidden" name="remove_image" class="remove-image" value="0">                       
                                                        <input class="upload form-control image-field mb-2 form-input-field" data-type="image" type="file" data-label = "Avatar" name="profile_image">
                                                        <div class="file-preview row ml-0">
                                                            <div class="d-flex mt-2 ms-2 file-preview-item">
                                                                <div class="align-items-center thumb">
                                                                    <img src="<?php echo e($profile_image); ?>" class="img-fit image-preview img-md" data-type="<?php echo e(__('store-admin.profile')); ?>" alt="Item">
                                                                </div>
                                                                <div class="remove"><button class="btn btn-sm btn-link remove-attachment" type="button"><i class="fa fa-close"></i></button></div>
                                                            </div>
                                                        </div>
                                                        <?php if($errors->has('profile_image')): ?>
                                                            <span class="text-danger error-message"><?php echo e($errors->first('profile_image')); ?></span>
                                                        <?php endif; ?>
                                                        <span class="error error-message"></span>
                                                        <div class="profile-image-preview dnone"></div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if(!empty(Auth::user()->is_admin) && Auth::user()->is_admin == 2): ?> 
                                            <div class="col-md-6">
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(trans('admin.country')); ?><span>*</span></label>
                                                    <div class="controls">
                                                        <select class="form-control required-field form-input-field country-list dropdown-search" data-label = "<?php echo e(trans('admin.country')); ?>" name="store_country">
                                                            <option value="">--Select Country--</option> 
                                                            <?php if(isset($countries) && !empty($countries)): ?>
                                                                <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <option value="<?php echo e($country->id); ?>" <?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->country_id) && ($cashier_admin_details[0]->country_id == $country->id) ? "selected" : ''); ?>><?php echo e($country->name); ?></option> 
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            <?php endif; ?>
                                                        </select>
                                                        <?php if($errors->has('store_country')): ?>
                                                            <span class="text-danger error-message"><?php echo e($errors->first('store_country')); ?></span>
                                                        <?php endif; ?>
                                                        <span class="error error-message"></span>
                                                    </div>
                                                </div>
                                                <input type="hidden" class="state-id" value="<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->state_id) ? $cashier_admin_details[0]->state_id : ''); ?>">
                                                <input type="hidden" class="city-id" value="<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->city_id) ? $cashier_admin_details[0]->city_id : ''); ?>">
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(trans('admin.state')); ?><span>*</span></label>
                                                    <select class="form-control required-field form-input-field state-list dropdown-search" data-label = "<?php echo e(trans('admin.state')); ?>" name="store_state">
                                                        <option value="">--Select State--</option>    
                                                    </select>
                                                    <?php if($errors->has('store_state')): ?>
                                                        <span class="text-danger error-message"><?php echo e($errors->first('store_state')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="error error-message"></span>
                                                </div>
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(trans('admin.city')); ?><span>*</span></label>
                                                    <select class="form-control required-field form-input-field city-list dropdown-search" data-label = "<?php echo e(trans('admin.city')); ?>" name="store_city">
                                                        <option value="">--Select City--</option>  
                                                    </select>
                                                    <?php if($errors->has('store_city')): ?>
                                                        <span class="text-danger error-message"><?php echo e($errors->first('store_city')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="error error-message"></span>
                                                </div>
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(trans('admin.postal_code')); ?><span>*</span></label>
                                                    <input type="text" data-label = "<?php echo e(trans('admin.postal_code')); ?>" data-error-msg="<?php echo e(__('validation.invalid_numeric_err')); ?>" data-min="5" data-max="11" data-pattern="^[0-9]+$" onkeypress="return restrictCharacters(event)" value = "<?php echo e(!empty($cashier_admin_details) && !empty($cashier_admin_details[0]->postal_code) ? $cashier_admin_details[0]->postal_code : ''); ?>" name="store_postal_code" class="form-control required-field form-input-field" >
                                                    <?php if($errors->has('store_postal_code')): ?>
                                                        <span class="text-danger error-message"><?php echo e($errors->first('store_postal_code')); ?></span>
                                                    <?php endif; ?>
                                                    <span class="error error-message"></span>
                                                </div>
                                                <div class="mb-4 input-field-div">
                                                    <label class="form-label"><?php echo e(__('store-admin.profile')); ?> (100 x 100)</label>
                                                    <div class="input-upload my-file">             
                                                        <input type="hidden" name="remove_image" class="remove-image" value="0">                       
                                                        <input class="upload form-control image-field mb-2 form-input-field" data-type="image" type="file" data-label = "Avatar" name="profile_image">
                                                        <div class="file-preview row ml-0">
                                                            <div class="d-flex mt-2 ms-2 file-preview-item">
                                                                <div class="align-items-center thumb">
                                                                    <img src="<?php echo e($profile_image); ?>" class="img-fit image-preview img-md" data-type="<?php echo e(__('store-admin.profile')); ?>" alt="Item">
                                                                </div>
                                                                <div class="remove"><button class="btn btn-sm btn-link remove-attachment" type="button"><i class="fa fa-close"></i></button></div>
                                                            </div>
                                                        </div>
                                                        <?php if($errors->has('profile_image')): ?>
                                                            <span class="text-danger error-message"><?php echo e($errors->first('profile_image')); ?></span>
                                                        <?php endif; ?>
                                                        <span class="error error-message"></span>
                                                        <div class="profile-image-preview dnone"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?> 
                                            <div class="col-md-6">&nbsp;</div>
                                        <?php endif; ?>
                                        <div class="col-md-12">
                                            <div class="text-right">
                                                <button class="btn btn-primary" id="save-profile-info"><?php echo e(__('store-admin.save')); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            <?php echo $__env->make('common.cashier_admin.copyright', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </div>
        <?php echo $__env->make('common.cashier_admin.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <script src="<?php echo e(URL::asset('assets/js/validation.js')); ?>"></script>
        <script src="<?php echo e(URL::asset('assets/js/select2.min.js')); ?>"></script>
        <script>
            $(document).on("click","#save-profile-info",function() {
                check_fields = validateFields($(this));
                if(check_fields > 0)
                    return false;
                else 
                    return true;     
            });
            $(document).ready(function() {
                $('.dropdown-search').select2();
            });
        </script>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/cashier_admin/profile.blade.php ENDPATH**/ ?>