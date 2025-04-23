<?php

namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\Student;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StudentDataTable extends DataTable
{
    public function dataTable($query)
    {

        $data = datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('name', function (Student $user) {
                $imageSrc = $user->dp ?  asset('/storage' . '/' . tenant('id') . '/' . $user->dp) : asset('assets/img/user.png');
                $html =
                    '
                <div class="flex justify-start items-center">'
                    .
                    "<img src=' " . $imageSrc . " ' width='20' class='rounded-full'/>"
                    .
                    "<span class='pl-2'>" . $user->name . " </span>" .
                    '</div>';
                return $html;
            })
            ->editColumn('created_at', function ($request) {
                return UtilityFacades::date_time_format($request->created_at);
            })
            ->editColumn('email_verified_at', function (Student $user) {
                if ($user->email_verified_at) {
                    $html = '
                    <div class="flex justify-center items-center">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_205_1682)">
                    <path d="M0.820312 7.36721C2.63193 9.47814 4.38846 11.3785 6.07693 13.7821C7.91268 9.85002 9.79158 5.90432 12.8918 1.63131L12.0564 1.21924C9.43865 4.209 7.40486 7.03908 5.63768 10.4024C4.40877 9.21018 2.42271 7.52307 1.21006 6.65627L0.820312 7.36721Z" fill="#16DBAA"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_205_1682">
                    <rect width="13" height="14" fill="white" transform="translate(0.376953 0.506836)"/>
                    </clipPath>
                    </defs>
                    </svg>
                    <span class="text-verified pl-1">'
                        . __('Verified') .
                        '</span>
                        </div>
                        ';
                    return $html;
                } else {
                    $html = '
                    <div class="flex justify-center items-center">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_205_1682)">
                    <path d="M0.820312 7.36721C2.63193 9.47814 4.38846 11.3785 6.07693 13.7821C7.91268 9.85002 9.79158 5.90432 12.8918 1.63131L12.0564 1.21924C9.43865 4.209 7.40486 7.03908 5.63768 10.4024C4.40877 9.21018 2.42271 7.52307 1.21006 6.65627L0.820312 7.36721Z" fill="#16DBAA"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_205_1682">
                    <rect width="13" height="14" fill="white" transform="translate(0.376953 0.506836)"/>
                    </clipPath>
                    </defs>
                    </svg>
                    <span class="text-verified pl-1">'
                        . __('UnVerified') .
                        '</span>
                        </div>
                        ';
                    return $html;
                }
            })
            ->editColumn('phone_verified_at', function (Student $user) {
                if ($user->phone_verified_at) {
                    $html = '
                    <div class="flex justify-center items-center">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_205_1682)">
                    <path d="M0.820312 7.36721C2.63193 9.47814 4.38846 11.3785 6.07693 13.7821C7.91268 9.85002 9.79158 5.90432 12.8918 1.63131L12.0564 1.21924C9.43865 4.209 7.40486 7.03908 5.63768 10.4024C4.40877 9.21018 2.42271 7.52307 1.21006 6.65627L0.820312 7.36721Z" fill="#16DBAA"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_205_1682">
                    <rect width="13" height="14" fill="white" transform="translate(0.376953 0.506836)"/>
                    </clipPath>
                    </defs>
                    </svg>
                    <span class="text-verified pl-1">'
                        . __('Verified') .
                        '</span>
                        </div>
                        ';
                    return $html;
                } else {
                    $html = '
                    <div class="flex justify-center items-center">
                    <svg width="14" height="15" viewBox="0 0 14 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <g clip-path="url(#clip0_205_1682)">
                    <path d="M0.820312 7.36721C2.63193 9.47814 4.38846 11.3785 6.07693 13.7821C7.91268 9.85002 9.79158 5.90432 12.8918 1.63131L12.0564 1.21924C9.43865 4.209 7.40486 7.03908 5.63768 10.4024C4.40877 9.21018 2.42271 7.52307 1.21006 6.65627L0.820312 7.36721Z" fill="#16DBAA"/>
                    </g>
                    <defs>
                    <clipPath id="clip0_205_1682">
                    <rect width="13" height="14" fill="white" transform="translate(0.376953 0.506836)"/>
                    </clipPath>
                    </defs>
                    </svg>
                    <span class="text-verified pl-1">'
                        . __('UnVerified') .
                        '</span>
                        </div>
                        ';
                    return $html;
                }
            })
            ->editColumn('active_status', function (Student $user) {
                $checked = ($user->active_status == 1) ? 'checked' : '';
                $status   = '<label class="form-switch">
                             <input class="form-check-input chnageStatus" name="custom-switch-checkbox" ' . $checked . ' data-id="' . $user->id . '" data-url="' . route('user.status', $user->id) . '" type="checkbox">
                             </label>';
                return $status;
            })
            ->addColumn('action', function (Student $user) {
                return view('admin.students.action', compact('user'));
            })
            ->rawColumns(['role', 'action', 'email_verified_at', 'phone_verified_at', 'active_status', 'name']);
        return $data;
    }

    public function query(Student $model)
    {
        if (tenant('id') == null) {
            return   $model->newQuery()->select(['students.*', 'domains.domain'])
                ->join('domains', 'domains.tenant_id', '=', 'users.tenant_id')->where('type', 'Admin');
        } else {
            return $model->newQuery()->where('type', '=', 'Student')->where('isGuest', false);
        }
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('students-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->language([
                "paginate" => [
                    "next" => '<i class="ti ti-chevron-right"></i>',
                    "previous" => '<i class="ti ti-chevron-left"></i>'
                ],
                'lengthMenu' => __('_MENU_ entries per page'),
                "searchPlaceholder" => __('Search...'),
                "search" => ""
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
                'buttons'   => [
                    ['extend' => 'create', 'className' => 'btn btn-light-primary no-corner me-1 add_module', 'action' => " function ( e, dt, node, config ) {
                        window.location = '" . route('student.create') . "';
                   }"],
                    ['extend' => 'reload', 'className' => 'btn btn-light-primary no-corner me-1 add_module', 'action' => " function ( e, dt, node, config ) {
                        window.location = '" . route('student.import') . "';
                   }"],
                    [
                        'extend' => 'collection',
                        'className' => 'btn btn-light-secondary me-1 dropdown-toggle',
                        'text' => '<i class="ti ti-download"></i> Export',
                        "buttons" => [
                            ["extend" => "print", "text" => '<i class="fas fa-print"></i> Print', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "csv", "text" => '<i class="fas fa-file-csv"></i> CSV', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "excel", "text" => '<i class="fas fa-file-excel"></i> Excel', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                            ["extend" => "pdf", "text" => '<i class="fas fa-file-pdf"></i> PDF', "className" => "btn btn-light text-primary dropdown-item", "exportOptions" => ["columns" => [0, 1, 3]]],
                        ],
                    ],

                ],
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
                }',
            ])->language([
                'buttons' => [
                    'export' => __('Export'),
                    'print' => __('Print'),
                    'reload' => __('Import'),
                    'excel' => __('Excel'),
                    'csv' => __('CSV'),
                ]
            ]);
    }

    protected function getColumns()
    {

        return [
            Column::make('No')->title(__('#'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('User')),
            Column::make('email')->title(__('Email')),
            Column::make('email_verified_at')->title(__('Email Verified Status')),
            Column::make('phone_verified_at')->title(__('Phone Verified Status')),
            Column::make('created_at')->title(__('Created At')),
            Column::make('active_status')->title(__('Status')),
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
        return 'Students_' . date('YmdHis');
    }
}
