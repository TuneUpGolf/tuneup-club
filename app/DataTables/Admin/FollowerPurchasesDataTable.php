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
                ->query($query)
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
                ->editColumn('type', function ($request) {
                    if ($request->type === 'plan') {
                        return 'Subscription';
                    } else {
                        return 'Post';
                    }
                })
                ->rawColumns(['type'])
                // Global search across unioned/aliased columns
                ->filter(function ($query) {
                    $search = request('search.value');
                    if (!empty($search)) {
                        $keyword = strtolower($search);
                        $query->where(function ($q) use ($keyword) {
                            $q->whereRaw('LOWER(title) LIKE ?', ["%{$keyword}%"]) // includes plan name
                                ->orWhereRaw('LOWER(type) LIKE ?', ["%{$keyword}%"]) // plan/post
                                ->orWhereRaw('LOWER(CAST(purchase_date AS CHAR)) LIKE ?', ["%{$keyword}%"]) // date
                                ->orWhereRaw('LOWER(CAST(amount AS CHAR)) LIKE ?', ["%{$keyword}%"]); // numeric
                        });
                    }
                }, true)
                // Column-specific fallback filters (for column filters UI)
                ->filterColumn('title', function ($query, $keyword) {
                    $query->whereRaw('LOWER(title) LIKE ?', ['%' . strtolower($keyword) . '%']);
                })
                ->filterColumn('type', function ($query, $keyword) {
                    $query->whereRaw('LOWER(type) LIKE ?', ['%' . strtolower($keyword) . '%']);
                })
                ->filterColumn('purchase_date', function ($query, $keyword) {
                    $query->whereRaw('LOWER(CAST(purchase_date AS CHAR)) LIKE ?', ['%' . strtolower($keyword) . '%']);
                })
                ->filterColumn('amount', function ($query, $keyword) {
                    $query->whereRaw('LOWER(CAST(amount AS CHAR)) LIKE ?', ['%' . strtolower($keyword) . '%']);
                });

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

        // Get purchased post (use Query Builder, not Eloquent)
        $purchasedPosts = DB::table('purchasepost')
            ->select([
                'purchasepost.id',
                DB::raw('purchasepost.created_at as purchase_date'),
                'post.title',
                DB::raw('post.price as amount'),
                'followers.plan_id',
                DB::raw("'post' as type"),
                'purchasepost.follower_id'
            ])
            ->join('post', 'purchasepost.post_id', '=', 'post.id')
            ->join('followers', 'purchasepost.follower_id', '=', 'followers.id')
            ->leftJoin('plans', 'followers.plan_id', '=', 'plans.id');

        // Get current plan information if exists
        $currentPlan = DB::table('followers')
            ->select([
                DB::raw("CONCAT('plan_', followers.id) as id"),
                DB::raw("followers.plan_expired_date as purchase_date"),
                'plans.name as title',
                DB::raw("COALESCE(plans.price, 0) as amount"),
                'followers.plan_id',
                DB::raw("'plan' as type"),
                'followers.id as follower_id'
            ])
            ->leftJoin('plans', 'followers.plan_id', '=', 'plans.id')
            ->whereNotNull('followers.plan_id');

        // Union the queries and wrap in a subquery so aliases are searchable
        $unionQuery = $purchasedPosts->union($currentPlan);

        // Wrap union as subquery and return a Query Builder
        $outerQuery = DB::table(DB::raw("({$unionQuery->toSql()}) as combined_data"))
            ->mergeBindings($unionQuery)
            ->select('*')
            ->where('follower_id', $follower->id);

        return $outerQuery;
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
                        $("<span>").addClass("font-medium text-2xl pl-4").text("My Posts & Subscriptions")
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
            Column::make('title')->title(__('title'))->data('title')->searchable(true)->orderable(true),
            Column::make('type')->title(__('Type'))->data('type')->searchable(true)->orderable(true),
            Column::make('purchase_date')->title(__('Purchase Date'))->data('purchase_date')->searchable(true)->orderable(true),
            Column::make('amount')->title(__('Amount'))->data('amount')->searchable(true)->orderable(true),
        ];
    }

    protected function filename(): string
    {
        return 'Follower_Purchases_' . date('YmdHis');
    }
}
