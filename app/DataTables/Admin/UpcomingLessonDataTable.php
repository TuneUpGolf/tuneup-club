<?php
namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\Lesson;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class UpcomingLessonDataTable extends DataTable
{
    public function dataTable($query)
    {

        $data = datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($request) {
                return UtilityFacades::date_time_format($request->created_at);
            })
            ->editColumn('lesson_price', function (Lesson $lesson) {
                return UtilityFacades::amount_format($lesson->lesson_price);
            })
            ->editColumn('created_by', function (Lesson $lesson) {
                $imageSrc = $lesson?->user?->dp ? asset('/storage' . '/' . tenant('id') . '/' . $lesson?->user?->dp) : asset('assets/img/logo/logo.png');
                $html     =
                '
                <div class="flex justify-start items-center">'
                .
                "<img src=' " . $imageSrc . " ' width='20' class='rounded-full'/>"
                .
                "<span class='px-0'>" . $lesson->user->name . " </span>" .
                    '</div>';
                return $html;
            })
            ->editColumn('type', function (Lesson $lesson) {
                $s = Lesson::TYPE_MAPPING[$lesson->type];

                if ($lesson->type == Lesson::LESSON_TYPE_ONLINE) {
                    return '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . $s . '</label>';
                }
                return '<label class="badge rounded-pill bg-green-600 p-2 px-3">' . $s . '</label>';
            })
            ->addColumn('action', function (Lesson $lesson) {
                return view('admin.lessons.action', compact('lesson'));
            })
            ->rawColumns(['action', 'logo_image', 'created_by', 'type']);
        return $data;
    }

    public function query(Lesson $model)
    {
        if (tenant('id') == null) {
            return $model->newQuery()->select(['lessons.*', 'domains.domain'])
                ->join('domains', 'domains.tenant_id', '=', 'users.tenant_id')->where('type', 'Admin')->where('created_at', '>', now());
        } else if (Auth::user()->type == Role::ROLE_ADMIN || Auth::user()->type == Role::ROLE_FOLLOWER) {
            return $model->newQuery()->where('tenant_id', '=', tenant('id'))->where('active_status', true)->where('created_at', '>', now());
        } else {
            return $model->newQuery()->where('created_by', '=', Auth::user()->id)->where('created_at', '>', now());
        }
    }

    public function html()
    {

        return $this->builder()
            ->setTableId('lessons-table')
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
                'buttons'        => [],
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
        ]);
    }

    protected function getColumns()
    {

        return [
            Column::make('No')->title(__('#'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('lesson_name')->title(__('Name')),
            Column::make('lesson_price')->title(__('Price')),
            Column::make('lesson_quantity')->title(__('quantity')),
            Column::make('created_at')->title(__('Created At')),
            Column::make('type')->title(__('Type')),
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
        return 'Lessons_' . date('YmdHis');
    }
}
