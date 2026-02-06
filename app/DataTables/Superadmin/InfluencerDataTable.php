<?php

namespace App\DataTables\Superadmin;

use Carbon\Carbon;
use App\Models\User;
use App\Facades\UtilityFacades;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InfluencerDataTable extends DataTable
{
    protected $showCreateButton = false;

    public function with(array|string $key, mixed $value = null): static
    {
        if (is_array($key)) {
            $this->showCreateButton = $key['showCreateButton'] ?? false;
        } elseif ($key === 'showCreateButton') {
            $this->showCreateButton = $value;
        }

        return parent::with($key, $value);
    }

    public function dataTable($query)
    {

        $data = datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('created_at', function ($request) {
                return UtilityFacades::date_time_format($request->created_at);
            })
            ->editColumn('name', function (User $user) {
                $imageSrc = $user->dp ?? asset('assets/img/logo/logo.png');
                $html     =
                    '
                <div class="flex justify-start items-center">'
                    .
                    "<img src=' " . $imageSrc . " ' width='20' class='rounded-full'/>"
                    .
                    "<span class='pl-2'>" . $user->name . " </span>" .
                    '</div>';
                return $html;
            })
            ->editColumn('email_verified_at', function (User $user) {
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
            ->editColumn('phone_verified_at', function (User $user) {
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
            ->addColumn('subscription_status', function (User $user) {
                // Check if instructor has active subscription in central database
                $subscription = tenancy()->central(function () use ($user) {
                    return \App\Models\InfluencerSubscription::where('influencer_id', $user->id)
                        ->where('tenant_id', $user->tenant_id)
                        ->where('plan_id', $user->subscription_plan_id)
                        ->where('status', 'active')
                        ->first();
                });

                if ($subscription) {
                    // Has active paid subscription
                    // $expiryDate = Carbon::parse($subscription->expires_at)->format('M d, Y');
                    $html = '<span class="badge bg-success text-white px-2 py-1 rounded">';
                    $html .= __('Paid Subscription');
                    // $html .= '<br><small class="text-xs">' . __('Expires: ') . $expiryDate . '</small>';
                    $html .= '</span>';
                    return $html;
                } elseif ($user->days_limit > 0) {
                    // Has free trial
                    if (empty($user->start_login_date)) {
                        // Trial not started yet
                        $html = '<span class="badge bg-info text-white px-2 py-1 rounded">';
                        $html .= __('Free Trial: ' . $user->days_limit . ' days');
                        $html .= '<br><small class="text-xs">' . __('Not started yet') . '</small>';
                        $html .= '</span>';
                        return $html;
                    } else {
                        // Trial in progress
                        $startDate = Carbon::parse($user->start_login_date);
                        $endDate = $startDate->copy()->addDays($user->days_limit);
                        $now = Carbon::now();

                        if ($now->greaterThan($endDate)) {
                            // Trial expired
                            $html = '<span class="badge bg-danger text-white px-2 py-1 rounded">';
                            $html .= __('Trial Expired');
                            $html .= '</span>';
                            return $html;
                        } else {
                            // Trial active
                            $daysLeft = $now->diffInDays($endDate, false);
                            $html = '<span class="badge bg-warning text-dark px-2 py-1 rounded">';
                            $html .= __('Free Trial');
                            $html .= '<br><small class="text-xs">' . __('Days left: ') . $daysLeft . '</small>';
                            $html .= '</span>';
                            return $html;
                        }
                    }
                } else {
                    // No subscription or trial
                    $html = '<span class="badge bg-secondary text-white px-2 py-1 rounded">';
                    $html .= __('No Subscription');
                    $html .= '</span>';
                    return $html;
                }
            })
            ->addColumn('plan_info', function (User $user) {
                if ($user->subscription_plan_id) {
                    // Get plan name from central database
                    $plan = tenancy()->central(function () use ($user) {
                        return \App\Models\Plan::find($user->subscription_plan_id);
                    });

                    if ($plan) {
                        return $plan->name;
                    }
                }
                return __('No Plan');
            })
            ->addColumn('action', function (User $user) {
                return view('superadmin.influencers.action', compact('user'));
            })
            ->rawColumns(['role', 'action', 'email_verified_at', 'phone_verified_at', 'active_status', 'name', 'subscription_status']);
        return $data;
    }

    public function query(User $model)
    {
        if (tenant('id') == null) {
            return $model->newQuery()->select(['users.*', 'domains.domain'])
                ->join('domains', 'domains.tenant_id', '=', 'users.tenant_id')->where('type', 'Influencer');
        } else {
            return $model->newQuery()->where('type', '=', 'Influencer');
        }
    }

    public function html()
    {
        $buttons = [
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
        ];

        if ($this->showCreateButton) {
            $extraButtons = [
                [
                    'extend' => 'create',
                    'className' => 'btn btn-light-primary no-corner me-1 add_module',
                    'text' => '<i class="ti ti-plus"></i> Create',
                    'action' => "function ( e, dt, node, config ) {
                        window.location = '" . route('influencer.create') . "';
                    }",
                ],
                [
                    'extend' => 'reload',
                    'className' => 'btn btn-light-primary no-corner me-1 add_module',
                    'text' => '<i class="ti ti-upload"></i> Import',
                    'action' => "function ( e, dt, node, config ) {
                        window.location = '" . route('influencer.import') . "';
                    }",
                ],
            ];

            $buttons = array_merge($extraButtons, $buttons);
        }
        return $this->builder()
            ->setTableId('influencers-table')
            ->addTableClass('display responsive nowrap')
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
                var tableContainer = $(table.api().table().container());
                var searchInput = $(\'#\'+table.api().table().container().id+\' label input[type="search"]\');
                searchInput.removeClass(\'form-control form-control-sm\');
                searchInput.addClass(\'dataTable-input\');
                var select = $(table.api().table().container()).find(".dataTables_length select").removeClass(\'custom-select custom-select-sm form-control form-control-sm\').addClass(\'dataTable-selector\');
                tableContainer.find(".dataTable-title").html(
                    $("<div>").addClass("flex justify-start items-center").append(
                        $("<div>").addClass("custom-table-header"),
                        $("<span>").addClass("font-medium text-2xl pl-4").text("All Influencers")
                    )
                );
            }')
            ->parameters([
                "dom"            => "
                        <'dataTable-top row'<'dataTable-title col-lg-3 col-sm-12'<'custom-title'>>
                        <'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                        <'dataTable-container'<'col-sm-12'tr>>
                        <'dataTable-bottom row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l>
                        <'col-sm-7'p>>
                        ",
                'buttons'        => $buttons,

                "scrollX" => true,
                "responsive" => [
                    "scrollX" => false,
                    "details" => [
                        "display" => "$.fn.dataTable.Responsive.display.childRow", // <- keeps rows collapsed
                        "renderer" => "function (api, rowIdx, columns) {
                            var data = $('<table/>').addClass('vertical-table');
                            $.each(columns, function (i, col) {
                                data.append(
                                    '<tr>' +
                                        '<td><strong>' + col.title + '</strong></td>' +
                                        '<td>' + col.data + '</td>' +
                                    '</tr>'
                                );
                            });
                            return data;
                        }"
                    ]
                ],
                "rowCallback" => 'function(row, data, index) {
                    $(row).addClass("custom-parent-row"); 
                }',
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
                    'reload' => __('Import'),
                    'excel'  => __('Excel'),
                    'csv'    => __('CSV'),
                ],
            ]);
    }

    protected function getColumns()
    {

        return [
            Column::make('No')->title(__('#'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('User')),
            Column::make('email')->title(__('Email')),
            Column::make('plan_info')->title(__('Plan'))->orderable(false),
            Column::make('subscription_status')->title(__('Subscription Status'))->orderable(false),
            Column::make('email_verified_at')->title(__('Email Verified Status')),
            Column::make('phone_verified_at')->title(__('Phone Verified Status')),
            // Column::make('created_at')->title(__('Created At')),

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
        return 'Influencers_' . date('YmdHis');
    }
}
