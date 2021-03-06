@extends('layouts.adminlte')
@section('title', 'Create Permission')
@section('header_title', 'Create Permission')
@section('breadcrumbs')
{{--    {!! Breadcrumbs::render('create_permission') !!}--}}
@stop
@section('content')
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-solid">
                <div class="box-body">
                    <div class="um-form-container">
                        @include('um::forms.permission.create')
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
@section('scripts')
    <script type="text/javascript">
        $(function () {
            $(".um-form-container select").select2({
                "allowClear": true,
                "placeholder": ""
            });
        })
    </script>
@stop