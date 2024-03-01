<div class="offcanvas offcanvas-end address-info-popup" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 id="offcanvasRightLabel" class="modal-address-title"><?php echo e(__('customer.add_address')); ?></h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <form method="POST" action="<?php echo e(route($store_url.'.customer.address.store')); ?>" class="address-from-data form-element-data overflow-auto" enctype="multipart/form-data" novalidate>
        <?php echo csrf_field(); ?>
        <input type="hidden" class="state-list-url" value="<?php echo e(route('state-list')); ?>">
        <input type="hidden" class="city-list-url" value="<?php echo e(route('city-list')); ?>">
        <input type="hidden" name="mode" class="mode" value=""> 
        <input type="hidden" name="address_id" class="address-id" value="">
        <input type="hidden" class="state-id" value="">
        <input type="hidden" class="city-id" value="">
        <div class="offcanvas-body">
            <div>
                <div class="mb-3 input-field-div">
                    <label for="customer-name" class="form-label"><?php echo e(__('customer.full_name')); ?><span>*</span></label>
                    <input type="text" id="customer-name" data-label="<?php echo e(__('customer.full_name')); ?>" data-error-msg="<?php echo e(__('validation.invalid_name_err')); ?>" data-pattern="^[A-Za-z\u0600-\u06FF. ]+$" onkeypress="return restrictCharacters(event)" name="customer_name" data-max="50" class="form-control customer-name required-field form-input-field" value="">
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="mobile-number" class="form-label"><?php echo e(__('customer.phone_number')); ?><span>*</span></label>
                    <input type="text" id="mobile-number" data-label="<?php echo e(__('customer.phone_number')); ?>" name="mobile_number" data-min="10" data-max="12" class="form-control customer-mobile-no required-field form-input-field" data-pattern="^[0-9]+$" data-error-msg="<?php echo e(__('validation.invalid_numeric_err')); ?>" onkeypress="return restrictCharacters(event)" required value="">
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="building-name" class="form-label"><?php echo e(__('customer.building_name')); ?><span>*</span></label>
                    <input type="text" id="building-name" data-label="<?php echo e(__('customer.building_name')); ?>" name="building_name" data-max="100" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" class="form-control customer-building-name required-field form-input-field" required value="">
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="street-name" class="form-label"><?php echo e(__('customer.street_name')); ?><span>*</span></label>
                    <input type="text" id="street-name" data-label="<?php echo e(__('customer.street_name')); ?>" name="street_name" data-max="100" class="form-control customer-street-name required-field form-input-field"  data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" required value="">
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="country" class="form-label"><?php echo e(__('customer.country')); ?><span>*</span></label>
                    <select id="country" class="form-control required-field form-input-field country-list" data-label="<?php echo e(__('customer.country')); ?>" name="country_id">
                        <option value="">--Select Country--</option> 
                        <?php if(isset($countries) && !empty($countries)): ?>
                            <?php $__currentLoopData = $countries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $country): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($country->id); ?>"><?php echo e($country->name); ?></option> 
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </select>
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="state" class="form-label"><?php echo e(__('customer.state')); ?><span>*</span></label>
                    <select id="state" class="form-control required-field form-input-field state-list" data-label="<?php echo e(__('customer.state')); ?>" name="state_id">
                        <option value="">--Select State--</option>    
                    </select>
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="city" class="form-label"><?php echo e(__('customer.city')); ?><span>*</span></label>
                    <select id="city" class="form-control required-field form-input-field city-list" data-label="<?php echo e(__('customer.city')); ?>" name="city_id">
                        <option value="">--Select City--</option>  
                    </select>
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="pincode" class="form-label"><?php echo e(__('customer.pincode')); ?><span>*</span></label>
                    <input type="text" id="pincode" name="pincode" data-label="<?php echo e(__('customer.pincode')); ?>" data-pattern="^[0-9]+$" data-error-msg="<?php echo e(__('validation.invalid_numeric_err')); ?>" onkeypress="return restrictCharacters(event)" data-min="5" data-max="11" class="form-control customer-pincode required-field form-input-field" required value="">
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="landmark" class="form-label"><?php echo e(__('customer.nearest_landmark')); ?></label>
                    <input type="text" id="landmark" name="landmark" data-label="<?php echo e(__('customer.nearest_landmark')); ?>" data-max="100" class="form-control customer-landmark form-input-field" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" value="">
                    <span class="error error-message"></span>
                </div>
                <div class="mb-3 input-field-div">
                    <label for="address-type" class="form-label"><?php echo e(__('customer.address_type')); ?><span>*</span></label>
                    <input type="text" id="address-type" data-label="<?php echo e(__('customer.address_type')); ?>" name="address_type" data-max="100" data-error-msg="<?php echo e(__('validation.invalid_address_err')); ?>" data-pattern="^[A-Za-z0-9\u0600-\u06FF ',./&()+-]+$" onkeypress="return restrictCharacters(event)" class="form-control required-field form-input-field customer-address-type" value="">
                    <span class="error error-message"></span>
                </div>
            </div>
        </div>
        <div class="modal-footer mb-3">
            <button type="button" class="btn btn-outline-danger me-2" data-bs-dismiss="offcanvas"><i class="fa fa-close me-1"></i> <?php echo e(__('customer.close')); ?></button>
            <button type="button" class="btn btn-success me-2 modal-address-title" id="save-address-info"><?php echo e(__('customer.add_address')); ?></button>
        </div>
    </form>
</div><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/common/customer/address_popup.blade.php ENDPATH**/ ?>