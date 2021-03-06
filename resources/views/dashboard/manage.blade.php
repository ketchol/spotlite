@extends('layouts.adminlte')
@section('title', 'Manage Dashboard')
@section('header_title', 'Manage Dashboard')
@section('breadcrumbs')
{{--    {!! Breadcrumbs::render('manage_dashboard') !!}--}}
@stop
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-default">
                <div class="box-header with-border text-right">
                    {{--<h3 class="box-title"></h3>--}}
                    <button type="button" class="btn btn-primary btn-sm btn-flat" onclick="showAddDashboardForm(this)">
                        Create New Dashboard
                    </button>
                </div>
                <div class="box-body">
                    <table class="table table-striped table-condensed table-bordered" id="tbl-dashboard">
                        <thead>
                        <tr>
                            <th class="shrink"></th>
                            <th>Name</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script type="text/javascript">
        var tblDashboard;
        $(function () {

            jQuery.fn.dataTable.Api.register('processing()', function (show) {
                return this.iterator('table', function (ctx) {
                    ctx.oApi._fnProcessingDisplay(ctx, show);
                });
            });
            tblDashboard = $("#tbl-dashboard").DataTable({
                "pagingType": "full_numbers",
                "processing": true,
                "serverSide": true,
                "stateSave": true,
                "pageLength": 10,
                "ordering": false,
                "language": {
                    "emptyTable": "No dashboards in the list",
                    "zeroRecords": "No dashboards in the list"
                },
                "dom": "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'<\"toolbar-bottom-left\">><'col-sm-7'p>>",
                "ajax": {
                    "url": "{{route('dashboard.manage')}}",
                    "data": function (d) {
                        $.each(d.order, function (index, order) {
                            if (typeof d.columns[d.order[index].column] != "undefined") {
                                d.order[index].column = d.columns[d.order[index].column].name;
                            }
                        });
                    }
                },
                "columns": [
                    {
                        "name": "is_hidden",
                        "data": function (data) {
                            if (data.is_hidden == 'y') {
                                return $("<div>").append(
                                        $("<i>").addClass("fa fa-eye-slash text-muted")
                                ).html();
                            } else {
                                return "";
                            }
                        }
                    },
                    {
                        "name": "dashboard_name",
                        "data": "dashboard_name"
                    },
                    {
                        "data": function (data) {
                            return $("<div>").append(
                                    $("<div>").addClass("text-center").append(
                                            $("<a>").addClass("text-muted").attr({
                                                "href": "#",
                                                "onclick": "showEditDashboardForm(this); return false;",
                                                "data-url": data.urls['edit']
                                            }).append(
                                                    $("<i>").addClass("glyphicon glyphicon-cog")
                                            ),
                                            "&nbsp;",
                                            $("<a>").addClass("text-danger").attr({
                                                "href": "#",
                                                "onclick": "deleteDashboard(this); return false;",
                                                "data-url": data.urls['delete']
                                            }).append(
                                                    $("<i>").addClass("glyphicon glyphicon-trash")
                                            )
                                    )
                            ).html();
                        }
                    }
                ]
            });
        });

    </script>
@stop