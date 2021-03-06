var del = require('del');
var elixir = require('laravel-elixir');
var gulp = require('gulp');
var task = elixir.Task;

elixir.extend('remove', function (path) {
    new task('remove', function () {
        return del(path);
    });
});

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function (mix) {
    mix.remove([
        'public/css',
        'public/js',
        'public/fonts',
        'public/images',
        'public/videos',
        'public/csvs',
        'public/packages',
        'public/build'
    ]);
    mix.styles([
        "node_modules/bootstrap/dist/css/bootstrap.css",
        "node_modules/datatables.net-bs/css/dataTables.bootstrap.css",
        "node_modules/select2/dist/css/select2.css",
        'vendor/driftyco/ionicons/css/ionicons.css',
        'node_modules/font-awesome/css/font-awesome.css',
        'vendor/almasaeed2010/adminlte/dist/css/AdminLTE.min.css',
        'vendor/almasaeed2010/adminlte/dist/css/skins/_all-skins.css',
        'node_modules/dragula/dist/dragula.css',
        'resources/assets/css/file-tree.css',
        'vendor/almasaeed2010/adminlte/plugins/datepicker/datepicker3.css',
        'vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.css',
        'vendor/almasaeed2010/adminlte/plugins/icheck/square/blue.css',
        'node_modules/jquery-contextmenu/dist/jquery.contextMenu.css',
        'resources/assets/css/nlform.css',
        'resources/assets/css/welcome_popup.css',
        'resources/assets/css/set_password_popup.css',
        'resources/assets/css/spotlite.css',
        'resources/assets/css/color_scheme.css'
    ], "public/css/main.css", "./");
    mix.scripts([
        'node_modules/bootstrap-tour/build/css/bootstrap-tour.css'
    ], "public/css/tour.css", './');
    mix.styles([
        'resources/assets/css/email.css'
    ], "public/css/email.css", "./");
    mix.styles([
        'resources/assets/css/email_import.css'
    ], "public/css/email_import.css", "./");
    mix.styles([
        'resources/assets/css/email_brand.css'
    ], "public/css/email_brand.css", "./");
    mix.scripts([
        'resources/assets/js/error.js',
        'vendor/almasaeed2010/adminlte/plugins/jQuery/jquery-2.2.3.min.js',
        "node_modules/bootstrap/dist/js/bootstrap.js",
        "node_modules/datatables.net/js/jquery.dataTables.js",
        "node_modules/datatables.net-bs/js/dataTables.bootstrap.js",
        "node_modules/select2/dist/js/select2.js",
        'vendor/almasaeed2010/adminlte/plugins/slimScroll/jquery.slimscroll.min.js',
        'vendor/almasaeed2010/adminlte/plugins/fastclick/fastclick.js',
        'vendor/almasaeed2010/adminlte/dist/js/app.js',
        'node_modules/dragula/dist/dragula.js',
        'node_modules/dom-autoscroller/dist/dom-autoscroller.js',
        "node_modules/highcharts/highcharts.js",
        "node_modules/highcharts/highcharts-more.js",
        "node_modules/highcharts/modules/exporting.js",
        "node_modules/highcharts/modules/no-data-to-display.js",
        'vendor/almasaeed2010/adminlte/plugins/datepicker/bootstrap-datepicker.js',
        'vendor/almasaeed2010/adminlte/plugins/daterangepicker/moment.js',
        'vendor/almasaeed2010/adminlte/plugins/daterangepicker/daterangepicker.js',
        'vendor/almasaeed2010/adminlte/plugins/iCheck/icheck.js',
        'node_modules/jquery-contextmenu/dist/jquery.contextMenu.js',
        'resources/assets/js/nlform.js',
        'resources/assets/js/commonFunctions.js',
        'resources/assets/js/google_analytics.js',
        'resources/assets/js/sidebar.js'
    ], "public/js/main.js", "./");
    mix.scripts([
        'vendor/almasaeed2010/adminlte/plugins/jQuery/jquery-2.2.3.min.js',
        "node_modules/bootstrap/dist/js/bootstrap.js",
        "node_modules/select2/dist/js/select2.js",
        'vendor/almasaeed2010/adminlte/plugins/slimScroll/jquery.slimscroll.min.js',
        'vendor/almasaeed2010/adminlte/plugins/fastclick/fastclick.js',
        'vendor/almasaeed2010/adminlte/dist/js/app.js',
        'vendor/almasaeed2010/adminlte/plugins/iCheck/icheck.js',
        'resources/assets/js/commonFunctions.js',
        'resources/assets/js/google_analytics.js'
    ], "public/js/auth.js", "./");

    mix.scripts([
        'resources/assets/js/product/single_category.js',
        'resources/assets/js/product/single_product.js',
        'resources/assets/js/product/single_site.js'
    ], "public/js/product.js", "./");

    mix.scripts([
        'resources/assets/js/dashboard/manage_dashboard.js'
    ], "public/js/dashboard.js", "./");

    mix.scripts([
        'resources/assets/js/zendesk_widget.js'
    ], "public/js/zendesk.js", "./");

    mix.scripts([
        'node_modules/bootstrap-tour/build/js/bootstrap-tour.js',
        'resources/assets/js/dashboard-tour.js'
    ], "public/js/dashboard-tour.js", './');

    mix.scripts([
        'node_modules/bootstrap-tour/build/js/bootstrap-tour.js',
        'resources/assets/js/product-tour.js'
    ], "public/js/product-tour.js", './');

    mix.scripts([
        'node_modules/bootstrap-tour/build/js/bootstrap-tour.js',
        'resources/assets/js/alert-tour.js'
    ], "public/js/alert-tour.js", './');

    mix.scripts([
        'node_modules/bootstrap-tour/build/js/bootstrap-tour.js',
        'resources/assets/js/report-tour.js'
    ], "public/js/report-tour.js", './');

    mix.scripts([
        'resources/assets/js/spotlite.js'
    ], "public/js/spotlite.js", './');

    /* copy images */
    mix.copy('resources/assets/images', 'public/images');
    mix.copy('vendor/almasaeed2010/adminlte/plugins/iCheck/square/blue.png', 'public/images//blue.png');
    mix.copy('vendor/almasaeed2010/adminlte/plugins/iCheck/square/blue@2x.png', 'public/images//blue@2x.png');
    mix.copy('resources/assets/plugins/jquery.fileTree-1.01/images', 'public/images');
    mix.copy('resources/assets/videos', 'public/videos');
    mix.copy('resources/assets/others', 'public/others');

    /* copy fonts */
    mix.copy("node_modules/bootstrap/dist/fonts", "public/fonts/");
    mix.copy("node_modules/font-awesome/fonts", "public/fonts/");
    mix.copy('vendor/driftyco/ionicons/fonts', 'public/fonts/');
    mix.copy('resources/assets/fonts', 'public/fonts/');
    mix.copy('resources/assets/csvs', 'public/csvs');

    /* copy packages */
    mix.copy('resources/assets/packages', 'public/packages');

    /* versioning */
    mix.version([
        'public/css/main.css', 'public/css/tour.css', 'public/css/email.css', 'public/css/email_import.css', 'public/css/email_brand.css',
        'public/js/main.js', 'public/js/auth.js', 'public/js/zendesk.js', 'public/js/product-tour.js', 'public/js/dashboard-tour.js',
        'public/js/alert-tour.js', 'public/js/report-tour.js', 'public/js/dashboard.js', 'public/js/product.js', 'public/js/spotlite.js'
    ]);
    mix.copy('public/fonts', 'public/build/fonts');
    mix.copy('public/images', 'public/build/images');
    mix.copy('public/videos', 'public/build/videos');
    mix.copy('public/others', 'public/build/others');
    mix.copy('public/packages', 'public/build/packages');
    mix.copy('public/csvs', 'public/build/csvs');
});
