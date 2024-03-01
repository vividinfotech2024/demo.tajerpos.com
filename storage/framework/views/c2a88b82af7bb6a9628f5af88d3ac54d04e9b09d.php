<div class="col-sm-12 col-md-4 col-lg-4 pb-30">
    <div class="account-sidebar around-border">
        <ul class="account-sidebar-list">
            <li class="<?php echo e((request()->is($store_url.'/customer/dashboard')) ? 'active' : ''); ?>"><a href="<?php echo e(route($store_url.'.customer.dashboard')); ?>"><?php echo e(__('customer.your_account')); ?></a></li>
            <li class="<?php echo e((request()->is($store_url.'/customer/orders*')) ? 'active' : ''); ?>"><a href="<?php echo e(route($store_url.'.customer.orders.index')); ?>"><?php echo e(__('customer.your_orders')); ?></a></li>
            <li class="<?php echo e((request()->is($store_url.'/customer/address*')) ? 'active' : ''); ?>"><a href="<?php echo e(route($store_url.'.customer.address.index')); ?>"><?php echo e(__('customer.your_addresses')); ?></a></li>
            <li>
                <a href="<?php echo e(route($store_url.'.customer.logout')); ?>" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><i class="material-icons md-exit_to_app"></i><?php echo e(__('customer.sign_out')); ?></a>
                <form id="logout-form" action="<?php echo e(route($store_url.'.customer.logout')); ?>" method="POST" style="display: none;">
                    <?php echo csrf_field(); ?>
                </form>
            </li>
        </ul>
    </div>
</div><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/common/customer/account_sidebar.blade.php ENDPATH**/ ?>