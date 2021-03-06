@extends('layouts.adminlte')
@section('title', 'Dashboard')
@section('content')
    @if(isset($dashboard))
        @include('dashboard.templates.' . $dashboard->template->dashboard_template_name)
    @endif
@stop

@section('links')
    <link rel="stylesheet" href="{{elixir('css/tour.css')}}">
@stop

@section('head_scripts')
    {{--TOUR--}}
    @if(\Request::has('tour') && \Request::get('tour') == 'product')
        <script type="text/javascript" src="{{elixir('js/product-tour.js')}}"></script>
    @else
        <script type="text/javascript" src="{{elixir('js/dashboard-tour.js')}}"></script>
    @endif
@stop

@section('scripts')
    <script type="text/javascript">
        function applyFilters() {
            showLoading();
            $.ajax({
                "url": "{{route("dashboard.filter.edit", $dashboard->getKey())}}",
                "method": "get",
                "success": function (html) {
                    hideLoading();
                    var $modal = $(html);
                    $modal.modal();
                    $modal.on("shown.bs.modal", function () {
                        if ($.isFunction(modalReady)) {
                            modalReady({
                                "callback": function (response) {
                                    window.location.reload();
                                }
                            })
                        }
                    });
                    $modal.on("hidden.bs.modal", function () {
                        $("#modal-dashboard-filter-update").remove();
                    });
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            })
        }

        function addWidget() {
            showLoading();
            $.ajax({
                "url": "{{route("dashboard.widget.create")}}",
                "method": "get",
                "data": {
                    "dashboard_id": "{{$dashboard->getKey()}}"
                },
                "success": function (html) {
                    hideLoading();
                    var $modal = $(html);
                    $modal.modal();
                    $modal.on("shown.bs.modal", function () {
                        if ($.isFunction(modalReady)) {
                            modalReady({
                                "callback": function (response) {
//                                    window.location.reload();
                                    if (response.status == true && typeof response.dashboardWidget != 'undefined') {
                                        getWidget(response.dashboardWidget.urls['show'], function ($newWidget) {
                                            $(".widgets-container .widget-placeholder-container").before(
                                                    $newWidget
                                            );
                                            $newWidget.slideDown();
                                            $("[data-toggle=popover]").popover();
                                        });
                                    }
                                    /* TODO append new widget */
                                }
                            })
                        }
                    });
                    $modal.on("hidden.bs.modal", function () {
                        $("#modal-dashboard-widget-store").remove();
                    });
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            })
        }

        function getWidget(url, callback) {
            showLoading();
            $.ajax({
                "url": url,
                "method": "get",
                "success": function (html) {
                    hideLoading();
                    var $newWidget = $("<div>").addClass("col-md-3 widget-container").css("display", "none").attr("data-widget-url", url).html(html);
                    if ($.isFunction(callback)) {
                        callback($newWidget);
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            });
        }

        function editWidget(el) {
            showLoading();
            $.ajax({
                "url": $(el).attr("data-url"),
                "method": "get",
                "success": function (html) {
                    hideLoading();
                    var $modal = $(html);
                    $modal.modal();
                    $modal.on("shown.bs.modal", function () {
                        if ($.isFunction(modalReady)) {
                            modalReady({
                                "callback": function (response) {
//                                    window.location.reload();
                                    if (response.status == true && typeof response.dashboardWidget != 'undefined') {
                                        getWidget(response.dashboardWidget.urls['show'], function ($savedWidget) {
                                            $(el).closest(".widget-container").slideUp(function () {
                                                $(this).replaceWith($savedWidget)
                                                $savedWidget.slideDown();
                                            });
                                            $("[data-toggle=popover]").popover();
                                        });
                                        /*TODO update existing widget*/
                                    }
                                }
                            })
                        }
                    });
                    $modal.on("hidden.bs.modal", function () {
                        $("#modal-dashboard-widget-update").remove();
                    });
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    describeServerRespondedError(xhr.status);
                }
            })
        }

        function deleteWidget(el) {
            deletePopup("Delete Chart", "Are you sure you want to delete this Chart?",
                    "By deleting this Chart, you will lose the following:",
                    [
                        "All Category or Product data associated with this Chart displayed on the Dashboard"
                    ],
                    {
                        "affirmative": {
                            "text": "DELETE",
                            "class": "btn-danger btn-flat",
                            "dismiss": true,
                            "callback": function () {
                                showLoading();
                                $.ajax({
                                    "url": $(el).attr("data-url"),
                                    "method": "delete",
                                    "dataType": "json",
                                    "success": function (response) {
                                        hideLoading();
                                        if (response.status == true) {
                                            $(el).closest(".widget-container").slideUp(function () {
                                                $(this).remove();
                                            })
                                        } else {
                                            if (typeof response.errors != 'undefined') {
                                                var errorMessage = "";
                                                $.each(response.errors, function (index, error) {
                                                    errorMessage += error + " ";
                                                });
                                                alertP("Oops! Something went wrong.", errorMessage);
                                            } else {
                                                alertP("Oops! Something went wrong.", "Unable to delete chart, please try again later.");
                                            }
                                        }
                                    },
                                    "error": function (xhr, status, error) {
                                        hideLoading();
                                        describeServerRespondedError(xhr.status);
                                    }
                                })
                            }
                        },
                        "negative": {
                            "text": "CANCEL",
                            "class": "btn-default btn-flat",
                            "dismiss": true
                        }
                    });
        }

        gaDisplayDashboard();
    </script>
@stop