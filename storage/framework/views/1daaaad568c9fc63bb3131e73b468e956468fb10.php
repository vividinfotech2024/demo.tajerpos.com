<!DOCTYPE html>
<html lang="en">
    <head>
        <title><?php echo e(trans('admin.store_list_title')); ?></title>
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
                            <h2 class="content-title card-title"><?php echo e(trans('admin.all_store')); ?></h2>                       
                        </div> 
                        <a href="<?php echo e(route(config('app.prefix_url').'.admin.store.create')); ?>" class="btn btn-primary btn-sm rounded"><?php echo e(trans('admin.add_new_store')); ?></a>					
                    </div>
                    <div class="card mb-4 all-store-list">
                        <header class="card-header">
                            <div class="row gx-3">
                                <div class="col-lg-3 col-md-6 me-auto">
                                    <h4><?php echo e(trans('admin.store_info')); ?></h4>
                                </div>  
                                <!-- <div class="col-lg-3 col-6 col-md-3">
                                    <select class="form-select filter-by-conditions">
                                        <option value="clear">Filter by </option>
                                        <option value="all">All</option>
                                        <option value="recent">Recently added</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>	  -->
                            </div>
                        </header>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered pay-sel" id="store-list-table" >
                                    <thead>
                                        <tr>
                                            <th scope="col"><?php echo e(trans('admin.s_no')); ?></th> 
                                            <th scope="col"><?php echo e(trans('admin.shop_id')); ?></th> 
                                            <th scope="col"><?php echo e(trans('admin.shop_name')); ?></th>                                      
                                            <th scope="col"><?php echo e(trans('admin.store_logo')); ?></th>    
                                            <th scope="col"><?php echo e(trans('admin.email')); ?></th>     
                                            <th scope="col"><?php echo e(trans('admin.phone_number')); ?></th>     
                                            <th scope="col"><?php echo e(trans('admin.store_admin')); ?></th>
                                            <th scope="col"><?php echo e(trans('admin.cashier')); ?></th>
                                            <th scope="col"><?php echo e(trans('admin.customer')); ?></th>
                                            <th scope="col"><?php echo e(trans('admin.app')); ?></th>
                                            <!-- <th scope="col"><?php echo e(trans('admin.validity')); ?></th> -->
                                            <th scope="col"><?php echo e(trans('admin.action')); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody class = "store-list-tbody">
                                        <tr>
                                            <td class="text-center" colspan="11"><?php echo e(trans('datatables.sEmptyTable')); ?></td>
                                        </tr>
                                    </tbody> 
                                </table>
                            </div>
                        </div>   
                    </div>
                    <div class="modal" id="reminderModal">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body">
                                    <div class="modal-header mb-4">
                                        <h4 class="modal-title">Reminder</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-3">&nbsp;</div>
                                        <div class="col-md-6">
                                            <form action="<?php echo e(route(config('app.prefix_url').'.admin.store.reminder-notification')); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                                <input type="hidden" name="type" value="expire">
                                                <input type="hidden" name="store_id" class="store-id" value="">
                                                <button class="btn btn-primary btn-sm rounded expire-reminder">Expire</button> 
                                            </form>
                                        </div>
                                        <div class="col-md-3">&nbsp;</div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-3">&nbsp;</div>
                                        <div class="col-md-6">
                                        <form action="<?php echo e(route(config('app.prefix_url').'.admin.store.reminder-notification')); ?>" method="POST">
                                            <?php echo csrf_field(); ?>
                                                <input type="hidden" name="type" value="balance">
                                                <input type="hidden" name="store_id" class="store-id" value="">
                                                <button class="btn btn-primary btn-sm rounded balance-reminder">Pay the Balance</button> 
                                            </form>
                                        </div>
                                        <div class="col-md-3">&nbsp;</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php echo $__env->make('common.admin.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        </main>
        <?php echo $__env->make('common.admin.script', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
        <script src="<?php echo e(URL::asset('assets/js/validation.js')); ?>"></script>
        <script src="<?php echo e(URL::asset('assets/js/common.js')); ?>"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
        <script>
            $(document).on("change",".application-access",function(event) {
                event.preventDefault();
                application_access = (this.checked) ? 1 : 0;
                store_id = $(this).closest("tr").find(".store_id").val();
                _type = $(this).attr("name");
                UpdateDeviceAccess(application_access,store_id,_type);
            });

            $(document).on("change",".store-status",function(event) {
                event.preventDefault();
                store_status = (this.checked) ? 1 : 0;
                store_id = $(this).closest("tr").find(".store_id").val();
                $.ajax({
                    url: "<?php echo e(route(config('app.prefix_url').'.admin.store.update-status')); ?>",
                    type: 'post',
                    data: {_token: CSRF_TOKEN,store_id: store_id,store_status: store_status},
                    success: function(response){
                        toastr.options =
                        {
                            "closeButton" : true,
                            "progressBar" : true
                        }
                        toastr.success(response.message);
                    }
                });
            });

            function UpdateDeviceAccess(access,store_id,type) {
                $.ajax({
                    url: "<?php echo e(route(config('app.prefix_url').'.admin.store.update')); ?>",
                    type: 'post',
                    data: {_token: CSRF_TOKEN,store_id: store_id,access: access,type:type},
                    success: function(response){
                        toastr.options =
                        {
                            "closeButton" : true,
                            "progressBar" : true
                        }
                        toastr.success(response.message);
                    }
                });
            }

            $(document).on("change",".filter-by-conditions",function(event) {
                event.preventDefault();
                _this = $(this);
                filter_condition = $(this).val();        
                StoreList(filter_condition);
            });

            $(document).ready(function() {
                StoreList('all');
            });

            function StoreList(filter_condition) {
                if ( $.fn.dataTable.isDataTable( '#store-list-table' ) )
                    store_list_table.destroy();
                store_list_table = $('#store-list-table').DataTable({
                    "language": {
                        "sEmptyTable": "<?php echo e(trans('datatables.sEmptyTable')); ?>",
                        "sInfo": "<?php echo e(trans('datatables.sInfo', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_'])); ?>",
                        "sInfoEmpty": "<?php echo e(trans('datatables.sInfoEmpty')); ?>",
                        "sInfoFiltered": "<?php echo e(trans('datatables.sInfoFiltered')); ?>",
                        "sInfoPostFix": "<?php echo e(trans('datatables.sInfoPostFix')); ?>",
                        "sInfoThousands": "<?php echo e(trans('datatables.sInfoThousands')); ?>",
                        "sLengthMenu": "<?php echo e(trans('datatables.sLengthMenu')); ?>",
                        "sLoadingRecords": "<?php echo e(trans('datatables.sLoadingRecords')); ?>",
                        "sProcessing": "<?php echo e(trans('datatables.sProcessing')); ?>",
                        "sSearch": "<?php echo e(trans('datatables.sSearch')); ?>",
                        "sZeroRecords": "<?php echo e(trans('datatables.sZeroRecords')); ?>",
                        "oPaginate": {
                            "sFirst": "<?php echo e(trans('datatables.oPaginate.sFirst')); ?>",
                            "sLast": "<?php echo e(trans('datatables.oPaginate.sLast')); ?>",
                            "sNext": "<?php echo e(trans('datatables.oPaginate.sNext')); ?>",
                            "sPrevious": "<?php echo e(trans('datatables.oPaginate.sPrevious')); ?>"
                        },
                        "oAria": {
                            "sSortAscending": "<?php echo e(trans('datatables.oAria.sSortAscending')); ?>",
                            "sSortDescending": "<?php echo e(trans('datatables.oAria.sSortDescending')); ?>"
                        }
                    },
                    "processing": true,
                    "serverSide": true,
                    "order": [[ 0, "desc" ]],
                    "ajax": {
                        "url": "<?php echo e(route(config('app.prefix_url').'.admin.store.index')); ?>",
                        "dataType": "json",
                        "type": "get",
                        "data":{type: filter_condition},
                    },
                    "columns": [
                        { "data": "id" },
                        { "data": "store_number" },
                        { "data": "store_name" }, 
                        { "data": "store_logo" },
                        { "data": "email" },
                        { "data": "store_phone_number" },
                        { "data": "web_status","orderable": false,"searchable":false},
                        { "data": "cashier_status","orderable": false,"searchable":false},
                        { "data": "customer_access","orderable": false,"searchable":false},
                        { "data": "app_status","orderable": false,"searchable":false},
                        // { "data": "validity"},
                        { "data": "action","orderable": false,"searchable":false},
                    ]	 
                });
            }
            
            $(document).on("click",".add-payment",function() {
                store_id = $(this).closest("tr").find(".store_id").val();
                $(this).closest(".body-content").find(".payment-store-id").val('').val(store_id);
            });

            $(document).on("click",".store-delete",function() {
                event.preventDefault();
                delete_store_link = $(this).attr("href");
                swal({
                    title: adminTranslations.delete_confirmation_title,
                    text: adminTranslations.delete_confirmation_text,
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: adminTranslations.cancel_button_text,
                            value: null,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: adminTranslations.ok_button_text,
                            value: true,
                            visible: true,
                            closeModal: true,
                        },
                    },
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $(location).attr('href',delete_store_link);
                    }
                });
            });

            $(document).on("click",".send-reminder",function() {
                store_id = $(this).closest("tr").find(".store_id").val();
                $(this).closest("body").find(".store-id").val(store_id);
            });
        </script>
    </body>
</html><?php /**PATH /var/www/html/dev.tajerpos.com/resources/views/admin/store/list.blade.php ENDPATH**/ ?>