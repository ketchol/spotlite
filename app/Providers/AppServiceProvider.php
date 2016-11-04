<?php

namespace App\Providers;

use App\Models\Alert;
use App\Models\AlertEmail;
use App\Models\Crawler;
use App\Models\Dashboard\Dashboard;
use App\Models\DeletedRecordModels\DeletedAlert;
use App\Models\DeletedRecordModels\DeletedAlertEmail;
use App\Models\DeletedRecordModels\DeletedCategory;
use App\Models\DeletedRecordModels\DeletedCrawler;
use App\Models\DeletedRecordModels\DeletedDomain;
use App\Models\DeletedRecordModels\DeletedGroup;
use App\Models\DeletedRecordModels\DeletedProduct;
use App\Models\DeletedRecordModels\DeletedSite;
use App\Models\Domain;
use App\Models\Group;
use App\Models\Site;
use App\Models\SitePreference;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserPreference;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\Category;
use Invigor\Crawler\Contracts\CrawlerInterface;
use Invigor\Crawler\Contracts\ParserInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'product' => Product::class,
            'category' => Category::class,
            'site' => Site::class
        ]);

        /**
         * create default dashboard and preferences when user is created
         */
        User::created(function ($user) {
            if (is_null($user->preference('DATE_FORMAT'))) {
                UserPreference::setPreference($user, "DATE_FORMAT", "Y-m-d");
            }
            if (is_null($user->preference('TIME_FORMAT'))) {
                UserPreference::setPreference($user, "TIME_FORMAT", "g:i a");
            }
            Dashboard::create(array(
                "user_id" => $user->getKey(),
                'dashboard_template_id' => 1,
                'dashboard_name' => 'Default Dashboard',
                'dashboard_order' => 1
            ));
        });

        /* when site is saved, create default site preferences and domain (if domain not exist) */
        Site::created(function ($site) {
            /**
             * Create crawler when site is created
             */
            if (is_null($site->crawler)) {
                Crawler::create(array(
                    "site_id" => $site->getKey()
                ));
            }

            /**
             * Create site preference when site is created
             */
            if (is_null($site->preference)) {
                SitePreference::create(array(
                    "site_id" => $site->getKey()
                ));
            }

            /* create domain if the domain of site url does not exist */
            $newDomain = parse_url($site->site_url)['host'];
            if (Domain::where('domain_url', $newDomain)->count() == 0) {
                Domain::create(array(
                    "domain_url" => $newDomain
                ));
            }
            return true;
        });

        /*************************************************************************
         * On Delete Back up
         *************************************************************************/
        Alert::deleting(function ($alert) {
            /* delete alert emails if there are any */
            foreach ($alert->emails as $email) {
                $email->delete();
            }
            DeletedAlert::create(array(
                "content" => $alert->toJson()
            ));
            return true;
        });
        AlertEmail::deleting(function ($alertEmail) {
            DeletedAlertEmail::create(array(
                "content" => $alertEmail->toJson()
            ));
            return true;
        });
        Category::deleting(function ($category) {
            DeletedCategory::create(array(
                "content" => $category->toJson()
            ));
            foreach ($category->products as $product) {
                $product->delete();
            }
            return true;
        });

        Crawler::deleting(function ($crawler) {
            DeletedCrawler::create(array(
                "content" => $crawler->toJson()
            ));
            return true;
        });

        Domain::deleting(function ($domain) {
            DeletedDomain::create(array(
                "content" => $domain->toJson()
            ));
            return true;
        });

        Group::deleting(function ($group) {
            DeletedGroup::create(array(
                "content" => $group->toJson()
            ));
            return true;
        });

        Product::deleting(function ($product) {
            if (!is_null($product->alert)) {
                $product->alert->delete();
            }
            DeletedProduct::create(array(
                "content" => $product->toJson()
            ));
            foreach ($product->sites as $site) {
                $site->delete();
            }
            return true;
        });

        Site::deleting(function ($site) {
            DeletedSite::create(array(
                "content" => $site->toJson()
            ));
            return true;
        });

        /************************************************************************
         * clearing cache
         ************************************************************************/
        Subscription::saved(function ($subscription) {
            if (isset($subscription->user_id)) {
                Cache::forget("user.{$subscription->user_id}.subscription");
                Cache::forget("user.{$subscription->user_id}.subscription.api");
            }
            return true;
        });

        Subscription::deleting(function ($subscription) {
            Cache::forget("user.{$subscription->user_id}.subscription");
            Cache::forget("user.{$subscription->user_id}.subscription.api");
            return true;
        });

        User::saved(function ($user) {
            $userPrimaryKey = $user->getKeyName();
            Cache::forget("user.{$user->$userPrimaryKey}.subscription");
            return true;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('App\Contracts\Repository\Mailer\MailerContract', 'App\Repositories\Mailer\MailerRepository');
        $this->app->bind('App\Contracts\Repository\User\Group\GroupContract', 'App\Repositories\User\Group\GroupRepository');
        $this->app->bind('App\Contracts\Repository\Crawler\CrawlerContract', 'App\Repositories\Crawler\CrawlerRepository');
        $this->app->bind('App\Contracts\Repository\Subscription\SubscriptionContract', 'App\Repositories\Subscription\ChargifySubscriptionRepository');
        $this->app->bind('App\Contracts\Repository\Product\Report\ReportContract', 'App\Repositories\Product\Report\ReportRepository');
        $this->app->bind('App\Contracts\Repository\Product\Report\ReportTaskContract', 'App\Repositories\Product\Report\ReportTaskRepository');
        $this->app->bind('App\Contracts\Repository\Product\Alert\AlertContract', 'App\Repositories\Product\Alert\AlertRepository');
        $this->app->bind('App\Contracts\Repository\Product\Product\ProductContract', 'App\Repositories\Product\Product\ProductRepository');
        $this->app->bind('App\Contracts\Repository\Product\Category\CategoryContract', 'App\Repositories\Product\Category\CategoryRepository');
        $this->app->bind('App\Contracts\Repository\Product\Site\SiteContract', 'App\Repositories\Product\Site\SiteRepository');
        $this->app->bind('App\Contracts\Repository\Product\Domain\DomainContract', 'App\Repositories\Product\Domain\DomainRepository');
        $this->app->bind('App\Contracts\Repository\Dashboard\DashboardContract', 'App\Repositories\Dashboard\DashboardRepository');
        $this->app->bind('App\Contracts\Repository\Dashboard\DashboardTemplateContract', 'App\Repositories\Dashboard\DashboardTemplateRepository');
        $this->app->bind('App\Contracts\Repository\Dashboard\DashboardWidgetContract', 'App\Repositories\Dashboard\DashboardWidgetRepository');
        $this->app->bind('App\Contracts\Repository\Dashboard\DashboardWidgetTypeContract', 'App\Repositories\Dashboard\DashboardWidgetTypeRepository');
        $this->app->bind('App\Contracts\Repository\Mailer\MailingAgentContract', 'App\Repositories\Mailer\MailingAgentRepository');

        /* Site Query Filters */
        $this->app->when('App\Http\Controllers\Crawler\SiteController')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\AdminSiteFilters');
        $this->app->when('App\Models\Site')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\AdminSiteFilters');

        /* Domain Query Filters */
        $this->app->when('App\Http\Controllers\Crawler\DomainController')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\AdminDomainFilters');
        $this->app->when('App\Models\Domain')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\AdminDomainFilters');

        /* Category Query Filters */
        $this->app->when('App\Http\Controllers\Product\ProductController')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\CategoryFilters');
        $this->app->when('App\Models\Category')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\CategoryFilters');

        /* ReportTask Query Filters */
        $this->app->when('App\Http\Controllers\Product\ReportTaskController')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\ReportTaskFilters');
        $this->app->when('App\Models\ReportTask')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\ReportTaskFilters');

        /* Dashboard Query Filters */
        $this->app->when('App\Http\Controllers\Dashboard\DashboardController')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\DashboardFilters');
        $this->app->when('App\Models\Dashboard')
            ->needs('App\Filters\QueryFilter')
            ->give('App\Filters\DashboardFilters');


        /*************************************************************************
         *                                                                       *
         * CRAWLER AND PARSER CLASSES DYNAMIC BINDING BASED ON DATABASE RECORD   *
         *                                                                       *
         * ***********************************************************************
         */
        /* dynamic binding for crawler */

        $this->app->bind('Invigor\Crawler\Contracts\CrawlerInterface', 'Invigor\Crawler\Repositories\Crawlers\DefaultCrawler');
        $this->app->bind('Invigor\Crawler\Contracts\ParserInterface', 'Invigor\Crawler\Repositories\Parsers\XPathParser');
    }
}
