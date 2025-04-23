<?php
namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\Post;
use App\Models\ReportPost;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ReportedPostDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('title', function (ReportPost $post) {
                return $post->post->title;
            })
            ->editColumn('created_at', function ($request) {
                $created_at = UtilityFacades::date_time_format($request->created_at);
                return $created_at;
            })
            ->addColumn('action', function (ReportPost $report) {
                $post = $report->post;
                return view('admin.posts.reportAction', compact('post'));
            })
            ->editColumn('comment', function (ReportPost $post) {
                return isset($post->comment) ? $post->comment : "No Comment";
            })
            ->editColumn('reported_by', function (ReportPost $post) {
                return isset($post->influencer_id) ? $post->instructor->name . " - Instructor " : $post->student->name . " - Student ";
            })
            ->editColumn("photo", function (ReportPost $post) {
                if ($post->post->file) {
                    $imageSrc = asset('/storage' . '/' . tenant('id') . '/' . $post->post->file);
                    return "<img src=' " . $imageSrc . " ' width='50'/>";
                } else {
                    $return = "<img src='" . asset('/storage' . '/' . tenant('id') . '/seeder-image/350x250.png') . "' width='50' />";
                }
                return $return;
            })
            ->rawColumns(['action', 'photo']);
    }

    public function query(ReportPost $model)
    {
        if (Auth::user()->type == Role::ROLE_ADMIN) {
            return $model->newQuery();
        }

        if (Auth::user()->type == Role::ROLE_INFLUENCER) {
            return $model->newQuery()->where('influencer_id', Auth::user()->id);
        }

        return $model->newQuery()->where('student_id', Auth::user()->id);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('users-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->language([
                "paginate"          => [
                    "next"     => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>',
                ],
                'lengthMenu'        => __('_MENU_ entries per page'),
                "searchPlaceholder" => __('Search...'),
                "search"            => "",
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }')
            ->parameters([
                "dom"            => "
                <'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12'>
                <'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                <'dataTable-container'<'col-sm-12'tr>>
                <'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                <'col-sm-7'p>>
                ",
                'buttons'        => [
                    ['extend' => 'create', 'className' => 'btn btn-light-primary no-corner me-1 add_module', 'action' => " function ( e, dt, node, config ) {
                        window.location = '" . route('blogs.create') . "';
                   }", ],
                    [
                        'extend'    => 'collection',
                        'className' => 'btn btn-light-secondary me-1 dropdown-toggle',
                        'text'      => '<i class="ti ti-download"></i> Export',
                        "buttons"   => [
                            ["extend" => "print", "text" => '<i class="fas fa-print"></i> Print', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "csv", "text" => '<i class="fas fa-file-csv"></i> CSV', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "excel", "text" => '<i class="fas fa-file-excel"></i> Excel', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "pdf", "text" => '<i class="fas fa-file-pdf"></i> PDF', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                        ],
                    ],
                    ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
                    ['extend' => 'reload', 'className' => 'btn btn-light-warning'],
                ],
                "scrollX"        => true,
                'headerCallback' => 'function(thead, data, start, end, display) {
                    $(thead).find("th").css({
                        "background-color": "rgba(249, 252, 255, 1)",
                        "font-weight": "400",
                        "font":"sans",
                        "border":"none",
                    });
                }',
                'rowCallback'    => 'function(row, data, index) {
                    // Make the first column bold
                    $("td", row).css("font-family", "Helvetica");
                    $("td", row).css("font-weight", "300");
                }',
                "drawCallback"   => 'function( settings ) {
                    var tooltipTriggerList = [].slice.call(
                        document.querySelectorAll("[data-bs-toggle=tooltip]")
                      );
                      var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl);
                      });
                      var popoverTriggerList = [].slice.call(
                        document.querySelectorAll("[data-bs-toggle=popover]")
                      );
                      var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                        return new bootstrap.Popover(popoverTriggerEl);
                      });
                      var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                      var toastList = toastElList.map(function (toastEl) {
                        return new bootstrap.Toast(toastEl);
                      });
                }',
            ])->language([
            'buttons' => [
                'create' => __('Create'),
                'export' => __('Export'),
                'print'  => __('Print'),
                'reset'  => __('Reset'),
                'reload' => __('Reload'),
                'excel'  => __('Excel'),
                'csv'    => __('CSV'),
            ],
        ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::computed('reported_by')->title(__('Reported By')),
            Column::computed('title')->title(__('Title')),
            Column::make('photo')->title(__('Photo')),
            Column::make('comment')->title(__('Comment')),
            Column::make('created_at')->title(__('Created At')),
            Column::computed('action')->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->width('20%'),
        ];
    }

    protected function filename(): string
    {
        return 'Post_' . date('YmdHis');
    }
}
