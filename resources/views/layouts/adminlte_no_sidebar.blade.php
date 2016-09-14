<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 8/16/2016
 * Time: 2:45 PM
 */
?>

        <!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>@yield('title') - SpotLite</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <link rel="stylesheet" href="{{elixir('css/main.css')}}">

    @yield('links')

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition skin-blue sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">

    @include('layout_partials.default_header')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->

        <!-- Main content -->
        <section class="content">
            @yield('content')
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Add the sidebar's background. This div must be placed
         immediately after the control sidebar -->
    <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->

@include('scripts.variable_setter')

<script type="text/javascript" src="{{elixir('js/main.js')}}"></script>
{{--<script type="text/javascript" src="{{asset('build/almasaeed2010/adminlte/plugins/jQuery/jquery-2.2.3.min.js')}}"></script>--}}
{{--<script type="text/javascript" src="{{asset("build/bootstrap/dist/js/bootstrap.js")}}"></script>--}}
{{--<script type="text/javascript" src="{{asset("build/datatables.net/js/jquery.dataTables.js")}}"></script>--}}
{{--<script type="text/javascript" src="{{asset("build/datatables.net-bs/js/dataTables.bootstrap.js")}}"></script>--}}
{{--<script type="text/javascript" src="{{asset("build/select2/dist/js/select2.js")}}"></script>--}}
{{--<script type="text/javascript" src="{{asset('build/almasaeed2010/adminlte/plugins/slimScroll/jquery.slimscroll.min.js')}}"></script>--}}
{{--<script type="text/javascript" src="{{asset('build/almasaeed2010/adminlte/plugins/fastclick/fastclick.js')}}"></script>--}}
{{--<script type="text/javascript" src="{{asset('build/almasaeed2010/adminlte/dist/js/app.js')}}"></script>--}}
{{--<script type="text/javascript" src="{{asset('build/js/commonFunctions.js')}}"></script>--}}
{{--<script type="text/javascript" src="{{asset('build/js/spotlite.js')}}"></script>--}}

@yield('scripts')

@include('scripts.notification')

</body>
</html>

