@extends('layouts.adminlte')
@section('title', 'Products')

@section('notification_banner')
    @if(auth()->user()->preference("HIDE_PRODUCT_BANNER_MESSAGE") != "1")
        <div class="callout callout-default" style="margin-bottom: 0!important;">
            <button type="button" class="close" onclick="hideProductBannerMessage(this); return false;">×</button>
            <h4>Track how your competitors are pricing identical and similar products.</h4>
            Configure your categories and products by adding URLs below. Make sure to set up the
            alerts so you and more team members can receive timely notifications about price changes.
        </div>
    @endif
@stop

@section('header_title', "Products")


@section('links')
    <link rel="stylesheet" href="{{elixir('css/product-tour.css')}}">
@stop


@section('breadcrumbs')
    <input type="text" class="form-control general-search-input" placeholder="Search">
    <button class="btn btn-default general-search-button">
        <i class="fa fa-search"></i>
    </button>
@stop

@section('content')
    <div class="row">
        <div class="col-sm-12">
            <p class="text-muted font-size-17">
                In this area of SpotLite you can set-up all the prices that you want to track. Simply add a category,
                then a product name. Once you have done this, simply copy and paste the product pages of the brands
                prices you want to track. To do this go to each of the brand or competitors site, navigate to the
                product details page or any place that holds the pricing information. Copy and paste the URL into the
                Add URL box shown below.
            </p>
        </div>
    </div>

    <hr class="content-divider-white">

    {{--@include('products.partials.banner_stats')--}}
    <div class="row">
        <div class="col-md-12">
            <div class="box box-solid">
                <div class="box-body product-list-page-content">

                    <div class="row m-b-10 text-muted font-weight-bold">
                        <div class="col-sm-12">
                            @if(!auth()->user()->isStaff && !is_null(auth()->user()->subscription))
                                Credit: &nbsp;&nbsp;&nbsp;&nbsp;

                                {{--TODO update color based on the ratio--}}
                                <div class="progress vertical-align-middle"
                                     style="width: 300px;display: inline-block;margin-bottom: 0;background-color:#dedede;border-radius: 10px; height:15px;">
                                    <div class="progress-bar progress-bar-success progress-bar-striped"
                                         id="prog-product-usage"
                                         role="progressbar"
                                         aria-valuenow="{{auth()->user()->products()->count() / auth()->user()->subscriptionCriteria()->product * 100}}"
                                         aria-valuemin="0" aria-valuemax="100"
                                         style="width: {{auth()->user()->products()->count() / auth()->user()->subscriptionCriteria()->product * 100}}%">
                                    </div>
                                </div>
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <span id="lbl-product-usage">{{auth()->user()->products()->count()}}</span>
                                &nbsp;/&nbsp;
                                <span id="lbl-product-total">{{auth()->user()->subscriptionCriteria()->product == 0 ? "unlimited" : auth()->user()->subscriptionCriteria()->product}}</span>
                                &nbsp;
                                products
                            @endif
                        </div>
                    </div>

                    <div class="row m-b-10">
                        <div class="col-sm-12">
                            {{--<a href="#" class="btn btn-primary btn-xs btn-add-category btn-flat"--}}
                            {{--onclick="appendCreateCategoryBlock();">--}}
                            {{--<i class="fa fa-plus"></i> Add Category--}}
                            {{--</a>--}}
                            <div class="add-item-block add-category-container"
                                 onclick="appendCreateCategoryBlock(this); event.stopPropagation(); return false;">
                                <div class="add-item-label">
                                    <i class="fa fa-plus"></i>&nbsp;&nbsp;&nbsp;
                                    <span class="add-item-text">ADD CATEGORY</span>
                                </div>
                                <div class="add-item-controls">
                                    <div class="row">
                                        <div class="col-lg-8 col-md-7 col-sm-5 col-xs-4">
                                            <form action="{{route('category.store')}}" method="post"
                                                  class="frm-store-category"
                                                  onsubmit="btnAddCategoryOnClick(this); return false;">
                                                <input type="text" placeholder="Category Name" id="txt-category-name"
                                                       class="form-control txt-item" name="category_name">
                                            </form>
                                        </div>
                                        <div class="col-lg-4 col-md-5 col-sm-7 col-xs-8 text-right">
                                            <button class="btn btn-primary"
                                                    onclick="btnAddCategoryOnClick(this); event.stopPropagation(); event.preventDefault();">
                                                ADD CATEGORY
                                            </button>
                                            &nbsp;&nbsp;
                                            <button class="btn btn-default" id="btn-cancel-add-category"
                                                    onclick="cancelAddCategory(this); event.stopPropagation(); event.preventDefault();">
                                                CANCEL
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 list-container">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script type="text/javascript" src="{{elixir('js/product.js')}}"></script>

    <script type="text/javascript">
        var start = 0;
        var length = 5;
        var initLength = 10;
        var theEnd = false;
        /**
         * drag and drop source
         */
        var drag_source = null;
        var draggedType = null;
        var categoryDrake = null;

        var generalSearchPromise = null;

        $(function () {

            /**
             * category drag and drop
             */
            categoryDrake = dragula([$(".list-container").get(0)], {
                moves: function (el, container, handle) {
                    if ($(".general-search-input").val() != "") {
                        return false;
                    }
                    return $(handle).hasClass("btn-category-dragger") || $(handle).closest(".btn-category-dragger").length > 0;
                }
            }).on('drop', function (el, target, source, sibling) {
                updateCategoryOrder();
            });

            /** enable scrolling when dragging */
            autoScroll([window], {
                margin: 20,
                pixels: 20,
                scrollWhenOutside: true,
                autoScroll: function () {
                    //Only scroll when the pointer is down, and there is a child being dragged.
                    return this.down && categoryDrake.dragging;
                }
            });

            $(".general-search-input").on("input", function () {
                if (generalSearchPromise != null) {
                    clearTimeout(generalSearchPromise);
                }
                generalSearchPromise = setTimeout(function () {
                    $(".general-search-input").blur();
                    showLoading();
                    resetFilters();
                    loadCategories(start, initLength, function (response) {
                        $(".list-container").fadeOut(function () {
                            $(".list-container").html(response.categoriesHTML);
                            $(".list-container").fadeIn();
                        });
                        hideLoading();
                        generalSearchPromise = null;
                    }, function (xhr, status, error) {
                        hideLoading();
                        generalSearchPromise = null;
                    });
                }, 500);
            });

            loadCategories(start, initLength, function (response) {
                $(".list-container").append(response.categoriesHTML);
            }, function (xhr, status, error) {

            });
            $(window).scroll(function () {
                if (Math.round($(window).scrollTop() + $(window).height()) == $(document).height()) {
                    if (!theEnd) {
                        loadCategories(start, initLength, function (response) {
                            $(".list-container").append(response.categoriesHTML);
                        }, function (xhr, status, error) {

                        });
                    }
                }
            });
        });

        function appendCreateCategoryBlock(el) {
            $(el).find(".add-item-label").slideUp();
            $(el).find(".add-item-controls").slideDown();
            $("#txt-category-name").focus();
        }

        function cancelAddCategory(el) {
            $(el).closest(".add-item-block").find(".add-item-label").slideDown();
            $(el).closest(".add-item-block").find(".add-item-controls").slideUp();
            $(el).closest(".add-item-block").find(".add-item-controls input").val("");
        }

        function loadCategories(tStart, tLength, successCallback, failCallback) {
            showLoading();
            $.ajax({
                "url": "{{route("product.index")}}",
                "method": "get",
                "data": {
                    "start": tStart,
                    "length": tLength,
                    "keyword": $(".general-search-input").val()
                },
                "dataType": "json",
                "success": function (response) {
                    hideLoading();
                    if (response.status == true) {
                        start += response.recordFiltered;
                        theEnd = response.recordFiltered < tLength;
                        if ($.isFunction(successCallback)) {
                            successCallback(response);
                        }
                    } else {
                        alertP("Error", "unable to load categories, please try again later.");
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            })
        }


        function btnAddCategoryOnClick() {
            showLoading();
            $.ajax({
                "url": "{{route('category.store')}}",
                "method": "post",
                "data": {
                    "category_name": $("#txt-category-name").val()
                },
                "dataType": "json",
                "success": function (response) {
                    hideLoading();
                    if (response.status == true) {
                        cancelAddCategory($("#btn-cancel-add-category").get());
                        gaAddCategory();
                        if (response.category != null) {
                            showLoading();
                            loadSingleCategory(response.category.urls.show, function (html) {
                                hideLoading();
                                $(".list-container").prepend(html);
                                updateCategoryOrder();
                                updateUserProductCredit();
                            });
                        } else {
                            alertP("Create Category", "Category has been created. But encountered error while page being lodaed.", function () {
                                window.location.reload();
                            });
                        }
                    } else {
                        var errorMsg = "";
                        if (response.errors != null) {
                            $.each(response.errors, function (index, error) {
                                errorMsg += error + " ";
                            })
                        }
                        alertP("Error", errorMsg);
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            })
        }

        function loadSingleCategory(url, callback) {
            $.ajax({
                "url": url,
                "method": "get",
                "success": function (response) {
                    hideLoading();
                    if ($.isFunction(callback)) {
                        callback(response);
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            });
        }


        function resetFilters() {
            start = 0;
            length = 5;
        }

        function toggleCollapseCategories() {
            if ($(".collapsible-category-div").attr("aria-expanded") == "true") {
                $(".collapsible-category-div").attr("aria-expanded", false).removeClass("in")
            } else {
                $(".collapsible-category-div").attr("aria-expanded", true).addClass("in")
            }
        }

        function toggleCollapseProducts() {
            if ($(".collapsible-product-div").attr("aria-expanded") == "true") {
                $(".collapsible-product-div").attr("aria-expanded", false).removeClass("in")
            } else {
                $(".collapsible-product-div").attr("aria-expanded", true).addClass("in")
            }
        }

        function assignCategoryOrderNumber() {
            $(".category-wrapper").each(function (index) {
                $(this).attr("data-order", index + 1);
            });
        }

        function updateCategoryOrder() {
            assignCategoryOrderNumber();
            var orderList = [];
            $(".category-wrapper").filter(function () {
                return !$(this).hasClass("gu-mirror");
            }).each(function () {
                if ($(this).attr("data-category-id")) {
                    var categoryId = $(this).attr("data-category-id");
                    var categoryOrder = parseInt($(this).attr("data-order"));
                    orderList.push({
                        "category_id": categoryId,
                        "category_order": categoryOrder
                    });
                }
            });

            $.ajax({
                "url": "{{route('category.order')}}",
                "method": "put",
                "data": {
                    "order": orderList
                },
                "dataType": "json",
                "success": function (response) {
                    if (response.status == false) {
                        alertP("Error", "Unable to update category order, please try again later.");
                    } else {
                        gaMoveCategory();
                    }
                },
                "error": function (xhr, status, error) {
                    describeServerRespondedError(xhr.status);
                }
            })
        }

        function hideProductBannerMessage(el) {
            $(el).closest(".callout").slideUp(function () {
                $(this).remove();
            });
            $.ajax({
                "url": "{{route("preference.update", ["element" => "HIDE_PRODUCT_BANNER_MESSAGE", "value" => "1"])}}",
                "method": "put",
                "dataType": "json",
                "success": function (response) {
                },
                "error": function (xhr, status, error) {
                }
            })
        }

        function updateUserProductCredit() {
            $.ajax({
                "url": "/product/product/usage",
                "method": "get",
                "dataType": "json",
                "success": function (response) {
                    if (response.status == true) {
                        var total = response.total;
                        var usage = response.usage;

                        $("#prog-product-usage").attr({
                            "aria-valuenow": usage / total * 100
                        }).css("width", (usage / total * 100) + "%");

                        $("#lbl-product-usage").text(usage);
                        $("#lbl-product-total").text(total);
                        updateUserProductUsageBarColor();
                        updateAddProductPanelStatus(usage, total);
                    }
                },
                "error": function (xhr, status, error) {
                    describeServerRespondedError(xhr.status);
                }
            })
        }

        function updateUserProductUsageBarColor() {
            var $progressBar = $("#prog-product-usage")
            var currentValue = $progressBar.attr("aria-valuenow");
            $progressBar.removeClass("progress-bar-warning progress-bar-success, progress-bar-danger");
            if (currentValue < 80) {
                $progressBar.addClass("progress-bar-success");
            } else if (currentValue < 90) {
                $progressBar.addClass("progress-bar-warning");
            } else {
                $progressBar.addClass("progress-bar-danger");
            }
        }

        function updateAddProductPanelStatus(usage, total) {
            if (usage >= total) {
                /*TODO disable add product*/
                $(".add-product-container").attr('onclick', 'appendUpgradeForCreateProductBlock(this); event.stopPropagation(); return false;');
            } else {
                /*TODO enable add product*/
                $(".add-product-container").attr('onclick', 'appendCreateProductBlock(this); event.stopPropagation(); return false;');
            }
        }
    </script>

    {{--TOUR--}}
    <script type="text/javascript" src="{{elixir('js/product-tour.js')}}"></script>
    <script type="text/javascript">
        var tour = new Tour({
            steps: [
                {
                    element: ".btn-add-category",
                    content: "Add a category of products you wish to track."
                },
                {
                    element: ".btn-add-product:first",
                    content: "Add products within each Category."
                },
                {
                    element: ".btn-add-site:first",
                    content: "Add webpages from your competitors' Sites for each Product."
                },
                {
                    element: ".action-cell:first",
                    content: "You can edit or delete a Category, Product or Site.",
                    placement: "left"
                },
                {
                    element: ".btn-report:first",
                    content: "You can schedule a report for Categories and Products.",
                    placement: "left"
                },
                {
                    element: ".btn-alert:first",
                    content: "You can set an Alert for Products and Sites.",
                    placement: "left"
                },
                {
                    element: ".btn-chart:first",
                    content: "You can generate a chart for Categories, Products and Sites and add them to your Dashboard.",
                    placement: "left"
                }
            ],
            backdrop: true,
            storage: false,
            backdropPadding: 20
        });
        tour.init();

        function startTour() {
            tour.restart();
        }

        function setTourVisited() {
            $.ajax({
                "url": "preference/PRODUCT_TOUR_VISITED/1",
                "method": "put",
                "dataType": "json",
                "success": function (response) {

                },
                "error": function (xhr, status, error) {

                }
            })
        }

        function tourNotYetVisit() {
            return user.preferences.PRODUCT_TOUR_VISITED != 1
        }
    </script>
@stop