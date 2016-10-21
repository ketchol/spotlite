<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 9/12/2016
 * Time: 5:19 PM
 */

namespace App\Models;


use App\Models\DeletedRecordModels\DeletedCrawler;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Crawler extends Model
{
    protected $primaryKey = "crawler_id";
    protected $fillable = [
        "crawler_class", "parser_class", "status", "site_id", "cookie_id", "active_at"
    ];
    public $timestamps = false;

    public function site()
    {
        return $this->belongsTo('App\Models\Site', 'site_id', 'site_id');
    }

    public function activityLogs()
    {
        return $this->hasMany('App\Models\Logs\CrawlerActivityLog', 'crawler_id', 'crawler_id');
    }

    public function pick()
    {
        $this->status = "picked";
        $this->save();
    }

    public function queue()
    {
        $this->status = "queuing";
        $this->save();
    }

    public function run()
    {
        $this->status = "running";
        $this->save();
    }

    public function resetStatus()
    {
        $this->status = null;
        $this->save();
    }

    public function updateLastActiveAt()
    {
        $this->last_active_at = new Carbon;
        $this->save();
    }

    public function lastCrawlerWithinHour($hour = 1)
    {
        $hourDiff = (strtotime(date('Y-m-d H:00:00', strtotime($this->site->last_crawled_at))) - strtotime(date('Y-m-d H:00:00'))) / 3600;
        return $hourDiff < $hour;
    }
}