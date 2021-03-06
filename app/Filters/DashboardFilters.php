<?php
/**
 * Created by PhpStorm.
 * User: ivan.li
 * Date: 10/13/2016
 * Time: 10:50 AM
 */

namespace App\Filters;


class DashboardFilters extends QueryFilter
{
    /**
     * Setting the offset of the query
     * @param $numberOfRows
     * @return mixed
     */
    public function start($numberOfRows)
    {
        return $this->builder->skip($numberOfRows);
    }

    /**
     * Setting the length of each page
     * @param $numberOfRows
     * @return mixed
     */
    public function length($numberOfRows)
    {
        return $this->builder->take($numberOfRows);
    }

    /**
     * search logs by activity and first name + last name
     * @param $keyWord
     * @return mixed
     */
    public function search($keyWord)
    {
        return $this->builder->where('dashboard_name', 'LIKE', "%{$keyWord['value']}%");
    }

    /**
     * order by columns and directions, accept multiple columns ordering
     * @param $columnsAndDirections
     * @return mixed
     */
    public function order($columnsAndDirections)
    {
        foreach ($columnsAndDirections as $columnAndDirection) {
            $this->builder->orderBy($columnAndDirection['column'], $columnAndDirection['dir']);
        }
        return $this->builder;
    }
}