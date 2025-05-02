<?php
namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\PurchaseVideos;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseVideoDataTable extends DataTable
{



    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('purchase_id', function (PurchaseVideos $purchaseVideo) {

                return $purchaseVideo->purchase_id;
            })
            ->editColumn('influencer_id', function () {
                $instructor_name = User::find($this->purchase->influencer_id);
                return $instructor_name->name;
            })
            ->editColumn('video', function (PurchaseVideos $purchaseVideo) {
                $video = $purchaseVideo;
                return view('admin.purchases.renderVideo', compact('video'));
            })
            ->editColumn('feedback', function (PurchaseVideos $purchaseVideo) {
                $feedback = $purchaseVideo->feedback ? $purchaseVideo->feedback : "Feedback pending";
                return $feedback;
            })
            ->editColumn('created_at', function ($request) {
                $created_at = UtilityFacades::date_time_format($request->created_at);
                return $created_at;
            })
            ->addColumn('action', function (PurchaseVideos $purchaseVideo) {
                return view('admin.purchases.purchaseVideoAction', compact('purchaseVideo'));
            })
            ->rawColumns(['action', 'logo_image']);
    }

    public function query(PurchaseVideos $model)
    {
        return $model->newQuery()->where('purchase_id', $this->purchase->id);
    }


    protected function getColumns()
    {
        $columns = [
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('purchase_id')->title(__('Purchase')),
            Column::make('influencer_id')->title(__('Instructor Name')),
            Column::make('video')->title(__('Video'))->searchable(false),
            Column::make('feedback')->title(__('Feedback')),
            Column::make('created_at')->title(__('Created At')),
            Column::computed('action')->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->width('20%'),
        ];

        if (Auth::user()->type == Role::ROLE_FOLLOWER) {
            unset($columns[6]);
        }

        return $columns;
    }

    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}
