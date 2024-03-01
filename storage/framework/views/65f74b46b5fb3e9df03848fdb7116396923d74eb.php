<script>
    window.langTranslations = <?php echo json_encode(trans('validation'), 15, 512) ?>;
    window.customerTranslations = <?php echo json_encode(trans('customer'), 15, 512) ?>;
</script>
<script src="<?php echo e(URL::asset('assets/customer/js/vendor/jquery-3.6.0.min.js')); ?>"></script>
<!-- <script src="<?php echo e(URL::asset('assets/customer/js/vendor/jquery-migrate-3.3.2.min.js')); ?>"></script> -->
<script src="<?php echo e(URL::asset('assets/customer/js/vendor/bootstrap.bundle.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/customer/js/slick.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/customer/js/owl.carousel.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/customer/js/wow.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/customer/js/jquery.scrollup.min.js')); ?>"></script>
<!-- <script src="<?php echo e(URL::asset('assets/customer/js/jquery.nice-select.js')); ?>"></script> -->
<script src="<?php echo e(URL::asset('assets/customer/js/jquery.magnific-popup.min.js')); ?>"></script>
<!-- <script src="<?php echo e(URL::asset('assets/customer/js/mailchimp-ajax.js')); ?>"></script> -->
<script src="<?php echo e(URL::asset('assets/customer/js/jquery-ui.min.js')); ?>"></script>
<!-- <script src="<?php echo e(URL::asset('assets/customer/js/jquery.zoom.min.js')); ?>"></script> -->
<script src="<?php echo e(URL::asset('assets/customer/js/main.js')); ?>"></script>
<!--modernizr min js here-->
<script src="<?php echo e(URL::asset('assets/customer/js/vendor/modernizr-3.11.2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/common.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/validation.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/toastr.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/customer/js/common.js')); ?>"></script>
<script>
    $(document).ready(function() {
        CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        <?php if(Session::has('message')): ?>
            toastr.options =
            {
                "closeButton" : true,
                "progressBar" : true
            }
            toastr.success("<?php echo e(session('message')); ?>");
        <?php endif; ?>
        <?php if(Session::has('error')): ?>
            toastr.options =
            {
                "closeButton" : true,
                "progressBar" : true
            }
            toastr.error("<?php echo e(session('error')); ?>");
        <?php endif; ?>
        <?php if(Session::has('info')): ?>
            toastr.options =
            {
                "closeButton" : true,
                "progressBar" : true
            }
            toastr.info("<?php echo e(session('info')); ?>");
        <?php endif; ?>
        <?php if(Session::has('warning')): ?>
            toastr.options =
            {
                "closeButton" : true,
                "progressBar" : true
            }
            toastr.warning("<?php echo e(session('warning')); ?>");
        <?php endif; ?>
    });
</script><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/common/customer/script.blade.php ENDPATH**/ ?>