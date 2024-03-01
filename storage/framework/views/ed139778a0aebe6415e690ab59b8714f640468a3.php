<div class="breadcrumbs_aree breadcrumbs_bg mb-70" data-bgimg="<?php echo e(URL::asset('assets/customer/images/others/breadcrumbs-bg.png')); ?>"> 
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcrumbs_text">
                    <?php if(isset($breadcrumbs) && !empty($breadcrumbs)): ?>
                        <ul>
                            <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $breadcrumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if(!$loop->last): ?>
                                    <li><a href="<?php echo e($breadcrumb['url']); ?>"><?php echo e($breadcrumb['name']); ?></a></li>
                                    <span> &raquo; </span>
                                <?php else: ?>
                                    <li><?php echo e($breadcrumb['name']); ?></li>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/common/customer/breadcrumbs.blade.php ENDPATH**/ ?>