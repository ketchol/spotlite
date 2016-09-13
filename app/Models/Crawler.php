<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 9/12/2016
 * Time: 5:19 PM
 */

namespace App\Models;


use App\Models\DeletedRecordModels\DeletedCrawler;
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

    /**
     * back up category before deleting
     * @return bool|null
     */
    public function delete()
    {
        DeletedCrawler::create(array(
            "content" => $this->toJson()
        ));
        return parent::delete(); // TODO: Change the autogenerated stub
    }
}