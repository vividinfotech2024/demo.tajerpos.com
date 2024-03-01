<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo e(($mode == "add") ? trans('admin.add_store_title') : trans('admin.edit_store_title')); ?></title>
        <?php echo $__env->make('common.admin.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </head>
    <body>
        <div class="page-loader"><div class="spinner"></div></div>  
        <div class="screen-overlay"></div>
        <?php echo $__env->make('common.admin.navbar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <main class="main-wrap">
            <?php echo $__env->make('common.admin.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
            <section class="content-main">  
                <?php echo $__env->make('common.admin.search', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
                <div class="body-content">
                    <div class="content-header">
                        <div>
                            <h2 class="content-title card-title"><?php echo e(($mode == "add") ? trans('admin.add_new_store_title') : trans('admin.edit_store')); ?></h2>                       
                        </div> 			
                    </div>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h4><?php echo e(trans('admin.store_info')); ?></h4>
                        </div>
                        <div class="card-body">
                            <form  method="POST" action="<?php echo e(route(config('app.prefix_url').'.admin.store.store')); ?>" class="form-element-data store-form" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                                <input type="hidden" name="mode" class="mode" value=<?php echo e($mode); ?>> 
                                <input type="hidden" name="store_id" class="store_id" value="<?php echo e(!empty($store_details) && !empty($store_details[0]->store_id) ? Crypt::encrypt($store_details[0]->store_id) : ''); ?>">
                                <input type="hidden" name="user_id" class="user-id" value="<?php echo e(!empty($store_details) && !empty($store_details[0]->user_id) ? Crypt::encrypt($store_details[0]->user_id) : ''); ?>">
                                <input type="hidden" class="state-list-url" value="<?php echo e(route('state-list')); ?>">
                                <input type="hidden" class="city-list-url" value="<?php echo e(route('city-list')); ?>">
                                <input type="hidden" class="email-path" value="<?php echo e(route('email-exist')); ?>"> 
                                <input type="hidden" class="is_admin" value="2">
                                <input type="hidden" class="url" value="<?php echo e(route(config('app.prefix_url').'.admin.store.url-exist')); ?>">
                                <?php
                                    $fields_validation = !empty($store_details) && !empty($store_details[0]->store_logo) ? 'optional-field' : 'required-field';
                                    $image_path = !empty($store_details) && !empty($store_details[0]->store_logo) ? $store_details[0]->store_logo : '';
                                    $background_img_validation = !empty($store_details) && !empty($store_details[0]->store_background_image) ? 'optional-field' : 'required-field';
                                    $background_image_path = !empty($store_details) && !empty($store_details[0]->store_background_image) ? $store_details[0]->store_background_image : '';
                                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.owner_name')); ?><span>*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-user"></i></span>									
                                                <input type="text" data-max="50" data-error-msg="<?php echo e(__('validation.invalid_name_err')); ?>" data-label = "<?php echo e(trans('admin.owner_name')); ?>" data-pattern="^[A-Za-z\u0600-\u06FF. ]+$" onkeypress="return restrictCharacters(event)" name="store_user_name" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->store_user_name) ? $store_details[0]->store_user_name : ''); ?>" class="form-control required-field form-input-field" >
                                            </div>
                                            <?php if($errors->has('store_user_name')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_user_name')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div> 
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.shop_name')); ?><span>*</span></label>
                                            <input type="text" data-label = "<?php echo e(trans('admin.shop_name')); ?>" data-error-msg="<?php echo e(__('validation.invalid_company_name_err')); ?>" data-max="100" data-pattern="^[',\-A-Za-z\u0600-\u06FF0-9 .&()]+$" onkeypress="return restrictCharacters(event)" name="store_name" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->store_name) ? $store_details[0]->store_name : ''); ?>" class="form-control required-field form-input-field" >
                                            <?php if($errors->has('store_name')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_name')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.url')); ?><span>*</span></label>
                                            <input type="text" data-label = "<?php echo e(trans('admin.url')); ?>" data-error-msg="<?php echo e(__('validation.invalid_url_err')); ?>" data-max="150" name="store_url" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->store_url) ? $store_details[0]->store_url : ''); ?>" data-pattern="^[0-9-A-Za-z.%\/-]" onkeypress="return restrictCharacters(event)" class="form-control required-field form-input-field store-url" >
                                            <?php if($errors->has('store_url')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_url')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label input-field-div"><?php echo e(trans('admin.phone_number')); ?><span>*</span></label>
                                            <div class="input-group">                                        
                                                <span class="input-group-text"><i class="fa fa-phone"></i></span>
                                                <input type="text" data-label = "<?php echo e(trans('admin.phone_number')); ?>" data-error-msg="<?php echo e(__('validation.invalid_numeric_err')); ?>" data-min="10" data-max="15" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->store_phone_number) ? $store_details[0]->store_phone_number : ''); ?>" name="store_phone_number" data-pattern="^[0-9]+$" onkeypress="return restrictCharacters(event)" class="form-control required-field form-input-field store-phone-number" >
                                            </div>
                                            <?php if($errors->has('store_phone_number')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_phone_number')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label input-field-div"><?php echo e(trans('admin.email_address')); ?><span>*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                                                <input type="text" data-error-msg="<?php echo e(__('validation.email_invalid_msg')); ?>" data-label = "<?php echo e(trans('admin.email_address')); ?>" data-max="100" data-pattern="^[A-Za-z\u0600-\u06FF0-9._%+\-@]+$" onkeypress="return restrictCharacters(event)" data-type="store_admin" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->email) ? $store_details[0]->email : ''); ?>" name="email" class="form-control required-field form-input-field email-field">
                                            </div>
                                            <?php if($errors->has('email')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('email')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label input-field-div"><?php echo e(trans('admin.password')); ?><span>*</span></label>
                                            <div class="input-group">
                                                <?php
                                                    $decrypted_password = !empty($store_details) && !empty($store_details[0]->plain_password) ? decrypt($store_details[0]->plain_password) : '';
                                                ?>
                                                <input type="password" data-label = "<?php echo e(trans('admin.password')); ?>" data-error-msg="<?php echo e(__('validation.pwd_invalid_msg')); ?>" data-min="8" data-max="100" data-pattern="^[A-Za-z\u0600-\u06FF0-9!@#$%^&*_=.,~/<:;?+-]+$" onkeypress="return restrictCharacters(event)" name="store_password" class="form-control required-field form-input-field password" value="<?php echo e($decrypted_password); ?>">
                                                <div class="input-group-text"><span id="user-password" class="fa fa-fw fa-eye field_icon"></span></div>
                                            </div>
                                            <?php if($errors->has('store_password')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_password')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.building_name')); ?><span>*</span></label>
                                            <input type="text" data-label = "<?php echo e(trans('admin.building_name')); ?>" data-max="100" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" name="building_name" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->building_name) ? $store_details[0]->building_name : ''); ?>" class="form-control required-field form-input-field">
                                            <?php if($errors->has('building_name')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('building_name')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <!-- <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.validity')); ?><span>*</span></label>
                                            <input type="date" data-label = "<?php echo e(trans('admin.validity')); ?>"  value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->store_validity_date) ? $store_details[0]->store_validity_date : ''); ?>" name="store_validity_date" class="form-control required-field form-input-field validity-date" >
                                            <?php if($errors->has('store_validity_date')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_validity_date')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div> -->
                                        <!-- <div class='form-check'>
                                            <input class="form-check-input" type="checkbox" name="add_payment_details" value="1">
                                            <label class="form-check-label">Need to add payment details</label>
                                        </div> -->
                                    </div>
                                    <div class="col-md-6">
                                        <!-- <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.address')); ?><span>*</span></label>
                                            <textarea data-label = "<?php echo e(trans('admin.address')); ?>" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-max="200" style="height: 131px;" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" name="store_address" class="form-control required-field form-input-field"><?php echo e(!empty($store_details) && !empty($store_details[0]->store_address) ? $store_details[0]->store_address : ''); ?></textarea>
                                            <?php if($errors->has('store_address')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_address')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div> -->
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.street_name')); ?><span>*</span></label>
                                            <input type="text" data-label = "<?php echo e(trans('admin.street_name')); ?>" data-max="100" name="street_name" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->street_name) ? $store_details[0]->street_name : ''); ?>" class="form-control required-field form-input-field">
                                            <?php if($errors->has('street_name')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('street_name')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.country')); ?><span>*</span></label>
                                            <select class="form-select required-field form-input-field country-list dropdown-search" data-label = "<?php echo e(trans('admin.country')); ?>" name="store_country">
                                                <option value="">--Select Country--</option> 
                                                <?php if(isset($countries) && !empty($countries)): ?>
                                                    <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <option value="<?php echo e($country->id); ?>" <?php echo e(!empty($store_details) && !empty($store_details[0]->country_id) && ($store_details[0]->country_id == $country->id) ? "selected" : ''); ?>><?php echo e($country->name); ?></option> 
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                <?php endif; ?>
                                            </select>
                                            <?php if($errors->has('store_country')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_country')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <input type="hidden" class="state-id" value="<?php echo e(!empty($store_details) && !empty($store_details[0]->state_id) ? $store_details[0]->state_id : ''); ?>">
                                        <input type="hidden" class="city-id" value="<?php echo e(!empty($store_details) && !empty($store_details[0]->city_id) ? $store_details[0]->city_id : ''); ?>">
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.state')); ?><span>*</span></label>
                                            <select class="form-select required-field form-input-field state-list dropdown-search" data-label = "<?php echo e(trans('admin.state')); ?>" name="store_state">
                                                <option value="">--Select State--</option>    
                                            </select>
                                            <?php if($errors->has('store_state')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_state')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.city')); ?><span>*</span></label>
                                            <select class="form-select required-field form-input-field city-list dropdown-search" data-label = "<?php echo e(trans('admin.city')); ?>" name="store_city">
                                                <option value="">--Select City--</option>  
                                            </select>
                                            <?php if($errors->has('store_city')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_city')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label"><?php echo e(trans('admin.postal_code')); ?><span>*</span></label>
                                            <input type="text" data-label = "<?php echo e(trans('admin.postal_code')); ?>" data-error-msg="<?php echo e(__('validation.invalid_numeric_err')); ?>" data-min="5" data-max="11" data-pattern="^[0-9]+$" onkeypress="return restrictCharacters(event)" value = "<?php echo e(!empty($store_details) && !empty($store_details[0]->postal_code) ? $store_details[0]->postal_code : ''); ?>" name="store_postal_code" class="form-control required-field form-input-field" >
                                            <?php if($errors->has('store_postal_code')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_postal_code')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label input-field-div"><?php echo e(trans('admin.store_logo')); ?><span>*</span></label>
                                            <input type="file" data-type="image" data-label = "<?php echo e(trans('admin.store_logo')); ?>" name="store_logo_image" class="form-control form-input-field <?php echo e($fields_validation); ?> image-field" value="">
                                            <div class="file-preview row">
                                                <div class="d-flex mt-2 ms-2 file-preview-item">
                                                    <div class="align-items-center thumb">
                                                        <input type="hidden" class="image-path-data" value="<?php echo e($image_path); ?>">
                                                        <img src="<?php echo e($image_path); ?>" class="img-fit image-preview" alt="Item">
                                                    </div>
                                                    <div class="remove"><button class="btn btn-sm btn-link remove-attachment" data-image-type = "required" type="button"><i class="fa fa-close"></i></button></div>
                                                </div>
                                            </div>
                                            <?php if($errors->has('store_logo_image')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_logo_image')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                        <div class="mb-4 input-field-div">
                                            <label class="form-label input-field-div"><?php echo e(trans('admin.login_background_image')); ?><span>*</span></label>
                                            <input type="file" data-type="image" data-label = "<?php echo e(trans('admin.login_background_image')); ?>" name="store_background_image" class="form-control form-input-field <?php echo e($background_img_validation); ?> image-field" value="">
                                            <div class="file-preview row">
                                                <div class="d-flex mt-2 ms-2 file-preview-item">
                                                    <div class="align-items-center thumb">
                                                        <input type="hidden" class="image-path-data" value="<?php echo e($background_image_path); ?>">
                                                        <img src="<?php echo e($background_image_path); ?>" class="img-fit image-preview" alt="Item">
                                                    </div>
                                                    <div class="remove"><button class="btn btn-sm btn-link remove-attachment" data-image-type = "required" type="button"><i class="fa fa-close"></i></button></div>
                                                </div>
                                            </div>
                                            <?php if($errors->has('store_background_image')): ?>
                                                <span class="text-danger error-message"><?php echo e($errors->first('store_background_image')); ?></span>
                                            <?php endif; ?>
                                            <span class="error error-message"></span>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <button class="btn btn-md rounded font-sm hover-up" id="save-store-info"><?php echo e(trans('admin.save')); ?></button>
                                    </div>
                                </div>                    
                            </form>
                        </div>
                    </div>
                </div>
            </section>
            <?php echo $__env->make('common.admin.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </main>
        <?php echo $__env->make('common.admin.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <script src="<?php echo e(URL::asset('assets/js/validation.js')); ?>"></script>
        <script src="<?php echo e(URL::asset('assets/js/select2.min.js')); ?>"></script>
        <script>
            $(document).on("click","#save-store-info",function() {
                check_fields = validateFields($(this));
                if(check_fields > 0)
                    return false;
                else
                    return true;
            });
            //Minimum validation for Date field
            // var today = new Date();
            // var month = today.getMonth()+1;
            // var date = today.getDate();
            // var min_date = today.getFullYear() + '-' + (month<10 ? '0' : '') + month + '-' + (date <10 ? '0' : '') + date;
            // $(".validity-date").attr("min",min_date);

            // Check URL is Unique
            $(document).on("change",".store-url",function() {
                store_url = $(this).val();
                if(store_url != "")
                    isURLExist($(this));
            });
            $(document).ready(function() {
                $('.dropdown-search').select2();
            });
        </script>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/admin/store/create.blade.php ENDPATH**/ ?>