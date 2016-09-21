<div class="modal fade" tabindex="-1" role="dialog" id="modal-category-chart">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">{{$category->category_name}}</h4>
            </div>
            <div class="modal-body" style="background-color: #f5f5f5;">
                <div class="row">
                    <div class="col-lg-5 col-md-6 col-sm-12">

                        <div class="box box-solid">
                            <div class="box-header with-border">
                                <h3 class="box-title">Chart Characteristics</h3>
                            </div>
                            <div class="box-body">
                                <div class="row m-b-10">
                                    <div class="col-sm-12">
                                        <form action="" class="form-horizontal" id="frm-category-chart-characteristics">
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
                                                               id="txt-category-chart-start-date">
                                                        <input type="hidden" name="end_date"
                                                               id="txt-category-chart-end-date">
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
                                        <button class="btn btn-primary" onclick="loadCategoryChartData()">
                                            Generate Chart
                                        </button>
                                        <button class="btn btn-default" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
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
        function modalReady() {

            $("#txt-date-range").daterangepicker({
                "maxDate": moment()
            }).on('apply.daterangepicker', function (ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD') + ' - ' + picker.endDate.format('YYYY-MM-DD'));
                $("#txt-category-chart-start-date").val(picker.startDate.format('X'));
                $("#txt-category-chart-end-date").val(picker.endDate.format('X'));
            });

            var ranges = [
                        [1246406400000, 16.3, 27.7],
                        [1246492800000, 17.5, 27.8],
                        [1246579200000, 22.5, 29.6],
                        [1246665600000, 23.7, 30.7],
                        [1246752000000, 24.5, 25.0],
                        [1246838400000, 25.8, 25.7],
                        [1246924800000, 13.5, 24.8],
                        [1247011200000, 10.5, 21.4],
                        [1247097600000, 9.2, 23.8],
                        [1247184000000, 11.6, 21.8],
                        [1247270400000, 10.7, 23.7],
                        [1247356800000, 11.0, 23.3],
                        [1247443200000, 66.6, 23.7],
                        [1247529600000, 23.8, 20.7],
                        [1247616000000, 12.6, 22.4],
                        [1247702400000, 22.6, 19.6],
                        [1247788800000, 41.4, 22.6],
                        [1247875200000, 13.2, 25.0],
                        [1247961600000, 24.2, 21.6],
                        [1248048000000, 13.1, 17.1],
                        [1248134400000, 13.2, 15.5],
                        [1248220800000, 12.0, 20.8],
                        [1248307200000, 12.0, 17.1],
                        [1248393600000, 12.7, 18.3],
                        [1248480000000, 12.4, 19.4],
                        [1248566400000, 12.6, 19.9],
                        [1248652800000, 11.9, 20.2],
                        [1248739200000, 11.0, 19.3],
                        [1248825600000, 10.8, 17.8],
                        [1248912000000, 11.8, 18.5],
                        [1248998400000, 10.8, 16.1]
                    ],
                    averages = [
                        [1246406400000, 21.5],
                        [1246492800000, 22.1],
                        [1246579200000, 23],
                        [1246665600000, 23.8],
                        [1246752000000, 21.4],
                        [1246838400000, 21.3],
                        [1246924800000, 18.3],
                        [1247011200000, 15.4],
                        [1247097600000, 16.4],
                        [1247184000000, 17.7],
                        [1247270400000, 17.5],
                        [1247356800000, 17.6],
                        [1247443200000, 17.7],
                        [1247529600000, 16.8],
                        [1247616000000, 17.7],
                        [1247702400000, 16.3],
                        [1247788800000, 17.8],
                        [1247875200000, 18.1],
                        [1247961600000, 17.2],
                        [1248048000000, 14.4],
                        [1248134400000, 13.7],
                        [1248220800000, 15.7],
                        [1248307200000, 14.6],
                        [1248393600000, 15.3],
                        [1248480000000, 15.3],
                        [1248566400000, 15.8],
                        [1248652800000, 15.2],
                        [1248739200000, 14.8],
                        [1248825600000, 14.4],
                        [1248912000000, 15],
                        [1248998400000, 13.6]
                    ];


            var ranges1 = [
                        [1246406400000, 14.3, 27.7],
                        [1246492800000, 14.5, 27.8],
                        [1246579200000, 15.5, 29.6],
                        [1246665600000, 16.7, 30.7],
                        [1246752000000, 16.5, 25.0],
                        [1246838400000, 17.8, 25.7],
                        [1246924800000, 13.5, 24.8],
                        [1247011200000, 10.5, 21.4],
                        [1247097600000, 9.2, 23.8],
                        [1247184000000, 11.6, 21.8],
                        [1247270400000, 10.7, 23.7],
                        [1247356800000, 11.0, 23.3],
                        [1247443200000, 11.6, 23.7],
                        [1247529600000, 11.8, 20.7],
                        [1247616000000, 12.6, 22.4],
                        [1247702400000, 13.6, 19.6],
                        [1247788800000, 11.4, 22.6],
                        [1247875200000, 13.2, 25.0],
                        [1247961600000, 20.2, 21.6],
                        [1248048000000, 13.1, 17.1],
                        [1248134400000, 12.2, 15.5],
                        [1248220800000, 12.0, 20.8],
                        [1248307200000, 12.0, 17.1],
                        [1248393600000, 12.7, 18.3],
                        [1248480000000, 30.4, 19.4],
                        [1248566400000, 12.6, 19.9],
                        [1248652800000, 11.9, 20.2],
                        [1248739200000, 11.0, 19.3],
                        [1248825600000, 10.8, 17.8],
                        [1248912000000, 11.8, 18.5],
                        [1248998400000, 10.8, 16.1]
                    ],
                    averages1 = [
                        [1246406400000, 21.5],
                        [1246492800000, 22.1],
                        [1246579200000, 23],
                        [1246665600000, 23.8],
                        [1246752000000, 21.4],
                        [1246838400000, 21.3],
                        [1246924800000, 18.3],
                        [1247011200000, 15.4],
                        [1247097600000, 16.4],
                        [1247184000000, 17.7],
                        [1247270400000, 17.5],
                        [1247356800000, 17.6],
                        [1247443200000, 17.7],
                        [1247529600000, 16.8],
                        [1247616000000, 17.7],
                        [1247702400000, 33.3],
                        [1247788800000, 17.8],
                        [1247875200000, 42.1],
                        [1247961600000, 17.2],
                        [1248048000000, 14.4],
                        [1248134400000, 13.7],
                        [1248220800000, 15.7],
                        [1248307200000, 14.6],
                        [1248393600000, 15.3],
                        [1248480000000, 22.3],
                        [1248566400000, 15.8],
                        [1248652800000, 15.2],
                        [1248739200000, 14.8],
                        [1248825600000, 14.4],
                        [1248912000000, 15],
                        [1248998400000, 13.6]
                    ];


            $('#chart-container').highcharts({
                title: {
                    text: 'July temperatures'
                },

                xAxis: {
                    type: 'datetime'
                },
                yAxis: {
                    title: {
                        text: null
                    }
                },
                tooltip: {
                    crosshairs: true,
                    shared: true,
                    valueSuffix: '°C'
                },

                legend: {},
                series: [
                    {
                        name: 'Temperature',
                        data: averages,
                        zIndex: 1,
                        marker: {
                            fillColor: 'white',
                            lineWidth: 2,
                            lineColor: Highcharts.getOptions().colors[0]
                        }
                    },
                    {
                        name: 'Range',
                        data: ranges,
                        type: 'arearange',
                        lineWidth: 0,
                        linkedTo: ':previous',
                        color: Highcharts.getOptions().colors[0],
                        fillOpacity: 0.3,
                        zIndex: 0
                    },
                    {
                        name: 'Temperature',
                        data: averages1,
                        zIndex: 1,
                        marker: {
                            fillColor: 'white',
                            lineWidth: 2,
                            lineColor: Highcharts.getOptions().colors[0]
                        }
                    },
                    {
                        name: 'Range',
                        data: ranges1,
                        type: 'arearange',
                        lineWidth: 0,
                        linkedTo: ':previous',
                        color: Highcharts.getOptions().colors[0],
                        fillOpacity: 0.3,
                        zIndex: 0
                    }
                ]
            });
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

        function loadCategoryChartData() {
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
                    startDate = $("#txt-category-chart-start-date").val();
                    endDate = $("#txt-category-chart-end-date").val();
            }

            if (startDate == null || endDate == null) {
                alertP("Error", "Please select the start date and end date for the timespan.");
                return false;
            }

            $("#txt-category-chart-start-date").val(startDate);
            $("#txt-category-chart-end-date").val(endDate);

            $.ajax({
                "url": "{{$category->urls['chart']}}",
                "method": "get",
                "data": $("#frm-category-chart-characteristics").serialize(),
                "dataType": "json",
                "success": function (response) {
                    console.info('response', response);
                },
                "error": function (xhr, status, error) {

                }
            })
        }
    </script>
</div>