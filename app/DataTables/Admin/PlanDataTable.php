<?php

namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PlanDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('name', function (Plan $plan) {
                $url = route('plans.buyers', $plan->id);
                return '<a href="#" class="js-plan-buyers" data-plan-id="' . $plan->id . '" data-url="' . $url . '">' . e($plan->name) . '</a>';
            })
            ->editColumn('created_at', function ($request) {
                return UtilityFacades::date_time_format($request->created_at);
            })
            ->editColumn('duration', function (plan $plan) {
                return $plan->duration . ' ' . $plan->durationtype;
            })
            ->editColumn('active_status', function (plan $plan) {
                $checked = ($plan->active_status == 1) ? 'checked' : '';
                $status   = '<label class="form-switch">
                             <input class="form-check-input chnageStatus" name="custom-switch-checkbox" ' . $checked . ' data-id="' . $plan->id . '" data-url="' . route('myplan.status', $plan->id) . '" type="checkbox">
                             </label>';
                return $status;
            })
            ->editColumn('created_at', function ($request) {
                return UtilityFacades::date_time_format($request->created_at);
            })
            ->addColumn('action', function (plan $plan) {
                return view('admin.plans.action', compact('plan'));
            })
            ->editColumn('price', function (plan $plan) {
                return UtilityFacades::amount_format($plan->price);
            })
            ->addColumn('purchases_count', function (Plan $plan) {
                return $plan->buyers_count ?? 0;
            })
            ->rawColumns(['action', 'active_status', 'name']);
    }

    public function query(Plan $model)
    {
        $query = $model->newQuery()->withCount([
            'orders as buyers_count' => function ($q) {
                $q->join('followers', 'orders.follower_id', '=', 'followers.id')
                    ->whereNotNull('followers.plan_expired_date')
                    ->whereDate('followers.plan_expired_date', '>=', now()->toDateString())
                    ->whereColumn('followers.plan_id', 'orders.plan_id')
                    ->select(DB::raw('COUNT(DISTINCT orders.follower_id)'));
            },
        ]);
        if (auth()->user()->type == 'Admin') {
            return $query->where('tenant_id', null);
        }
        return $query->where('influencer_id', auth()->user()->id);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('users-table')
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
                               <'dataTable-top row'<'dataTable-dropdown page-dropdown col-lg-2 col-sm-12'l><'dataTable-botton table-btn col-lg-6 col-sm-12'B><'dataTable-search tb-search col-lg-3 col-sm-12'f>>
                             <'dataTable-container'<'col-sm-12'tr>>
                             <'dataTable-bottom row'<'col-sm-5'i><'col-sm-7'p>>
                               ",
                'buttons'   => [
                    ['extend' => 'create', 'className' => 'btn btn-light-primary no-corner me-1 add_module', 'action' => " function ( e, dt, node, config ) {
                        window.location = '" . route('plans.createmyplan') . "';
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
                    ['extend' => 'reset', 'className' => 'btn btn-light-danger me-1'],
                    ['extend' => 'reload', 'className' => 'btn btn-light-warning'],
                ],
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
                    'create' => __('Create'),
                    'export' => __('Export'),
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
            Column::make('No')->title(__('No'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('name')->title(__('Name')),
            Column::make('price')->title(__('Price')),
            Column::make('duration')->title(__('Duration')),
            Column::computed('purchases_count')->title(__('# of Purchases'))->exportable(true)->printable(true),
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
        return 'Plan_' . date('YmdHis');
    }
}
