<div class="row category-wrapper" data-category-id="{{$category->getKey()}}" draggable="true"
     data-report-task-link="{{$category->urls['report_task']}}"
     data-get-site-usage-link="{{$category->urls['site_usage']}}"
>
    <div class="col-sm-12">
        <table class="table table-condensed tbl-category">
            <thead>
            <tr>
                <th class="shrink category-th">
                    <a class="btn-collapse btn-category-dragger"><i class="fa fa-tag "></i></a>
                </th>
                <th class="category-th">
                    <a class="text-muted category-name-link" href="#"
                       onclick="return false;">{{$category->category_name}}</a>

                    @if(!auth()->user()->isPastDue)
                        {!! Form::model($category, array('route' => array('category.update', $category->getKey()), 'method'=>'delete', 'class'=>'frm-edit-category', 'onsubmit' => 'submitEditCategoryName(this); return false;', 'style' => 'display: none;')) !!}
                        <div class="input-group sl-input-group">
                            <input type="text" name="category_name" placeholder="Category Name" autocomplete="off"
                                   class="form-control sl-form-control input-lg category-name"
                                   onkeyup="cancelEditCategoryName(this, event)" onblur="txtCategoryOnBlur(this);"
                                   value="{{$category->category_name}}">
                            <span class="input-group-btn">
                                <button type="submit" class="btn btn-default btn-flat btn-lg">
                                    <i class="fa fa-check"></i>
                                </button>
                            </span>
                        </div>
                        {!! Form::close() !!}
                        {{--<span class="btn-edit btn-edit-category" onclick="toggleEditCategoryName(this)">Edit &nbsp; <i--}}
                        {{--class="fa fa-pencil-square-o"></i></span>--}}
                    @endif
                </th>

                <th class="text-right action-cell category-th">
                    @if(!auth()->user()->isPastDue)
                        {{--<a href="#" class="btn-action btn-chart" data-toggle="tooltip" title="chart"--}}
                        {{--onclick="showCategoryChart('{{$category->urls['chart']}}'); return false;">--}}
                        {{--<i class="fa fa-line-chart"></i>--}}
                        {{--</a>--}}
                        {{--<a href="#" class="btn-action btn-report" onclick="showCategoryReportTaskForm(this); return false;"--}}
                        {{--data-toggle="tooltip"--}}
                        {{--title="report">--}}
                        {{--<i class="fa {{!is_null($category->reportTask) ? "fa-envelope ico-report-enabled" : "fa-envelope-o"}}"></i>--}}
                        {{--</a>--}}
                        <a href="#" class="btn-action btn-edit-category" onclick="toggleEditCategoryName(this)">
                            <i class="glyphicon glyphicon-pencil"></i>
                        </a>
                        {!! Form::model($category, array('route' => array('category.destroy', $category->getKey()), 'method'=>'delete', 'class'=>'frm-delete-category', 'onsubmit' => 'return false;')) !!}
                        <a href="#" data-name="{{$category->category_name}}" class="btn-action btn-delete-category"
                           onclick="btnDeleteCategoryOnClick(this); return false;" data-toggle="tooltip"
                           title="delete">
                            <i class="glyphicon glyphicon-trash"></i>
                        </a>
                        {!! Form::close() !!}
                    @endif
                </th>
                <th class="text-center vertical-align-middle" style="background-color: #d3d3d3;" width="70">
                    <a class="text-muted btn-collapse collapsed" style="font-size: 35px;" href="#category-{{$category->getKey()}}"
                       role="button"
                       data-toggle="collapse" data-parent="#accordion" aria-expanded="false"
                       aria-controls="category-{{$category->getKey()}}">
                        <i class="fa fa-angle-up"></i>
                    </a>
                </th>
            </tr>
            <tr>
                <th></th>
                <td colspan="3" class="category-th">
                    <div class="text-light">
                        Created
                        @if(!is_null($category->created_at))
                            on {{date(auth()->user()->preference('DATE_FORMAT'), strtotime($category->created_at))}}
                        @endif
                        <strong class="text-muted"><i>by {{$category->user->first_name}} {{$category->user->last_name}}</i></strong>
                    </div>
                    <div class="text-light">
                        Product URLs Tracked:
                        <strong><span class="lbl-site-usage text-muted">{{$category->sites()->count()}}</span></strong>
                    </div>
                </td>
            </tr>
            @if(!auth()->user()->isPastDue)
                <tr>
                    <th></th>
                    <th colspan="3" class="category-th action-cell add-item-cell">
                        <div class="add-item-block add-product-container"
                             onclick="appendCreateProductBlock(this); event.stopPropagation(); return false;">
                            <div class="add-item-label">
                                <i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;
                                <span class="add-item-text">ADD PRODUCT</span>
                            </div>
                            <div class="add-item-controls">
                                <form action="{{route('product.store')}}" method="post"
                                      class="frm-store-product" style="display: inline-block;"
                                      onsubmit="btnAddProductOnClick(this); return false;">
                                    <input type="text" name="product_name" autocomplete="off"
                                           class="form-control txt-item txt-product-name">
                                </form>
                                <div style="display:inline-block">
                                    <button class="btn btn-primary btn-flat"
                                            onclick="btnAddProductOnClick(this); event.stopPropagation(); event.preventDefault();">
                                        ADD PRODUCT
                                    </button>
                                    &nbsp;&nbsp;
                                    <button class="btn btn-default btn-flat btn-cancel-add-product"
                                            onclick="cancelAddProduct(this); event.stopPropagation(); event.preventDefault();">
                                        CANCEL
                                    </button>
                                </div>
                            </div>
                            @if(auth()->user()->needSubscription && !is_null(auth()->user()->subscription))
                                <div class="upgrade-for-add-item-controls" style="display: none;">
                                    <span class="add-item-text">
                                        You have reached the product limit of
                                        {{auth()->user()->apiSubscription->product()->name}} plan.
                                        Please
                                        <a href="{{route('subscription.edit', auth()->user()->subscription->getKey())}}"
                                           onclick="event.stopPropagation();">
                                            upgrade your subscription
                                        </a> to add more products.
                                    </span>
                                </div>
                            @endif
                        </div>
                    </th>
                </tr>
            @endif
            </thead>
            <tbody>
            <tr>
                <td></td>
                <td colspan="3" class="table-container">
                    <div id="category-{{$category->getKey()}}" class="collapse collapsible-category-div"
                         data-products-url="{{$category->urls['show_products']}}" data-start="0" data-length="10"
                         data-end="false" aria-expanded="false">
                        <div class="row">
                            <div class="col-sm-12 text-center">
                                <div class="dotdotdot loading-products" style="margin: 20px auto; display: none;">

                                </div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <script type="text/javascript">

        var productDrake{{$category->getKey()}} = null;

        $(function () {

            productDrake{{$category->getKey()}} = dragula([$("#category-{{$category->getKey()}}").get(0)], {
//                moves: function (el, container, handle) {
//                    return $(handle).hasClass("btn-product-dragger") || $(handle).closest(".btn-product-dragger").length > 0;
//                }
                invalid: function (el, handle) {
                    return !$(handle).hasClass("btn-product-dragger") && $(handle).closest(".btn-product-dragger").length == 0;
                }
            }).on('drop', function (el, target, source, sibling) {
                updateProductOrder({{$category->getKey()}});
            });


            /** enable scrolling when dragging*/
            autoScroll([window], {
                margin: 20,
                pixels: 20,
                scrollWhenOutside: true,
                autoScroll: function () {
                    //Only scroll when the pointer is down, and there is a child being dragged.
                    return this.down && productDrake{{$category->getKey()}}.dragging;
                }
            });


            loadProducts('{{$category->getKey()}}', function (response) {
                $("#category-{{$category->getKey()}}").prepend(response.html);
            });
        });

        function loadProducts(category_id, successCallback, failCallback) {
            showLoadingProducts(category_id);
            var $categoryWrapper = $("#category-" + category_id);
            $.ajax({
                "url": $categoryWrapper.attr("data-products-url"),
                "data": {
                    "start": $categoryWrapper.attr("data-start"),
                    "length": $categoryWrapper.attr("data-length"),
                    "keyword": $(".general-search-input").val()
                },
                "dataType": "json",
                "success": function (response) {
                    hideLoadingProducts(category_id);
                    if (response.status == true) {
                        $categoryWrapper.attr("data-end", response.recordFiltered < $categoryWrapper.attr("data-length"));
                        $categoryWrapper.attr("data-start", parseInt($categoryWrapper.attr("data-start")) + response.recordFiltered);
                        if ($.isFunction(successCallback)) {
                            successCallback(response);
                        }
                    } else {
                        if (typeof response.errors != 'undefined') {
                            var errorMessage = "";
                            $.each(response.errors, function (index, error) {
                                errorMessage += error + " ";
                            });
                            alertP("Oops! Something went wrong.", errorMessage);
                        } else {
                            alertP("Oops! Something went wrong.", "unable to load products, please try again later.");
                        }
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoadingProducts(category_id);
                    describeServerRespondedError(xhr.status);
                    if ($.isFunction(failCallback)) {
                        failCallback(xhr, status, error);
                    }
                }
            })
        }

        function showLoadingProducts(category_id) {
            $("#category-" + category_id + " .loading-products").show();
        }

        function hideLoadingProducts(category_id) {
            $("#category-" + category_id + " .loading-products").hide();
        }

    </script>
</div>