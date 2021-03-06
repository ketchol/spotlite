<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['middleware' => ['auth']], function () {
    Route::group(['middleware' => ['subs']], function () {
        Route::get('/', 'Dashboard\DashboardController@index')->name("dashboard.index");
        Route::get('dashboard/manage', 'Dashboard\DashboardController@manage')->name('dashboard.manage');
        Route::put('dashboard/order', 'Dashboard\DashboardController@updateOrder')->name('dashboard.order.update');
        Route::get('dashboard/{dashboard_id}/filter', 'Dashboard\DashboardController@editFilter')->name('dashboard.filter.edit');
        Route::put('dashboard/{dashboard_id}/filter', 'Dashboard\DashboardController@updateFilter')->name('dashboard.filter.update');
        Route::delete('dashboard/{dashboard_id}/filter', 'Dashboard\DashboardController@deleteFilter')->name('dashboard.filter.destroy');
        Route::resource('dashboard', 'Dashboard\DashboardController');
        Route::put('dashboard/widget/order', 'Dashboard\DashboardWidgetController@updateOrder')->name('dashboard.widget.order.update');
        Route::resource('dashboard/widget', 'Dashboard\DashboardWidgetController');


        Route::get('msg/subscription/welcome/{raw?}', 'MessageController@welcomeSubscription')->name("msg.subscription.welcome");
        Route::get('msg/subscription/update/{raw?}', 'MessageController@updateSubscription')->name("msg.subscription.update");
        Route::get('msg/subscription/cc_expiring/{raw?}', 'MessageController@notifyCreditCardExpiringSoon')->name('msg.subscription.cc_expiring');


        /**
         * User Profile Related Routes
         */
        Route::get('profile/edit', 'User\ProfileController@edit')->name('profile.edit');
        Route::put('profile/init_update', 'User\ProfileController@initialUpdate')->name('profile.init_update');
        Route::resource('profile', 'User\ProfileController', ['except' => [
            'create', 'store', 'destroy', 'edit'
        ]]);

        Route::resource('user-domain', 'User\UserDomainController');


        /**
         * Group Related Routes
         */
        Route::get('group/first_login', 'User\GroupController@firstLogin');
        Route::resource('group', 'User\GroupController');


        /**
         * User Account Related Routes
         */
//        Route::get('account/edit', 'User\AccountController@edit')->name('account.edit');
        Route::resource('account', 'User\AccountController', ['except' => [
            'create', 'store', 'destroy', 'edit'
        ]]);

        /**
         * User Preferences Related Routes
         */
        Route::put('preference/{element}/{value}', 'User\UserPreferenceController@updatePreference')->name('preference.update');
        Route::put('preference', 'User\UserPreferenceController@massUpdatePreferences')->name('preference.mass_update');


        /**
         * Product Related Routes
         */


        //product import
        Route::post('product_import/product', 'Product\ImportProductController@storeProducts')->name('product_import.product.store');
        Route::post('product_import/site', 'Product\ImportProductController@storeSites')->name('product_import.site.store');

        //product routes
        Route::put('product/order', 'Product\ProductController@updateOrder')->name('product.order');
        Route::get('product/product/usage', 'Product\ProductController@getUserProductCredit')->name('product.product.usage');
        Route::get('product/{product_id}/site/usage/', 'Product\ProductController@getUserSiteCredit')->name('product.site.usage');
        Route::get('product/category/{category_id}', 'Product\ProductController@indexByCategory')->name('product.category.products');
        Route::resource('product', 'Product\ProductController', ['except' => [
            'edit'
        ]]);

        Route::get('positioning', 'Product\PositioningController@index')->name('positioning.index');
        Route::post('positioning/show', 'Product\PositioningController@show')->name('positioning.show');
        Route::get('positioning/export', 'Product\PositioningController@export')->name('positioning.export');


        //category routes
        Route::put('category/order', 'Product\CategoryController@updateOrder')->name('category.order');
        Route::get('category/{category_id}/site/usage', 'Product\CategoryController@getUserSiteCredit')->name('category.site.usage');
        Route::resource('category', 'Product\CategoryController', ['except' => ['edit']]);
        //site routes
        Route::post('site/prices', 'Product\SiteController@getPrices')->name('site.prices');
        Route::put("site/{site_id}/my_price", 'Product\SiteController@setMyPrice')->name('site.my_price');
        Route::put('site/order', 'Product\SiteController@updateOrder')->name('site.order');
        Route::get('site/product/{product_id}', 'Product\SiteController@indexByProduct')->name('site.product.sites');
        Route::resource('site', 'Product\SiteController');

        /**
         * Alert Related Routes
         */
        //not being used in anywhere but keep them
        Route::get('alert/category/{category_id}/edit', 'Product\AlertController@editCategoryAlert')->name('alert.category.edit');
        Route::put('alert/category/{category_id}', 'Product\AlertController@updateCategoryAlert')->name('alert.category.update');
        Route::delete('alert/category/{category_id}', 'Product\AlertController@deleteCategoryAlert')->name('alert.category.destroy');
        Route::get('alert/product/{product_id}/edit', 'Product\AlertController@editProductAlert')->name('alert.product.edit');
        Route::put('alert/product/{product_id}', 'Product\AlertController@updateProductAlert')->name('alert.product.update');
        Route::delete('alert/product/{product_id}', 'Product\AlertController@deleteProductAlert')->name('alert.product.destroy');
        Route::get('alert/site/{site_id}/edit', 'Product\AlertController@editSiteAlert')->name('alert.site.edit');
        Route::put('alert/site/{site_id}', 'Product\AlertController@updateSiteAlert')->name('alert.site.update');
        Route::delete('alert/site/{site_id}', 'Product\AlertController@deleteSiteAlert')->name('alert.site.destroy');
        Route::get('alert/user/edit', 'Product\AlertController@setUpNotification')->name('alert.user.edit');
        Route::put('alert/user', 'Product\AlertController@updateNotification')->name('alert.user.update');
        Route::delete('alert/user', 'Product\AlertController@deleteNotification')->name('alert.user.destroy');


        //currently being used
        Route::get('alert/set_up_notifications', 'Product\AlertController@setUpNotification')->name('alert.set_up_notifications');
        Route::put('alert/set_up_notifications', 'Product\AlertController@updateNotification')->name('alert.update_notifications');
        Route::delete('alert/set_up_notifications', 'Product\AlertController@deleteNotification')->name('alert.delete_notifications');

        Route::resource('alert', 'Product\AlertController');
        Route::resource('alert_log', 'Log\AlertActivityLogController');


        /**
         * Report Related Routes
         */
        Route::get('report/category/{category_id}/edit', 'Product\ReportTaskController@editCategoryReport')->name('report_task.category.edit');
        Route::put('report/category/{category_id}', 'Product\ReportTaskController@updateCategoryReport')->name('report_task.category.update');
        Route::delete('report/category/{category_id}', 'Product\ReportTaskController@deleteCategoryReport')->name('report_task.category.destroy');
        Route::get('report/product/{product_id}/edit', 'Product\ReportTaskController@editProductReport')->name('report_task.product.edit');
        Route::put('report/product/{product_id}', 'Product\ReportTaskController@updateProductReport')->name('report_task.product.update');
        Route::delete('report/product/{product_id}', 'Product\ReportTaskController@deleteProductReport')->name('report_task.product.destroy');
        /*load all report tasks*/
        Route::get('report/task', 'Product\ReportTaskController@index')->name('report_task.index');

        /**
         * Report Page Related Routes
         */
        Route::resource('report', 'Product\ReportController', ['only' => [
            'index', 'show', 'destroy'
        ]]);


        /**
         * Chart Related Routes
         */
        Route::get('chart/category/{category_id}', 'Product\ChartController@categoryIndex')->name('chart.category.index');
        Route::get('chart/product/{product_id}', 'Product\ChartController@productIndex')->name('chart.product.index');
        Route::get('chart/site/{site_id}', 'Product\ChartController@SiteIndex')->name('chart.site.index');
    });


    /**
     * Subscription Related Routes
     */
    //for those users who registered but not yet subscribe
    Route::get('subscription/back', 'Subscription\SubscriptionController@viewProducts')->name('subscription.back');
    //redirect route for chargify sign up page
    Route::get('subscription/finalise', 'Subscription\SubscriptionController@finalise')->name('subscription.finalise');
    Route::get('subscription/update', 'Subscription\SubscriptionController@externalUpdate')->name('subscription.external_update');
    Route::get('subscription/confirm', 'Subscription\SubscriptionController@confirm')->name('subscription.confirm');

    Route::resource('subscription', 'Subscription\SubscriptionController', ['except' => [
        'create', 'show'
    ]]);


    Route::get('msg/subscription/cancelled/{id}/{raw?}', 'MessageController@cancelledSubscription')->name("msg.subscription.cancelled");


    /* ADMIN */
    /* logging */
    Route::resource('log/user_activity', 'Log\UserActivityLogController', ['only' => [
        'index', 'show'
    ]]);
    Route::resource('log/crawler_activity', 'Log\CrawlerActivityLogController', ['only' => [
        'index'
    ]]);

    /* admin crawler management */
    Route::post('admin/site/test/{site_id}', 'Crawler\SiteController@sendTest')->name('admin.site.test');
    Route::get('admin/site/xpath/{site_id}/edit', 'Crawler\SiteController@editxPath')->name('admin.site.xpath.edit');
    Route::put('admin/site/xpath/{site_id}/edit', 'Crawler\SiteController@updatexPath')->name('admin.site.xpath.update');
    Route::put('admin/site/status/{site_id}/edit', 'Crawler\SiteController@setStatus')->name('admin.site.status.update');
    Route::post('admin/site/queue/{site_id}', 'Crawler\SiteController@queue')->name('admin.site.queue');
    Route::resource('admin/site', 'Crawler\SiteController', ['except' => [
        'show', 'edit'
    ]]);
    Route::resource('admin/crawler', 'Crawler\CrawlerController', ['only' => [
        'edit', 'update'
    ]]);

    Route::get('admin/domain/xpath/{domain_id}/edit', 'Crawler\DomainController@editxPath')->name('admin.domain.xpath.edit');
    Route::put('admin/domain/xpath/{domain_id}/edit', 'Crawler\DomainController@updatexPath')->name('admin.domain.xpath.update');
    Route::put('admin/domain/classes/{domain_id}', 'Crawler\DomainController@updateClasses')->name('admin.domain.classes.update');
    Route::resource('admin/domain', 'Crawler\DomainController', ['except' => [
        'show', 'edit'
    ]]);
    /* admin app preferences management */
    Route::put('admin/app_preference/all', 'Admin\AppPreferenceController@update')->name('admin.app_preference.update');
    Route::resource('admin/app_preference', 'Admin\AppPreferenceController', ['except' => [
        'update'
    ]]);

    Route::get('password/init_reset', 'Auth\PasswordController@getSetPasswordPopup')->name('password.init_reset.get');
    Route::post('password/init_reset', 'Auth\PasswordController@postSetPassword')->name('password.init_reset.post');

    Route::get('logout', 'Auth\AuthController@logout')->name('logout');


    Route::resource('privacy_policy', 'Legal\PrivacyPolicyController', ['except' => ['show']]);
    Route::resource('term_and_condition', 'Legal\TermAndConditionController', ['except' => ['show']]);
    Route::put('privacy_policy/activeness/{id}', 'Legal\PrivacyPolicyController@toggleActiveness')->name('privacy_policy.activeness');
    Route::put('term_and_condition/activeness/{id}', 'Legal\TermAndConditionController@toggleActiveness')->name('term_and_condition.activeness');


    Route::get('um/user/login-as/{id}', 'UM\UserController@loginAs')->name('um.user.login_as');
});

