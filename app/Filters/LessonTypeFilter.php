<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use LaravelViews\Filters\Filter;

class LessonTypeFilter extends Filter
{
    /**
     * Modify the current query when the filter is used
     *
     * @param Builder $query Current query
     * @param $value Value selected by the user
     * @return Builder Query modified
     */

    public $title =  "Type";
    public function apply(Builder $query, $value): Builder
    {
        return $query->where('type', $value);
    }

    /**
     * Defines the title and value for each option
     *
     * @return Array associative array with the title and values
     */
    public function options(): array
    {
        return [
            'InPerson' => 'inPerson',
            'Online'    => 'online'
        ];
    }
}
