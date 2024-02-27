<!DOCTYPE html>
<html lang="en">
    <head>
        <title>{{ __('store-admin.instore_status_title',['company' => Auth::user()->company_name]) }}</title>
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
                            <form  method="POST" action="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.store-order-status.store') }}">
                            @csrf
                                <div class="content-header px-30">
                                    <div class="d-flex row align-items-center ">
                                        <div class="mr-auto">
                                            <h3 class="page-title">{{ __('store-admin.instore_status_card_title') }}</h3>
                                        </div>
                                        <div class="text-right ">
                                            <button class="btn btn-primary save-store-order-status"><span>{{ __('store-admin.save') }}</span> </button>					
                                        </div>
                                    </div>
                                    <hr/>
                                </div>
                                <div class="box-body">
                                    <div class="table-responsive">
                                    <table class="table table-hover nowrap table-bordered display table-striped" id="store-order-status-list">
                                        <input type="hidden" class="list-url" value="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.store-order-status.index')}}">
                                        <thead>
                                            <tr>
                                                <th>#</th>  
                                                <th scope="col">{{ __('store-admin.order_number') }}</th>                    
                                                <th scope="col">{{ __('store-admin.order_status') }}</th>
                                                <th scope="col">{{ __('store-admin.status') }}</th>
                                                <th scope="col" class="text-end"><a href="#" class="btn btn-circle btn-primary btn-xs add-status-row"><i class="fa fa-plus"></i></a></th> 
                                            </tr>
                                        </thead>
                                        <tbody class="status-tbody">
                                            @if(isset($store_order_status) && !empty($store_order_status))
                                                @foreach($store_order_status as $key => $status)
                                                    <tr>
                                                        <input type="hidden" name="status_id[]" class="status-id" value="{{ Crypt::encrypt($status['status_id']) }}">
                                                        <input type="hidden" class="remove-status-url" value="{{ route(config('app.prefix_url').'.'.$store_url.'.'.$prefix_url.'.store-order-status.destroy',Crypt::encrypt($status['status_id']))}}">
                                                        <td>{{ $key + 1 }}</td>
                                                        <td class="input-field-div"><input type="text" data-max="3" data-label = "{{ __('store-admin.order_number') }}" data-type = "show-border-error" id="status-order-number-{{$key + 1}}" name='add_status_order[]' onkeypress='return isNumber(event)' class="form-control required-field form-input-field status-order-number" value="{{ $status['order_number'] }}"><span class='error error-message'></span></td>
                                                        <td class="input-field-div"><input type="text" data-max="100" data-label = "{{ __('store-admin.order_status') }}" data-type = "show-border-error" name='add_status_name[]' class="form-control required-field form-input-field order-status-field" value="{{ $status['status_name'] }}" data-pattern="^[A-Za-z\u0600-\u06FF\s\-']*$" data-error-msg="{{ __('validation.invalid_status_err') }}" onkeypress="return restrictCharacters(event)"><span class='error error-message'></span></td>
                                                        <td><select data-type = "show-border-error" class='form-control required-field form-input-field' name='add_status_condition[]'><option value='1' {{ $status["status"] == "1" ? "selected" : "" }}>Active</option><option value='0' {{ $status["status"] == "0" ? "selected" : "" }}>Inactive</option></select></td>
                                                        <td><a href="#" data-type="saved-data" class="btn btn-circle btn-primary btn-xs remove-status-row"><i class="fa fa-trash"></i></a></td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="empty-row">      
                                                    <td class="text-center" colspan="5">{{ __('datatables.sEmptyTable') }}</td> 
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
            @include('common.cashier_admin.copyright')
        </div>
        @include('common.cashier_admin.footer')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.0/sweetalert.min.js"></script>
        <script src="{{ URL::asset('assets/js/validation.js') }}"></script>
        <script>
            function addStatusRow(_this) {
                status_row_count = parseInt(_this.closest("table").find("tbody").find("tr:not(.empty-row)").length) + 1; 
                status_row = `<tr><input type="hidden" name="status_id[]" class="status-id" value=""><td>${status_row_count}</td><td class="input-field-div"><input type="text" data-max="3" data-type="show-border-error" data-label="${translations.order_number}" name="add_status_order[]" data-pattern="^[0-9]*$" data-error-msg="${langTranslations.invalid_numeric_err}" onkeypress="return restrictCharacters(event)" id="status-order-number-${status_row_count}" class="form-control required-field form-input-field status-order-number"><span class="error error-message"></span></td><td class="input-field-div"><input type="text" data-max="100" data-type="show-border-error" name="add_status_name[]" class="form-control required-field form-input-field order-status-field" data-label = "${translations.order_status}" data-pattern="^[A-Za-z\\u0600-\\u06FF\\s\\-']*$" data-error-msg="${langTranslations.invalid_status_err}" onkeypress="return restrictCharacters(event)"><span class="error error-message"></span></td><td><select class="form-control required-field form-input-field" data-type="show-border-error" name="add_status_condition[]"><option value="1">Active</option><option value="0">Inactive</option></select></td><td><a href="#" class="btn btn-circle btn-primary btn-xs remove-status-row"><i class="fa fa-trash"></i></a></td></tr>`;
                _this.closest("table").find("tbody").find(".empty-row").remove();
                _this.closest("table").find("tbody").append(status_row);
            }
            $(document).ready(function() {
                status_count = $("#store-order-status-list").find(".status-tbody").find(".empty-row").length;
                if(status_count > 0)
                    addStatusRow($(".add-status-row"));
            }); 
            $(document).on("click",".remove-status-row",function(event) {
                event.preventDefault();
                __this = $(this);
                _this = $(this).closest("tbody");
                status_row_length = _this.find("tr").length;
                delete_type = $(this).attr("data-type");
                if(delete_type == "saved-data") {
                    remove_status_url = $(this).closest("tr").find(".remove-status-url").val();
                    remove_status_id = $(this).closest("tr").find(".status-id").val();
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
                            $(".page-loader").show();
                            $.ajax({
                                url: remove_status_url,
                                type: 'DELETE',
                                data: {_token: CSRF_TOKEN,remove_status_id: remove_status_id},
                                success: function(response){
                                    __this.closest("tr").remove();
                                    toastr.options =
                                    {
                                        "closeButton" : true,
                                        "progressBar" : true
                                    }
                                    toastr.success(response.message);  
                                    if(status_row_length == 1) {
                                        _this.append('<tr class="empty-row"><td class="text-center" colspan="5">'+datatableTranslations.sEmptyTable+'</td></tr>');
                                        _this.closest("form").find(".save-store-order-status").addClass("dnone");
                                    } else {
                                        i = 1;
                                        _this.find("tr").each(function() {
                                            $(this).find("td:eq(0)").text(i);
                                            $(this).find("td:eq(1)").attr("id","status-order-number-"+i);
                                            i++;
                                        });
                                    }
                                    $(".page-loader").hide();
                                }
                            });
                        }
                    });
                } else {
                    __this.closest("tr").remove();
                    if(status_row_length == 1) {
                        _this.append('<tr class="empty-row"><td class="text-center" colspan="5">'+datatableTranslations.sEmptyTable+'</td></tr>');
                        _this.closest("form").find(".save-store-order-status").addClass("dnone");
                    } else {
                        i = 1;
                        _this.find("tr").each(function() {
                            $(this).find("td:eq(0)").text(i);
                            $(this).find("td:eq(1)").attr("id","status-order-number-"+i);
                            i++;
                        });
                    }
                }
            });
            $(document).on("click",".add-status-row",function(event) {
                event.preventDefault();
                $(this).closest("form").find(".save-store-order-status").removeClass("dnone");
                addStatusRow($(this));
            });
            uniqueOrderNoError = orderStatusError = 0;
            $(document).on("click",".save-store-order-status",function() {
                check_fields = validateFields($(this));
                uniqueOrderNoError = checkUniqueField('.status-order-number', []);
                orderStatusError = checkUniqueField('.order-status-field', []);
                if((check_fields > 0) || (uniqueOrderNoError  > 0) || (orderStatusError  > 0))
                    return false;
                else 
                    return true;
            });
            $(document).on("change keyup",".status-order-number",function() {
                checkUniqueField('.status-order-number', []);
            });
            $(document).on("change keyup",".order-status-field",function() {
                checkUniqueField('.order-status-field', []);
            });
        </script>
    </body>
</html>