/**
 * Auth Related Routes
 */
Route::get('login', 'Auth\AuthController@getLogin')->name('login.get');
Route::post('login', 'Auth\AuthController@postLogin')->name('login.post');

Route::get('register', 'Auth\AuthController@getRegister')->name('register.get');
//Route::get('register', function () {
//    return redirect()->to("http://www.spotlite.com.au");
//})->name('register.get');
Route::post('register', 'Auth\AuthController@postRegister')->name('register.post');

Route::get('register/external', 'Auth\AuthController@registerExternalPreview')->name('register.get.external');
//Route::get('register/external', function(){
//    dd("fu");
//})->name('register.get.external');
Route::post('register/external', 'Auth\AuthController@registerExternal')->name('register.post.external');

Route::get('password', 'Auth\PasswordController@getEmail')->name('password.get');
Route::post('password', 'Auth\PasswordController@postEmail')->name('password.post');
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset')->name('password.reset.get');
Route::post('password/reset', 'Auth\PasswordController@postReset')->name('password.reset.post');

Route::get('privacy_policy/{id}', 'Legal\PrivacyPolicyController@show')->name('privacy_policy.show');
Route::get('term_and_condition/{id}', 'Legal\TermAndConditionController@show')->name('term_and_condition.show');


Route::match(['get', 'post', 'put', 'delete'], 'subscription/webhook', 'Subscription\SubscriptionController@webhookUpdate')->name('subscription.webhook_update');
Route::get('subscription/product_families', 'Subscription\SubscriptionController@productFamiliesAU')->name('subscription.product_families');
Route::get('subscription/product_families_us', 'Subscription\SubscriptionController@productFamiliesUS')->name('subscription.product_families_us');

