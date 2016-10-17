<div class="modal fade" tabindex="-1" role="dialog" id="modal-product-chart">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-body" style="background-color: #f5f5f5;">
                <div class="row">
                    <div class="col-lg-5 col-md-6 col-sm-12">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="box box-solid">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Chart Characteristics</h3>
                                    </div>
                                    <div class="box-body">
                                        <div class="row m-b-10">
                                            <div class="col-sm-12">
                                                <form action="" class="form-horizontal" id="frm-product-chart-characteristics">
                                                    <div class="form-group required">
                                                        <label class="col-sm-4 control-label">Timespan</label>
                                                        <div class="col-sm-8">
                                                            <select id="sel-timespan" name="timespan" class="form-control"
                                                                    onchange="timespanOnChange(this)">
                                                                <option value="this_week">This week</option>
                                                                <option value="last_week">Last week</option>
                                                                <option value="last_7_days">Last 7 days</option>
                                                                <option value="this_month">This month</option>
                                                                <option value="last_month">Last month</option>
                                                                <option value="last_30_days">Last 30 days</option>
                                                                <option value="this_quarter">This quarter</option>
                                                                <option value="last_quarter">Last quarter</option>
                                                                <option value="last_90_days">Last 90 days</option>
                                                                <option value="custom">Custom</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group show-when-custom" style="display: none;">
                                                        <label class="col-sm-4 control-label">Date range:</label>

                                                        <div class="col-sm-8">
                                                            <div class="input-group">
                                                                <div class="input-group-addon">
                                                                    <i class="fa fa-calendar"></i>
                                                                </div>
                                                                <input type="text" class="form-control pull-right"
                                                                       name="date_range"
                                                                       id="txt-date-range" readonly="readonly">
                                                                <input type="hidden" name="start_date"
                                                                       id="txt-product-chart-start-date">
                                                                <input type="hidden" name="end_date"
                                                                       id="txt-product-chart-end-date">
                                                            </div>
                                                        </div>
                                                        <!-- /.input group -->
                                                    </div>
                                                    <div class="form-group required">
                                                        <label class="col-sm-4 control-label">Period Resolution</label>
                                                        <div class="col-sm-8">
                                                            <select id="sel-period-resolution" name="resolution"
                                                                    class="form-control"
                                                                    onchange="periodResolutionOnChange(this)">
                                                                <option value="daily">Daily</option>
                                                                <option value="weekly">Weekly</option>
                                                                <option value="monthly">Monthly</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <button class="btn btn-primary" onclick="loadProductChartData()">Generate
                                                    Chart
                                                </button>
                                                <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">

                                @if(auth()->user()->dashboards->count() > 0)
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="box box-solid">
                                                <div class="box-header with-border">
                                                    <h3 class="box-title">Add to Dashboard</h3>
                                                </div>
                                                <div class="box-body">
                                                    <div class="row m-b-10">
                                                        <div class="col-sm-12">
                                                            <ul class="text-danger errors-container">
                                                            </ul>
                                                            {!! Form::open(array('route' => array('dashboard.widget.store'), 'method'=>'post', "onsubmit"=>"return false", "class" => "form-horizontal sl-form-horizontal", "id"=>"frm-dashboard-widget-store")) !!}
                                                            <input type="hidden" name="dashboard_widget_type_id" value="1">
                                                            <div class="form-group required">
                                                                <label class="col-sm-4 control-label">Dashboard</label>
                                                                <div class="col-sm-8">
                                                                    <select id="sel-dashboard-id" name="dashboard_id"
                                                                            class="form-control">
                                                                        @foreach(auth()->user()->dashboards as $dashboard)
                                                                            <option value="{{$dashboard->getKey()}}">{{$dashboard->dashboard_name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="form-group required">
                                                                <label class="col-sm-4 control-label">Chart Name</label>
                                                                <div class="col-sm-8">
                                                                    <input type="text" name="dashboard_widget_name"
                                                                           id="txt-dashboard-widget-name" class="form-control">
                                                                </div>
                                                            </div>

                                                            {!! Form::close() !!}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sm-12">
                                                            <button class="btn btn-primary" id="btn-add-chart">
                                                                Add Chart
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-7 col-md-6 col-sm-12">
                        <div id="chart-container">

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        var productChart = null;

        function modalReady() {
            $("#btn-add-chart").on("click", function () {
                submitAddContent();
            })
            $("#txt-date-range").daterangepicker({
                "maxDate": moment()
            }).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $("#txt-product-chart-start-date").val(picker.startDate.format('X'));
                $("#txt-product-chart-end-date").val(picker.endDate.format('X'));
            });

            productChart = new Highcharts.Chart({
                credits: {
                    enabled: false
                },
                chart: {
                    renderTo: 'chart-container'
                },
                title: {
                    text: '{!! addslashes(htmlentities($product->product_name)) !!}'
                },
                subtitle: {
                    text: '{!! addslashes(htmlentities($product->category->category_name)) !!}'
                },
                xAxis: {
                    type: "datetime"
                },
                yAxis: {
                    title: {
                        text: '$ Price'
                    },
                    plotLines: [{
                        value: 0,
                        width: 1,
                        color: '#808080'
                    }]
                },
                tooltip: {
                    valuePrefix: '$'
                },
                legend: {},
                series: []
            });
        }

        function submitAddContent(callback) {
            showLoading();
            $.ajax({
                "url": $("#frm-dashboard-widget-store").attr("action"),
                "method": "post",
                "data": {
                    "dashboard_id": $("#sel-dashboard-id").val(),
                    "dashboard_widget_name": $("#txt-dashboard-widget-name").val(),
                    "timespan": $("#sel-timespan").val(),
                    "resolution": $("#sel-period-resolution").val(),
                    "dashboard_widget_type_id": 1,
                    "category_id": "{{$product->category->getKey()}}",
                    "product_id": "{{$product->getKey()}}",
                    "chart_type": "product"
                },
                "dataType": "json",
                "success": function (response) {
                    hideLoading();
                    if (response.status == true) {
                        alertP("Add to Dashboard", "Chart has been added successfully");
                    } else {
                        if (typeof response.errors != 'undefined') {
                            var $errorContainer = $("#modal-product-chart .errors-container");
                            $errorContainer.empty();
                            $.each(response.errors, function (index, error) {
                                $errorContainer.append(
                                        $("<li>").text(error)
                                );
                            });
                        } else {
                            alertP("Error", "Unable to add chart to dashboard, please try again later.");
                        }
                    }
                },
                "error": function (xhr, status, error) {
                    hideLoading();
                    alertP("Error", "Unable to add chart to dashboard, please try again later.");
                }
            })
        }

        function timespanOnChange(el) {
            updateShowWhenCustomElements();
        }

        function periodResolutionOnChange(el) {

        }

        function updateShowWhenCustomElements() {
            if ($("#sel-timespan").val() == "custom") {
                $(".show-when-custom").slideDown();
            } else {
                $(".show-when-custom").slideUp();
            }
        }

        function loadProductChartData() {
            var startDate = null;
            var endDate = null;
            switch ($("#sel-timespan").val()) {
                case "this_week":
                    startDate = moment().startOf('isoweek').format("X");
                    endDate = moment().format("X");
                    break;
                case "last_week":
                    startDate = moment().subtract(1, 'week').startOf('isoweek').format("X");
                    endDate = moment().subtract(1, 'week').endOf('isoweek').format("X");
                    break;
                case "last_7_days":
                    startDate = moment().subtract(7, 'day').format("X");
                    endDate = moment().format("X");
                    break;
                case "this_month":
                    startDate = moment().startOf("month").format("X");
                    endDate = moment().format("X");
                    break;
                case "last_month":
                    startDate = moment().subtract(1, 'month').startOf("month").format("X");
                    endDate = moment().subtract(1, 'month').endOf("month").format("X");
                    break;
                case "last_30_days":
                    startDate = moment().subtract(30, 'day').format("X");
                    endDate = moment().format("X");
                    break;
                case "this_quarter":
                    startDate = moment().startOf("quarter").format("X");
                    endDate = moment().format("X");
                    break;
                case "last_quarter":
                    startDate = moment().subtract(1, 'quarter').startOf("quarter").format("X");
                    endDate = moment().subtract(1, 'quarter').endOf("quarter").format("X");
                    break;
                case "last_90_days":
                    startDate = moment().subtract(90, 'day').format("X");
                    endDate = moment().format("X");
                    break;
                case "custom":
                default:
                    startDate = $("#txt-product-chart-start-date").val();
                    endDate = $("#txt-product-chart-end-date").val();
            }

            if (startDate == null || endDate == null) {
                alertP("Error", "Please select the start date and end date for the timespan.");
                return false;
            }

            $("#txt-product-chart-start-date").val(startDate);
            $("#txt-product-chart-end-date").val(endDate);

            productChart.showLoading();
            $.ajax({
                "url": "{{$product->urls['chart']}}",
                "method": "get",
                "data": $("#frm-product-chart-characteristics").serialize(),
                "dataType": "json",
                "success": function (response) {
                    if (response.status == true) {
                        removeSeries();

                        $.each(response.data, function (siteId, site) {

                            productChart.addSeries({
                                name: site.name + " Average",
                                data: site.average,
                                tooltip: {
                                    pointFormat: '<span style="color:{point.color}">\u25CF</span> {series.name}: <b>${point.y:,.2f}</b><br/>'
                                }
                            });

                            productChart.redraw();
                            productChart.hideLoading();
                        });
                    }
                },
                "error": function (xhr, status, error) {

                }
            })
        }

        function removeSeries() {
            while (productChart.series.length > 0)
                productChart.series[0].remove(true);
        }
    </script>
</div>