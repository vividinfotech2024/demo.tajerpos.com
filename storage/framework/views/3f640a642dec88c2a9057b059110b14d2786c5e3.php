<script>
    window.langTranslations = <?php echo json_encode(trans('validation'), 15, 512) ?>;
    window.datatableTranslations = <?php echo json_encode(trans('datatables'), 15, 512) ?>;
    window.translations = <?php echo json_encode(trans('store-admin'), 15, 512) ?>;
</script>
<script src="<?php echo e(URL::asset('assets/cashier-admin/js/vendors.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/cashier-admin/vendor_components/apexcharts-bundle/dist/apexcharts.min.js')); ?>"></script>  	
<script src="<?php echo e(URL::asset('assets/cashier-admin/vendor_components/datatable/datatables.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/cashier-admin/vendor_components/OwlCarousel2/dist/owl.carousel.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/cashier-admin/js/template.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/cashier-admin/js/pages/dashboard.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/cashier-admin/js/pages/data-table.js')); ?>"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script src="<?php echo e(URL::asset('assets/js/vendors/select2.min.js')); ?>"></script>
<script src="<?php echo e(URL::asset('assets/js/chat.js')); ?>"></script>
<script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
<script src="<?php echo e(URL::asset('assets/js/common.js')); ?>"></script>
<script>
$(document).ready(function() {
    $(".page-loader").hide();
    CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
    var table = $('#example').DataTable( {
        rowReorder: {
            selector: 'td:nth-child(2)'
        },
        responsive: true
    });
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
// window.onload = function() {
//   $(".page-loader").hide();
// };
</script><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/common/cashier_admin/footer.blade.php ENDPATH**/ ?>