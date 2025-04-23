<?php

namespace App\DataTables\Admin;

use App\Models\Lesson;
use App\Models\Purchase;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->smart(false)
            ->addIndexColumn()
            ->filterColumn('lesson_name', function ($query, $keyword) {
                $query->where('lessons.lesson_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('instructor_name', function ($query, $keyword) {
                $query->where('instructors.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('follower_name', function ($query, $keyword) {
                $query->where('followers.name', 'like', "%{$keyword}%");
            })
            ->editColumn('instructor_name', function ($purchase) {
                $imageSrc = $purchase->instructor->dp
                    ? asset('/storage' . '/' . tenant('id') . '/' . $purchase->instructor->dp)
                    : asset('assets/img/logo/logo.png');

                return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-0">' . e($purchase->instructor_name) . '</span>
                    </div>';
            })
            ->editColumn('lesson_name', function ($purchase) {
                $s = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? 'N/A';
                $lesson_type = $purchase->lesson->type ?? null;

                if ($lesson_type == Lesson::LESSON_TYPE_INPERSON && $purchase->lesson->is_package_lesson) {
                    $s .= ' - PL'; // Append "- PL" for package lessons
                }

                $lesson_active_status = $purchase->lesson->active_status;
                $badgeClass = $lesson_type == Lesson::LESSON_TYPE_ONLINE ? 'bg-green-600' : 'bg-cyan-500';

                // Check if lesson is deleted
                $deletedText = !$lesson_active_status ? ' <span class="text-gray-500 italic"> deleted</span>' : '';

                // Truncate lesson name if it exceeds 16 characters
                $lessonName = e($purchase->lesson_name);
                $truncatedLessonName = strlen($lessonName) > 20 ? substr($lessonName, 0, 20) . '...' : $lessonName;

                return '
                    <div class="flex justify-between">
                        <span class="mr-2" title="' . $lessonName . '">' . $truncatedLessonName . $deletedText . '</span>
                        <label class="badge rounded-pill ' . $badgeClass . ' p-2 px-3">' . e($s) . '</label>
                    </div>';
            })
            ->editColumn('follower_name', function ($purchase) {
                $imageSrc = $purchase->student->dp
                    ? asset('/storage' . '/' . tenant('id') . '/' . $purchase->student->dp)
                    : asset('assets/img/logo/logo.png');

                return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-0">' . e($purchase->follower_name) . '</span>
                    </div>';
            })
            ->addColumn('status', function ($purchase) {
                $s = Purchase::STATUS_MAPPING[$purchase->status] ?? 'Unknown';
                $statusClass = $purchase->status == Purchase::STATUS_COMPLETE ? 'bg-green-600' : 'bg-red-600';

                return '<label class="badge rounded-pill ' . $statusClass . ' p-2 px-3">' . e($s) . '</label>';
            })
            ->editColumn('due_date', function ($purchase) {
                return Carbon::parse($purchase->created_at)->toFormattedDateString();
            })
            ->addColumn('action', function ($purchase) {
                return view('admin.purchases.action', compact('purchase'));
            })
            ->rawColumns(['action', 'status', 'follower_name', 'instructor_name', 'lesson_name']);
    }


    public function query(Purchase $model)
    {
        $user = Auth::user();

        $query = $model->newQuery()
            ->select([
                'purchases.*',  // Select all purchase fields
                'lessons.lesson_name as lesson_name',  // Get lesson name
                'instructors.name as instructor_name', // Get instructor name
                'followers.name as follower_name' // Get student name
            ])
            ->join('lessons', 'purchases.lesson_id', '=', 'lessons.id')
            ->join('users as instructors', 'purchases.instructor_id', '=', 'instructors.id')
            ->join('followers as followers', 'purchases.follower_id', '=', 'followers.id')
            ->orderBy('purchases.created_at', 'desc'); // Order by creation date in descending order

        // Filter query based on user role
        if ($user->type == Role::ROLE_STUDENT) {
            $query->where('purchases.follower_id', $user->id)
                ->where('purchases.status', Purchase::STATUS_COMPLETE);
        }

        if ($user->type == Role::ROLE_ADMIN) {
            $query->where(function ($q) {
                $q->whereHas('lesson', function ($subQuery) {
                    $subQuery->where('is_package_lesson', true)
                        ->orWhere('type', 'online');
                })->where('status', 'complete')
                    ->orWhere(function ($subQ) {
                        $subQ->whereHas('lesson', function ($lessonQ) {
                            $lessonQ->where('type', 'inPerson')
                                ->where('is_package_lesson', false);
                        })->whereIn('status', ['complete', 'incomplete']);
                    });
            });
        }

        if ($user->type == Role::ROLE_INSTRUCTOR) {
            $query->where('purchases.instructor_id', $user->id)
                ->where('purchases.status', Purchase::STATUS_COMPLETE);
        }

        return $query;
    }




    public function html()
    {
        $buttons = [
            ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
            ['extend' => 'reload', 'className' => 'btn btn-light-warning'],
        ];
        if (Auth::user()->type == Role::ROLE_INSTRUCTOR) {
            unset($buttons[0]);
        }

        return $this->builder()
            ->setTableId('purchases-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => __('_MENU_ entries per page'),
                "searchPlaceholder" => __('Search'),
                'search' => ''
            ])
            ->initComplete('function() {
                var table = this;
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
            }')
            ->parameters([
                "dom" =>  "
                <'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12'>
                <'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                <'dataTable-container'<'col-sm-12'tr>>
                <'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                <'col-sm-7'p>>
                ",
                'buttons'   => $buttons,
                "scrollX" => true,
                'headerCallback' => 'function(thead, data, start, end, display) {
                    $(thead).find("th").css({
                        "background-color": "rgba(249, 252, 255, 1)",
                        "font-weight": "400",
                        "font":"sans",
                        "border":"none",
                    });
                }',
                'rowCallback' => 'function(row, data, index) {
                    // Make the first column bold
                    $("td", row).css("font-family", "Helvetica");
                    $("td", row).css("font-weight", "300");
                }',
                "drawCallback" => 'function( settings ) {
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
                }'
            ])->language([
                'buttons' => [
                    'create' => __('Choose Your Coach'),
                    'print' => __('Print'),
                    'reset' => __('Reset'),
                    'reload' => __('Reload'),
                    'excel' => __('Excel'),
                    'csv' => __('CSV'),
                ]
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('No')->title(__('Lesson Number'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('lesson_name')->title(__('Lesson'))->searchable(false),
            Column::make('instructor_name')->title(__('Instructor'))->searchable(true),
            Column::make('follower_name')->title("Student")->searchable(true),
            Column::make('status')->title(__('Payment Status')),
            Column::make("due_date")->title(__('Submission Date'))->defaultContent()->orderable(false)->searchable(false),
            Column::make('total_amount')->title(__('Total ($)'))->orderable(false),
            Column::computed('action')->title(__('Actions'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center')
                ->width('20%'),
        ];
    }

    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}
