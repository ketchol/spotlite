<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 9/5/2016
 * Time: 2:08 PM
 */

namespace App\Models;


use App\Filters\QueryFilter;
use App\Models\DeletedRecordModels\DeletedCategory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $primaryKey = "category_id";
    protected $fillable = [
        "category_name", "user_id", "category_order", "report_task_id"
    ];
    public $timestamps = false;
    protected $appends = ["urls"];

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'user_id');
    }

    public function products()
    {
        return $this->hasMany('App\Models\Product', 'category_id', 'category_id');
    }

    public function alerts()
    {
        return $this->morphMany('App\Models\Alert', 'alert_owner', 'alert_owner_type', 'alert_owner_id', 'category_id');
    }

    public function scopeFilter($query, QueryFilter $filters)
    {
        return $filters->apply($query);
    }

    /**
     * back up category before deleting
     * @return bool|null
     */
    public function delete()
    {
        DeletedCategory::create(array(
            "content" => $this->toJson()
        ));
        return parent::delete(); // TODO: Change the autogenerated stub
    }


    public function getUrlsAttribute()
    {
        return array(
            "show" => route("category.show", $this->getKey()),
            "delete" => route("category.destroy", $this->getKey()),
        );
    }
}