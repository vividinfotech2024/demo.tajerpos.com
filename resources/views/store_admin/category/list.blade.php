<!DOCTYPE html>
<html lang="en">
    <head>
        @php $role_name = Auth::user()->is_admin == 2 ? __('store-admin.store_admin') : __('store-admin.cashier');  @endphp
        <title>{{ __('store-admin.category_list_title',['company' => Auth::user()->company_name, 'role_name' => $role_name]) }}</title>
        @include('common.cashier_admin.header') 
        <!-- <link href="{{ URL::asset('assets/cashier-admin/vendor_components/dropzone/dropzone.min.css') }}" rel="stylesheet" type="text/css" />  
        <style>
            .dropzone {
                background: white;
                border-radius: 5px;
                border: 2px dashed rgb(0, 135, 247);
                border-image: none;
                max-width: 500px;
                margin-left: auto;
                margin-right: auto;
            }
            .custom-error-messages .error-messages {
                color : red;
            }
        </style>-->
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
                                <div class="d-flex row align-items-center ">
                                    <div class="mr-auto">
                                        <h3 class="page-title">{{ __('store-admin.all_categories') }}</h3>
                                    </div>
                                    <div class="text-right ">
                                        <!-- <button class="btn btn-default import-category-data" data-toggle="modal" data-target="#import-category" type="button"><span>Import</span> </button> -->
                                        <button class="btn btn-success export-category-data" data-export-type="pdf" type="button"> <span>{{ __('store-admin.pdf') }}</span> </button>
                                        <button class="btn btn-danger export-category-data" data-export-type="excel" type="button"> <span>{{ __('store-admin.excel') }}</span> </button>		
                                        <a href="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.category.create') }}"><button class="btn btn-primary" type="button"><i class="fa fa-plus"></i> <span>{{ __('store-admin.add_category') }}</span> </button></a>					
                                    </div>
                                </div>
                                <hr/>
                            </div>
                            <!-- <div class="modal center-modal fade" id="import-category" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Import categories using a CSV file</h5>
                                            <button type="button" class="close" data-dismiss="modal">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="import-category-form" class="dropzone" enctype="multipart/form-data">
                                                <div class="fallback">
                                                    <input name="file" type="file" />   
                                                </div>
                                            </form>
                                            <div class="custom-error-messages"></div>
                                        </div>
                                        <div class="modal-footer modal-footer-uniform">
                                            <div class="float-left">
                                                <a href="{{ URL::asset('import_CSV/import-category.csv') }}" download="sample-category-import.csv">Download sample CSV</a>
                                            </div>
                                            <div class="float-right">
                                                <button type="button" class="waves-effect waves-light btn btn-outline btn-light mb-5 btn-md" data-dismiss="modal">Cancel</button>
                                                <button type="button" class="waves-effect waves-light btn btn-dark mb-5 btn-md import-category-csv">Upload</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                            <div class="box-body">
                                <div class="table-responsive">
                                <table class="table table-hover nowrap table-bordered display table-striped" id="category-list1">
                                    <input type="hidden" class="list-url" value="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.category.index')}}">
                                    <input type="hidden" class="update-order-no-url" value="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.category.update-order-number')}}">
                                    <input type="hidden" class="import-category-url" value="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.category.import')}}">
                                    <thead>
                                        <tr>
                                            <th>#</th>  
                                            <th scope="col">{{ __('store-admin.category_id') }}</th>
                                            <th scope="col">{{ __('store-admin.category') }}</th>                    
                                            <!-- <th scope="col">Category Image</th> -->
                                            <!-- <th scope="col">Banner</th> -->
                                            <th scope="col">{{ __('store-admin.icon') }}</th>
                                            <!-- <th scope="col">Ordering Number</th> -->
                                            <!-- <th scope="col">Featured</th> -->
                                            <th scope="col">{{ __('store-admin.created_at') }}</th>
                                            <th scope="col">{{ __('store-admin.status') }}</th>
                                            <th scope="col" class="text-end">{{ __('store-admin.action') }}</th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>      
                                            <td class="text-center" colspan="7">{{ trans('datatables.sEmptyTable') }}</td>
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
        <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
        <!-- <script src="{{ URL::asset('assets/cashier-admin/vendor_components/dropzone/dropzone.min.js') }}"></script>   -->
        <script>
            $(document).on("change",".category-status",function(event) {
                event.preventDefault();
                value = (this.checked) ? 1 : 0;
                category_id = $(this).closest("tr").find(".category-id").val();
                status_url = $(this).closest("tr").find(".status-url").val();
                type = $(this).attr("data-type");
                $.ajax({
                    url: status_url,
                    type: 'post',
                    data: {_token: CSRF_TOKEN,category_id: category_id,value: value,type : type},
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
            // Dropzone.autoDiscover = false;
            $(document).ready(function() {
                categoryList();
                /*const import_category = new Dropzone("#import-category-form", {
                    url: $(".import-category-url").val(),
                    autoProcessQueue: false, // Disable auto processing of the queue
                    paramName: "file", // The name that will be used to transfer the file
                    maxFilesize: 5, // Set your desired max file size (in MB)
                    maxFiles: 1,
                    acceptedFiles: ".csv", // Set allowed file types
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // Include the CSRF token
                    },
                    init: function () {
                        this.on("addedfile", function (file) {
                            // Remove all files except the first one
                            if (this.files.length > 1) {
                                this.removeFile(this.files[0]);
                            }
                        });

                        this.on("maxfilesexceeded", function (file) {
                            this.removeAllFiles();
                            this.addFile(file);
                        });
                    },
                });
                import_category.on("addedfile", function (file) {
                    var errorMessageElement = $(file.previewElement).find('.dz-error-message');
                    errorMessageElement.hide();
                    var errorMarkElement = $(file.previewElement).find('.dz-error-mark');
                    errorMarkElement.hide();
                });

                // Handle click on the upload button
                $(".import-category-csv").on("click", function () {
                    // Process the queue manually when the button is clicked
                    import_category.processQueue();
                });

                // Event listener for a successful file upload
                import_category.on("success", function (file, response) {
                    console.log(response); // Log the server response
                    // You can add additional logic here based on the server response
                });

                // Event listener for a failed file upload
                import_category.on("error", function (file, errorMessage) {
                    if (errorMessage.errors) {
                        errors = errorMessage.errors;
                        console.log("errors");
                        console.log(errors);
                        if(errors.length > 0) {
                            error_messages = "";
                            $(errors).each(function(errorKey,error) {
                                console.log("errorKey "+errorKey);
                                console.log("error "+error);
                                error_messages += "<p class='error-messages'>"+error+"</p>";
                            });
                            console.log("error_messages"); 
                            console.log(error_messages);
                            $(".custom-error-messages").html(error_messages);
                        }
                    } 
                });
                $(".import-category-data").on("click", function () {
                    // Reset the Dropzone by removing all files
                    import_category.removeAllFiles();
                    import_category.processQueue();
                });*/
            });

            $(document).on("change",".category-order-number",function(event) {
                event.preventDefault();
                old_order_number = $(this).attr("data-order-number");
                order_number = $(this).val();
                category_id = $(this).closest("tr").find(".category-id").val();
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
            $(document).on("click",".delete-category",function(event) {
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
            function categoryList(exportType = "") {
                list_url = $('#category-list1').find(".list-url").val();
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
                                link.download = 'category-details.pdf';
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
                    if ( $.fn.dataTable.isDataTable( '#category-list1' ) )
                        category_table.destroy();
                    category_table = $('#category-list1').DataTable({
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
                            // { "data": "category_image" },  
                            // { "data": "banner" },  
                            { "data": "icon" },
                            // { "data": "order_number" },
                            // { "data": "featured","orderable": false,"searchable":false}, 
                            { "data": "created_at"},  
                            { "data": "status","orderable": false,"searchable":false},
                            { "data": "action","orderable": false,"searchable":false},
                        ]
                    });
                }
            }
            $(document).on("click",".export-category-data",function(){
                exportType = $(this).attr("data-export-type");
                categoryList(exportType);
            });
        </script>
    </body>
</html>