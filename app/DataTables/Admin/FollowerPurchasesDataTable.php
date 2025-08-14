<?php

namespace App\DataTables\Admin;

use App\Facades\UtilityFacades;
use App\Models\Follower;
use App\Models\Plan;
use App\Models\Post;
use App\Models\PurchasePost;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class FollowerPurchasesDataTable extends DataTable
{
    public function dataTable($query)
    {
        try {
            $data = datatables()
                ->eloquent($query)
                ->addIndexColumn()
                ->editColumn('purchase_date', function ($request) {
                    if ($request->purchase_date) {
                        return UtilityFacades::date_time_format($request->purchase_date);
                    }
                    return 'N/A';
                })
                ->editColumn('amount', function ($request) {
                    if ($request->amount) {
                        return UtilityFacades::amount_format($request->amount);
                    }
                    return 'N/A';
                })
                ->editColumn('plan_name', function ($request) {
                    if ($request->plan_name) {
                        return '<label class="badge rounded-pill bg-blue-600 p-2 px-3">' . $request->plan_name . '</label>';
                    }
                    return '<label class="badge rounded-pill bg-gray-400 p-2 px-3">No Plan</label>';
                })
                ->editColumn('post_name', function ($request) {
                    if ($request->post_name) {
                        return '<span class="font-medium">' . $request->post_name . '</span>';
                    }
                    return '<span class="text-gray-500">N/A</span>';
                })
                ->editColumn('type', function ($request) {
                    if ($request->type === 'plan') {
                        return '<label class="badge rounded-pill bg-green-600 p-2 px-3">Plan Subscription</label>';
                    } else {
                        return '<label class="badge rounded-pill bg-orange-600 p-2 px-3">Post Purchase</label>';
                    }
                })
                ->rawColumns(['plan_name', 'post_name', 'type']);

            return $data;
        } catch (\Exception $e) {
            // Return empty data table if there's an error
            return datatables()
                ->of([])
                ->addIndexColumn()
                ->toJson();
        }
    }

    public function query()
    {
        $follower = Auth::user();

        // Get purchased post
        $purchasedPosts = PurchasePost::select([
            'purchasepost.id',
            'purchasepost.created_at as purchase_date',
            'post.title as post_name',
            'post.price as amount',
            'followers.plan_id',
            'plans.name as plan_name',
            DB::raw("'post' as type")
        ])
            ->join('post', 'purchasepost.post_id', '=', 'post.id')
            ->join('followers', 'purchasepost.follower_id', '=', 'followers.id')
            ->leftJoin('plans', 'followers.plan_id', '=', 'plans.id')
            ->where('purchasepost.follower_id', $follower->id);

        // Get current plan information if exists
        $currentPlan = DB::table('followers')
            ->select([
                DB::raw("CONCAT('plan_', followers.id) as id"),
                DB::raw("followers.plan_expired_date as purchase_date"),
                DB::raw("NULL as post_name"),
                DB::raw("COALESCE(plans.price, 0) as amount"),
                'followers.plan_id',
                'plans.name as plan_name',
                DB::raw("'plan' as type")
            ])
            ->leftJoin('plans', 'followers.plan_id', '=', 'plans.id')
            ->where('followers.id', $follower->id)
            ->whereNotNull('followers.plan_id');

        // Union the queries without ordering (ordering will be handled by DataTables)
        return $purchasedPosts->union($currentPlan);
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('follower-purchases-table')
            ->addTableClass('display responsive nowrap')
            ->columns($this->getColumns())
            ->minifiedAjax(route('follower-purchases.data'))
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
                        $("<span>").addClass("font-medium text-2xl pl-4").text("My Purchases & Subscriptions")
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
                'buttons'        => [],
                "scrollX" => true,
                "responsive" => [
                    "scrollX" => false,
                    "details" => [
                        "display" => "$.fn.dataTable.Responsive.display.childRow",
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
            ])->language([]);
    }

    protected function getColumns()
    {
        return [
            Column::make('No')->title(__('#'))->data('DT_RowIndex')->name('DT_RowIndex')->searchable(false)->orderable(false),
            Column::make('type')->title(__('Type')),
            Column::make('plan_name')->title(__('Plan Name')),
            Column::make('post_name')->title(__('Post Name')),
            Column::make('purchase_date')->title(__('Purchase Date')),
            Column::make('amount')->title(__('Amount')),
        ];
    }

    protected function filename(): string
    {
        return 'Follower_Purchases_' . date('YmdHis');
    }
}
