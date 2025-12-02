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
            ->filter(function ($query) {
                if (request()->has('status') && request('status') !== '') {
                    if (request('status') === 'pending') {
                        $query->where('purchases.isFeedbackComplete', 0);
                    }

                    if (request('status') === 'completed') {
                        $query->where('purchases.isFeedbackComplete', 1);
                    }
                }
                if (request()->has('search') && !empty(request('search')['value'])) {
                    $search = request('search')['value'];

                    $query->where(function ($q) use ($search) {
                        $q->where('lessons.lesson_name', 'like', "%{$search}%")
                            ->orWhere('influencers.name', 'like', "%{$search}%")
                            ->orWhere('followers.name', 'like', "%{$search}%")
                            ->orWhere('purchases.status', 'like', "%{$search}%");
                    });
                }
            })
            ->smart(false)
            ->addIndexColumn()
            ->filterColumn('lesson_name', function ($query, $keyword) {
                $query->where('lessons.lesson_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('influencer_name', function ($query, $keyword) {
                $query->where('influencers.name', 'like', "%{$keyword}%");
            })
            ->filterColumn('follower_name', function ($query, $keyword) {
                $query->where('followers.name', 'like', "%{$keyword}%");
            })
            ->editColumn('influencer_name', function ($purchase) {
                $imageSrc = $purchase->influencer->logo
                    ? $purchase->influencer->logo
                    : asset('assets/img/logo/logo.png');

                return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-0">' . e($purchase->influencer_name) . '</span>
                    </div>';
            })
            ->editColumn('lesson_name', function ($purchase) {
                $s                    = Lesson::TYPE_MAPPING[$purchase->lesson->type] ?? 'N/A';
                $lesson_type          = $purchase->lesson->type ?? null;
                $lesson_active_status = $purchase->lesson->active_status;
                $badgeClass           = $lesson_type == Lesson::LESSON_TYPE_ONLINE ? 'bg-green-600' : 'bg-cyan-500';
                $deletedText          = ! $lesson_active_status ? ' <span class="text-gray-500 italic"> deleted</span>' : '';
                $lessonName           = e($purchase->lesson_name);
                $truncatedLessonName  = strlen($lessonName) > 20 ? substr($lessonName, 0, 20) . '...' : $lessonName;

                $url = route('purchase.feedback.index', ['purchase_id' => $purchase->id]);

                // Check user role
                if (Auth::user()->type == 'Influencer') {
                    $lessonLink = '<a href="' . $url . '" class="text-blue-600 hover:underline mr-2" title="' . $lessonName . '">' . $truncatedLessonName . '</a>';
                } else {
                    $lessonLink = '<span class="text-gray-800 mr-2" title="' . $lessonName . '">' . $truncatedLessonName . '</span>';
                }

                return '
                    <div class="flex justify-between items-center">
                        ' . $lessonLink .  $deletedText . '
                    </div>';
            })
            ->editColumn('follower_name', function ($purchase) {
                $imageSrc = $purchase->follower->dp
                    ? $purchase->follower->dp
                    : asset('assets/img/logo/logo.png');

                return '
                    <div class="flex justify-start items-center">
                        <img src="' . $imageSrc . '" width="20" class="rounded-full"/>
                        <span class="px-0">' . e($purchase->follower_name) . '</span>
                    </div>';
            })
            ->addColumn('status', function ($purchase) {
                $s = Purchase::STATUS_MAPPING[$purchase->status] ?? 'Unknown';
                // Inline styles for modal compatibility (from StudentPurchaseDataTable)
                $statusStyle = $purchase->status == Purchase::STATUS_COMPLETE
                    ? 'background-color: #16A34A; color: white; padding: .25rem .75rem; border-radius: 624.9375rem; display: inline-block; font-size: .875rem;'
                    : 'background-color: #DC2626; color: white; padding: .25rem .75rem; border-radius: 624.9375rem; display: inline-block; font-size: .875rem;';
                return '<span style="' . $statusStyle . '">' . e($s) . '</span>';
            })
            ->editColumn('due_date', function ($purchase) {
                return Carbon::parse($purchase->created_at)->toFormattedDateString();
            })
            ->addColumn('action', function ($purchase) {
                return view('admin.purchases.action', compact('purchase'));
            })
            ->rawColumns(['action', 'status', 'follower_name', 'influencer_name', 'lesson_name']);
    }

    public function query(Purchase $model)
    {
        $user = Auth::user();

        $query = $model->newQuery()
            ->select([
                'purchases.*',                         // Select all purchase fields
                'lessons.lesson_name as lesson_name',  // Get lesson name
                'influencers.name as influencer_name', // Get influencer name
                'followers.name as follower_name',     // Get follower name
            ])
            ->join('lessons', 'purchases.lesson_id', '=', 'lessons.id')
            ->join('users as influencers', 'purchases.influencer_id', '=', 'influencers.id')
            ->join('followers as followers', 'purchases.follower_id', '=', 'followers.id')
            ->groupBy('purchases.id')
            ->orderBy('purchases.created_at', 'desc'); // Order by creation date in descending order

        // Filter query based on user role
        if ($user->type == Role::ROLE_FOLLOWER) {
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

        if ($user->type == Role::ROLE_INFLUENCER) {
            $query->where('purchases.influencer_id', $user->id)
                ->where('purchases.status', Purchase::STATUS_COMPLETE);
        }

        return $query;
    }

    public function html()
    {
        // $lessonTypeFilter = "<select id='lessonTypeFilter' class='form-select' style='margin-left:auto; max-width: 12.5rem;'><option value=''>- Lesson Type -</option>";
        // // foreach (Lesson::SELECT_TYPE_MAPPING as $key => $label) {
        // //     $lessonTypeFilter .= "<option value='" . $key . "'>" . $label . "</option>";
        // // }
        // $lessonTypeFilter .= "</select>";

        $buttons = [
            // ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
            // ['extend' => 'reload', 'className' => 'btn btn-light-warning'],
        ];
        if (Auth::user()->type == Role::ROLE_INFLUENCER) {
            unset($buttons[0]);
        }

        return $this->builder()
            ->setTableId('purchases-table')
            ->addTableClass('display responsive nowrap')
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
                    var searchInput = $(\'#\' + table.api().table().container().id + \' label input[type="search"]\');
                    searchInput.removeClass(\'form-control form-control-sm\').addClass(\'dataTable-input\');

                    var select = $(table.api().table().container())
                        .find(".dataTables_length select")
                        .removeClass(\'custom-select custom-select-sm form-control form-control-sm\')
                        .addClass(\'dataTable-selector\');

                    $(".dataTable-search").prepend("' . $lessonTypeFilter . '").addClass("d-flex");

                    $("#lessonTypeFilter").on("change", function() {
                        table.api().ajax.reload();
                    });

                    $("#purchases-table").DataTable().on("preXhr.dt", function(e, settings, data) {
                        data.lesson_type = $("#lessonTypeFilter").val();
                    });
                }')
            ->parameters([
                "columnDefs" => [
                    ["responsivePriority" => 1, "targets" => 1],
                    ["responsivePriority" => 2, "targets" => 4],
                ],
                "dom" => "
                        <'dataTable-top row'
                            <'dataTable-title col-xl-7 col-lg-3 col-sm-6 d-none d-sm-block'>
                            <'dataTable-search dataTable-search tb-search col-md-5 col-sm-6 col-lg-6 col-xl-5 col-sm-12 d-flex'f>
                        >
                        <'dataTable-container'<'col-sm-12'tr>>
                        <'dataTable-bottom row'
                            <'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                            <'col-sm-7'p>
                        >
                    ",
                "buttons" => $buttons,
                "scrollX" => true,
                "responsive" => [
                    "details" => [
                        "display" => "$.fn.dataTable.Responsive.display.childRow",
                       "renderer" => "function (api, rowIdx, columns) {
    console.log('Renderer called for rowIdx:', rowIdx); 

    var data = columns.map(function(col) {
        switch(col.title) {
            case 'Submission #':
            case 'Status':
                return '';
            default:
                return '<tr><td style=\"font-weight: bold; padding: 5px;\">' + col.title + ':</td><td style=\"padding: 5px;\">' + (col.data || '-') + '</td></tr>';
        }
    }).join('');

    var rowData = api.row(rowIdx).data();
    console.log('rowData:', rowData); 

    var content = '<div style=\"padding: 0px;\"><table style=\"width: 100%; border-collapse: collapse;\">' + data + '</table></div>';

    Swal.fire({
        title: 'Purchase Details',
        html: content,
        showCloseButton: true,
        showConfirmButton: false,
        customClass: {
            container: 'swal2-responsive-container',
            popup: 'swal2-responsive-popup'
        }
    });

    return false; // Prevent default child row rendering
}"

                    ]
                ],
                "rowCallback" => 'function(row) {
                        $("td", row).css({"font-family":"Helvetica", "font-weight":"300"});
                        $(row).addClass("custom-parent-row");
                    }',
                "headerCallback" => 'function(thead) {
                        $(thead).find("th").css({
                            "background-color": "rgba(249, 252, 255, 1)",
                            "font-weight": "400",
                            "font": "sans",
                            "border": "none"
                        });
                    }',
                "drawCallback" => 'function() {
                        var tooltipTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=tooltip]"));
                        tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el); });

                        var popoverTriggerList = [].slice.call(document.querySelectorAll("[data-bs-toggle=popover]"));
                        popoverTriggerList.map(function (el) { return new bootstrap.Popover(el); });

                        var toastElList = [].slice.call(document.querySelectorAll(".toast"));
                        toastElList.map(function (el) { return new bootstrap.Toast(el); });
                    }'
            ])
            ->language([
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
        $columns = [
            Column::make('No')
                ->title(__('Submission #'))
                ->data('DT_RowIndex')
                ->name('DT_RowIndex')
                ->searchable(false)
                ->orderable(false)
                ->addClass('min-desktop'), // always visible

            Column::make('lesson_name')
                ->title(__('Lesson'))
                ->searchable(true)
                ->addClass('all'), // hide on phones, show on tablet/desktop

            Column::make('status')
                ->title(__('Payment Status'))
                ->addClass('min-tablet'),
        ];

        if (Auth::user()->type == Role::ROLE_INFLUENCER) {
            $columns[] = Column::make('follower_name')->title("Follower")->searchable(true)->addClass('min-tablet');
        } elseif (Auth::user()->type == Role::ROLE_FOLLOWER) {
            $columns[] = Column::make('influencer_name')->title(__('Influencer'))->searchable(true)->addClass('min-tablet');
        }

        return array_merge($columns, [
            Column::make("due_date")
                ->title(__('Submission Date'))
                ->defaultContent()
                ->orderable(false)
                ->searchable(false)
                ->addClass('all'), // always visible even on mobile

            Column::make('total_amount')
                ->title(__('Total ($)'))
                ->orderable(false)
                ->addClass('min-tablet'),

            Column::computed('action')
                ->title(__('Actions'))
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('min-desktop')
                ->width('20%'),
        ]);
    }


    protected function filename(): string
    {
        return 'Purchases_' . date('YmdHis');
    }
}