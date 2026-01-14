<?php

namespace App\DataTables;

use App\Models\Role;
use App\Models\Announcement;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;

class AnnouncementDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
     public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($announcement) {
                return view('admin.announcements.action', compact('announcement'));
            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Announcement $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Announcement $model): QueryBuilder
    {
        $user = Auth::user();
        // return $model->newQuery();
        if ($user->type == Role::ROLE_FOLLOWER) {
        // For students: show only announcements where they are recipients
            return $model->newQuery()
                ->whereHas('recipients', function ($query) use($user) {
                    $query->where('follower_id', $user->id);
                });
                // ->orWhereHas('recipients'); // Optional: include announcements with any recipients
        }

        else if ($user->type == Role::ROLE_INFLUENCER) {
            return $model->where('user_id',$user->id)->newQuery();
        }
        
        // For admins/teachers: show all announcements
        // return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
   public function html()
    {
        return $this->builder()
            ->setTableId('announcements-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
           ->parameters([
            'dom'        => "<'row'<'col-12'f>>" .
                            "<'row'<'col-12'tr>>" .
                            "<'row'<'col-12 col-md-5'i><'col-12 col-md-7'p>>",
            'stateSave'  => true,
            'buttons'    => [],
            'responsive' => true,
            'scrollX'    => true,
        ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    protected function getColumns()
    {
         return [
            // Column::make('id'),
          
            Column::make('title')->title(__('Title')),
            Column::make('content')->title(__('Content')),
            // Column::make('is_active')->title(__('Status')),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(120)
                ->addClass('text-center'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'Announcement_' . date('YmdHis');
    }
}
