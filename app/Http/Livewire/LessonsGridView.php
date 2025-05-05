<?php
namespace App\Http\Livewire;

use App\Models\Lesson;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use LaravelViews\Views\GridView;

class LessonsGridView extends GridView
{
    /**
     * Sets a model class to get the initial data
     */
    public function repository(): Builder
    {
        $query = Lesson::where('active_status', true);

        if (request()->query('influencer_id')) {
            $query->where('created_by', request()->query('influencer_id'));
        } elseif (request()->query('type') == Lesson::LESSON_TYPE_ONLINE) {
            $query->where('type', Lesson::LESSON_TYPE_ONLINE)
                ->whereHas('user', function ($q) {
                    $q->where('is_stripe_connected', true);
                });
        } elseif (request()->query('type') == Lesson::LESSON_TYPE_INPERSON) {
            $query->where('type', Lesson::LESSON_TYPE_INPERSON)
                ->where(function ($q) {
                    $q->where('payment_method', '!=', 'online')
                        ->orWhereHas('user', function ($q) {
                            $q->where('is_stripe_connected', true);
                        });
                });
        }

        return $query;
    }

    public $maxCols = 4;
    // protected $paginate = 4;

    /**
     * Sets the data to every card on the view
     *
     * @param $model Current model for each card
     */
    public $cardComponent = 'admin.lessons.card';

    public function card($model)
    {
        $model->load('user');
        $tenantId = tenancy()->tenant->id;
        $currency = tenancy()->central(function () use ($model, $tenantId) {
            return User::where('tenant_id', $tenantId)->value('currency');
        });

        $symbol = User::getCurrencySymbol($currency);

        return [
            'image' =>  asset('/storage' . '/' . tenant('id') . '/' . $model?->user?->logo) ?? asset('assets/img/logo/logo.png'),
            'title' => $model->lesson_name,
            'subtitle' => str_replace(['(', ')'], '', $symbol) . ' ' . $model->lesson_price . ' (' . strtoupper($currency) . ')',
            'description' => $model->lesson_description,
        ];
    }

    public function render()
    {
        $lessons = $this->repository()->get();

        if ($lessons->isEmpty()) {
            return view('admin.lessons.nolesson');
        }

        return parent::render();
    }
}