/**
 * Error messages
 */
Route::get('cookie_disabled', function () {
    return view('errors.cookie_disabled');
})->name('errors.cookie_disabled');

Route::get('javascript_disabled', function () {
    return view('errors.javascript_disabled');
})->name('errors.javascript_disabled');

Route::post('error/notify_error', 'ErrorController@notifyError')->name('errors.notify');

Route::get('token', 'TokenController@getToken')->name('token.index');

Route::get('contact_us/form', 'SupportController@contactUsIndex')->name('contact_us.form');
Route::post('contact_us', 'SupportController@contactUs')->name('contact_us.post');
Route::get('contact_us', 'SupportController@contactUs')->name('contact_us.get');
Route::get('sign_up_for_beta_testing', 'SupportController@signUpForBetaTesting')->name('contact_us.sign_up.post');


Route::get('unsubscribe/{page}', 'UnsubscribeController@create')->name('unsubscribe.create');
Route::post('unsubscribe', 'UnsubscribeController@store')->name('unsubscribe.store');


/**
 * APIs
 */

Route::get('api/geo/country/{ip_address?}', 'API\GeoController@countryByIP')->name('api.geo.country');
Route::get('api/geo/state/{ip_address?}', 'API\GeoController@stateByIP')->name('api.geo.country');
Route::get('api/geo/city/{ip_address?}', 'API\GeoController@cityByIP')->name('api.geo.city');
Route::get('api/geo/all/{ip_address?}', 'API\GeoController@allByIP')->name('api.geo.all');

Route::get('api/subscription/verify_coupon', 'API\SubscriptionController@verifyCoupon')->name('api.subscription.coupon');

Route::get('test', 'TestController@index');