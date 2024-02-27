<!DOCTYPE html>
<html lang="en">
    <head>
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
                    <div class="content-header">
                        <div class="d-flex align-items-center">
                            <div class="mr-auto">
                                <h3 class="page-title">Product Details</h3>
                            </div>
                        </div>
                    </div>
                    <section class="content">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="card mb-4">
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-hover pay-sel table-bordered">
                                                <tbody>
                                                    <tr>
                                                        <td style="width:35%;">Product Name</td>
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->product_name) ? $product_details[0]->product_name : '-' }}</td> 
                                                    </tr>
                                                    <tr>
                                                        <td>Category</td>
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->category_name) ? $product_details[0]->category_name : '-' }}</td>  
                                                    </tr>
                                                    <tr>
                                                        <td>Sub Category</td>
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->sub_category_name) ? $product_details[0]->sub_category_name : '-' }}</td>
                                                    </tr> 
                                                    @if(!empty($product_details) && !empty($product_details[0]->type_of_product) && $product_details[0]->type_of_product == "single")
                                                        <tr>
                                                            <td>SKU</td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->sku) ? $product_details[0]->sku : '-' }}</td>  
                                                        </tr>
                                                        <!-- <tr>
                                                            <td>Barcode</td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->barcode) ? $product_details[0]->barcode : '-' }}</td>
                                                        </tr> -->
                                                        <!-- <tr>
                                                            <td>Unit</td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->unit) ? $product_details[0]->unit : '-' }}</td>
                                                        </tr> -->
                                                        <tr>
                                                            <td>Price</td>
                                                            <td class="product-price">{{!empty($product_details) && !empty($product_details[0]->price) ? $product_details[0]->price : '-' }}</td>   
                                                        </tr>
                                                        <!-- <tr>
                                                            <td>Compare At Price </td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->compare_price) ? $product_details[0]->compare_price : '-' }}</td>   
                                                        </tr>
                                                        <tr>
                                                            <td>Cost Per Item</td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->cost_per_item) ? $product_details[0]->cost_per_item : '-' }}</td>   
                                                        </tr>
                                                        <tr>
                                                            <td>Profit</td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->profit) ? $product_details[0]->profit : '-' }}</td>   
                                                        </tr>
                                                        <tr>
                                                            <td>Margin</td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->margin) ? $product_details[0]->margin : '-' }}</td>   
                                                        </tr> -->
                                                        <!-- <tr>
                                                            <td>Discount Date Range</td>
                                                            <td>SAR 17.00</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Discount</td>
                                                            <td>1%</td>
                                                        </tr> -->
                                                        <tr>
                                                            <td>Quantity</td>
                                                            <td>{{!empty($product_details) && !empty($product_details[0]->quantity) ? $product_details[0]->quantity : '-' }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td>TAX VAT 
                                                            @if(!empty($product_details) && !empty($product_details[0]->tax_amount))
                                                                <span class="tax-type-symbol">({{ !empty($product_details) && !empty($product_details[0]->tax_type) && $product_details[0]->tax_type == "percent" ? "%" : "flat" }})</span>
                                                            @endif
                                                        </td>
                                                        <input type="hidden" class="tax-type" value="{{!empty($product_details) && !empty($product_details[0]->tax_type) ? $product_details[0]->tax_type : '-' }}">
                                                        <input type="hidden" class="tax-amount" value="{{!empty($product_details) && !empty($product_details[0]->tax_amount) ? $product_details[0]->tax_amount : '-' }}">
                                                        <!-- <td class="product-tax-details">{{!empty($product_details) && !empty($product_details[0]->tax_amount) ? $product_details[0]->tax_amount : '-' }}</td> -->
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->tax_amount) ? $product_details[0]->tax_amount : '-' }}</td>
                                                    </tr> 
                                                    <tr>
                                                        <td>Tags</td>
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->tags) ? $product_details[0]->tags : '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Status</td>
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->status_type) && ($product_details[0]->status_type == "publish") ? "Active" : 'Draft' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Full description</td>
                                                        <input type="hidden" class="product-description" value="{{!empty($product_details) && !empty($product_details[0]->product_description) ? $product_details[0]->product_description : '-' }}">
                                                        <td class="product-full-description"></td>
                                                    </tr>
                                                    <tr>
                                                        <td>Meta Title</td>
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->meta_title) ? $product_details[0]->meta_title : '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Meta Description</td>
                                                        <td>{{!empty($product_details) && !empty($product_details[0]->meta_description) ? $product_details[0]->meta_description : '-' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @if(!empty($product_details) && !empty($product_details[0]->type_of_product) && $product_details[0]->type_of_product == "variant")
                                    <div class="card mb-4">
                                        <div class="card-header">
                                            <h4 class="mb-0">Variants</h4>
                                        </div>
                                        <div class="card-body add-variants">
                                            <div class="table-responsive">
                                                <table class="table table-bordered display variants-table">
                                                    <thead>
                                                        <tr>
                                                            <th scope="col">#</th>
                                                            <th scope="col">Variant</th>
                                                            <th scope="col">Price</th>
                                                            <th scope="col">Onhand</th>
                                                            <!-- <th scope="col">Available</th> -->
                                                            <th scope="col">SKU</th>
                                                            <!-- <th scope="col">Barcode</th> -->
                                                        </tr>
                                                    </thead>
                                                    <tbody class="variants-tbody">
                                                        @if(!empty($variant_option_details) && count($variant_option_details) > 0)
                                                            @foreach($variant_option_details as $key => $variants)
                                                                <tr>
                                                                    <td>{{ $key+1 }}</td>
                                                                    <td>{{ $variants->variants_combination_name }}</td>
                                                                    <td>{{ $variants->variant_price }}</td>
                                                                    <td>{{ $variants->on_hand }}</td>
                                                                    <!-- <td>{{ $variants->available }}</td> -->
                                                                    <td>{{ $variants->sku }}</td>
                                                                    <!-- <td>{{ $variants->barcode }}</td> -->
                                                                </tr>
                                                            @endforeach
                                                        @else
                                                            <tr>
                                                                <td colspan="5" class="text-center">Data not found..!</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div> 
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="card mb-4">
                                    <div class="card-body text-center">
                                        <img src="{{ !empty($product_details) && !empty($product_details[0]->category_image) ? $product_details[0]->category_image : '' }}" class="rounded img-fluid" alt="Item">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
            @include('common.cashier_admin.copyright')
        </div>
        @include('common.cashier_admin.footer')
        <script>
            $(document).ready(function() {
                $('#example1').DataTable();
                product_description = $(".product-description").val();
                $(".product-full-description").html(product_description);
                tax_type = $(".tax-type").val();
                product_price = $(".product-price").text();
                tax_amount = $(".tax-amount").val();
                amount = "";
                if(tax_type == "flat" && tax_amount != "")
                    amount = "SAR "+tax_amount.toFixed(2);
                else if(tax_type == "percent" && tax_amount != "")
                    amount = "SAR "+(parseFloat(product_price) * parseFloat(tax_amount / 100)).toFixed(2);
                $(".product-tax-details").text(amount);
            });
        </script>
    </body>
</html>