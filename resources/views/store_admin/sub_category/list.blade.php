<!DOCTYPE html>
<html lang="en">
    <head>  
        @php $role_name = Auth::user()->is_admin == 2 ? __('store-admin.store_admin') : __('store-admin.cashier');  @endphp
        <title>{{ __('store-admin.subcategory_list_title',['company' => Auth::user()->company_name, 'role_name' => $role_name]) }}</title>
        @include('common.cashier_admin.header')
    </head>
    @php
        $prefix_url = config('app.module_prefix_url');
    @endphp
    <body class="hold-transition light-skin sidebar-mini theme-danger fixed">
        <div class="wrapper">
            @include('common.cashier_admin.navbar')
            @include('common.cashier_admin.sidebar') 
            <div class="content-wrapper" >
                <div class="container-full">
                    <section class="content">
                        <div class="box">
                            <div class="content-header px-30">
                                <div class="d-lg-flex align-items-center">
                                    <div class="mr-auto">
                                        <h3 class="page-title">{{ __('store-admin.all_sub_categories') }}</h3>
                                    </div>
                                    <div class="text-right">
                                        <button class="btn btn-sm btn-success export-sub-category-data" data-export-type="pdf" type="button"> <span>PDF</span> </button>
                                        <button class="btn btn-sm btn-danger export-sub-category-data" data-export-type="excel" type="button"> <span>Excel</span> </button>
                                        <a href="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.sub-category.create') }}"><button class="btn btn-sm btn-primary" type="button"><i class="fa fa-plus"></i> <span>{{ __('store-admin.add_sub_category') }}</span> </button></a>					
                                    </div>
                                </div>
                                <hr/>
                            </div>
                            <div class="box-body">
                                <div class="table-responsive">
                                    <table class="table table-hover nowrap table-bordered display table-striped" id="sub-category-list">
                                        <input type="hidden" class="list-url" value="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.sub-category.index')}}">
                                        <input type="hidden" class="update-order-no-url" value="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.sub-category.update-order-number')}}">
                                        <thead>
                                            <tr>                                       
                                                <th>{{ __('store-admin.id') }}</th>      
                                                <th scope="col">{{ __('store-admin.category_id') }}</th>                
                                                <th scope="col">{{ __('store-admin.category') }}</th>
                                                <th scope="col">{{ __('store-admin.sub_category_id') }}</th>
                                                <th scope="col">{{ __('store-admin.sub_category') }}</th>
                                                <!-- <th scope="col">Ordering Number</th> -->
                                                <!-- <th scope="col" width="20%">Sub Category Image</th>
                                                <th scope="col">Banner</th>
                                                <th scope="col">Icon</th> -->
                                                <th scope="col">{{ __('store-admin.created_at') }}</th>
                                                <th scope="col">{{ __('store-admin.status') }}</th>
                                                <th scope="col" class="text-end">{{ __('store-admin.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>      
                                                <td class="text-center" colspan="8">{{ trans('datatables.sEmptyTable') }}</td> 
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            @include('common.cashier_admin.copyright') 
        </div>
        @include('common.cashier_admin.footer')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
        <script>
            $(document).on("change",".sub-category-status",function(event) {
                event.preventDefault();
                value = (this.checked) ? 1 : 0;
                sub_category_id = $(this).closest("tr").find(".sub-category-id").val();
                status_url = $(this).closest("tr").find(".status-url").val();
                type = $(this).attr("data-type");
                $.ajax({
                    url: status_url,
                    type: 'post',
                    data: {_token: CSRF_TOKEN,sub_category_id: sub_category_id,value: value,type:type},
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
            $(document).ready(function() {
                subCategoryList();
                // list_url = $('#sub-category-list').find(".list_url").val();
                // if ( $.fn.dataTable.isDataTable( '#sub-category-list' ) )
                //     sub_category_table.destroy();
                // sub_category_table = $('#sub-category-list').DataTable({
                //     "processing": true,
                //     "serverSide": true,
                //     "order": [[ 0, "desc" ]],
                //     "ajax": {
                //         "url": list_url,
                //         "dataType": "json",
                //         "type": "get",
                //         "data":{type: 'all'},
                //     },
                //     "columns": [
                //         { "data": "id" },
                //         { "data": "category_number" },
                //         { "data": "category_name" },  
                //         { "data": "sub_category_number" },
                //         { "data": "sub_category_name" },
                //         // { "data": "sub_category_image" },
                //         // { "data": "banner" },  
                //         // { "data": "icon" }, 
                //         { "data": "created_at"},  
                //         { "data": "status","orderable": false,"searchable":false},
                //         { "data": "action","orderable": false,"searchable":false},
                //     ]	 
                // });
            });
            $(document).on("click",".delete-sub-category",function() {
                event.preventDefault();
                delete_category_link = $(this).attr("href");
                swal({
                    title: translations.delete_confirmation_title,
                    text: translations.delete_confirmation_text,
                    icon: "warning",
                    buttons: {
                        cancel: {
                            text: translations.cancel_button_text,
                            value: null,
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: translations.ok_button_text,
                            value: true,
                            visible: true,
                            closeModal: true,
                        },
                    },
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $(location).attr('href',delete_category_link);
                    }
                });
            });
            function subCategoryList(exportType = "") {
                list_url = $('#sub-category-list').find(".list_url").val();
                if(exportType != "") {
                    $.ajax({
                        url: list_url,
                        type: 'get',
                        xhrFields: {
                            responseType: exportType === "pdf" ? 'blob' : undefined
                        },
                        dataType: exportType !== "pdf" ? 'json' : undefined,
                        "data":{type: 'all',export_type:exportType},
                        success: function(response){
                            if(exportType == "pdf") {
                                var blobUrl = URL.createObjectURL(response);
                                var link = document.createElement('a');
                                link.href = blobUrl;
                                link.download = 'sub-category-details.pdf';
                                link.click();
                                URL.revokeObjectURL(blobUrl);
                            }
                            if(exportType == "excel") {
                                var fileUrl = response.file_url;
                                window.location.href = fileUrl; 
                            }
                        },
                        error: function (xhr, status, error) {
                            console.error(error);
                        }
                    });
                } else {
                    if ( $.fn.dataTable.isDataTable( '#sub-category-list' ) )
                        sub_category_table.destroy();
                    sub_category_table = $('#sub-category-list').DataTable({
                        "language": {
                            "sEmptyTable": "{{ trans('datatables.sEmptyTable') }}",
                            "sInfo": "{{ trans('datatables.sInfo', ['start' => '_START_', 'end' => '_END_', 'total' => '_TOTAL_']) }}",
                            "sInfoEmpty": "{{ trans('datatables.sInfoEmpty') }}",
                            "sInfoFiltered": "{{ trans('datatables.sInfoFiltered') }}",
                            "sInfoPostFix": "{{ trans('datatables.sInfoPostFix') }}",
                            "sInfoThousands": "{{ trans('datatables.sInfoThousands') }}",
                            "sLengthMenu": "{{ trans('datatables.sLengthMenu') }}",
                            "sLoadingRecords": "{{ trans('datatables.sLoadingRecords') }}",
                            "sProcessing": "{{ trans('datatables.sProcessing') }}",
                            "sSearch": "{{ trans('datatables.sSearch') }}",
                            "sZeroRecords": "{{ trans('datatables.sZeroRecords') }}",
                            "oPaginate": {
                                "sFirst": "{{ trans('datatables.oPaginate.sFirst') }}",
                                "sLast": "{{ trans('datatables.oPaginate.sLast') }}",
                                "sNext": "{{ trans('datatables.oPaginate.sNext') }}",
                                "sPrevious": "{{ trans('datatables.oPaginate.sPrevious') }}"
                            },
                            "oAria": {
                                "sSortAscending": "{{ trans('datatables.oAria.sSortAscending') }}",
                                "sSortDescending": "{{ trans('datatables.oAria.sSortDescending') }}"
                            }
                        },
                        "processing": true,
                        "serverSide": true,
                        "order": [[ 0, "desc" ]],
                        "ajax": {
                            "url": list_url,
                            "dataType": "json",
                            "type": "get",
                            "data":{type: 'all'},
                        },
                        "columns": [
                            { "data": "id" },
                            { "data": "category_number" },
                            { "data": "category_name" },  
                            { "data": "sub_category_number" },
                            { "data": "sub_category_name" },
                            // { "data": "sub_category_image" },
                            // { "data": "banner" },  
                            // { "data": "icon" }, 
                            // { "data": "order_number"},  
                            { "data": "created_at"},  
                            { "data": "status","orderable": false,"searchable":false},
                            { "data": "action","orderable": false,"searchable":false},
                        ]	 
                    });
                }
            }
            $(document).on("click",".export-sub-category-data",function(){
                exportType = $(this).attr("data-export-type");
                subCategoryList(exportType);
            });
            $(document).on("change",".category-order-number",function(event) {
                event.preventDefault();
                old_order_number = $(this).attr("data-order-number");
                order_number = $(this).val();
                category_id = $(this).closest("tr").find(".sub-category-id").val();
                update_order_no_url = $(this).closest("table").find(".update-order-no-url").val();
                $.ajax({
                    url: update_order_no_url,
                    type: 'post',
                    data: {_token: CSRF_TOKEN,order_number: order_number,category_id : category_id, old_order_number : old_order_number},
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
        </script>
    </body>
</html>