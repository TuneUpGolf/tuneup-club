<?php

namespace App\Http\Livewire;

use App\Models\Follow;
use App\Models\Lesson;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use LaravelViews\Views\GridView;

class InstructorGridView extends GridView
{

    /**
     * Sets a initial query with the data to fill the grid view
     *
     * @return Builder Eloquent query
     */
    public function repository(): Builder
    {
        return User::where('type', Role::ROLE_INFLUENCER);
    }

    public $maxCols = 3;

    /**
     * Sets the data to every card on the view
     *
     * @param $model Current model for each card
     */
    public $cardComponent = 'admin.instructors.card';

    public function card($model)
    {
        return [
            'image'       => asset('/storage' . '/' . tenant('id') . '/' . $model->logo) ?? asset('assets/img/logo/logo.png'),
            'follow'      => Follow::where('influencer_id', $model->id)?->where('student_id', Auth::user()->id)->first()?->active_status,
            'followers'   => Follow::where('influencer_id', $model->id)->where('active_status', true)->count(),
            'subscribers' => Follow::where('influencer_id', $model->id)->where('active_status', true)->where('isPaid', true)->count(),
        ];
    }
}
