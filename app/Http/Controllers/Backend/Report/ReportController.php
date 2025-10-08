<?php

namespace App\Http\Controllers\Backend\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
    // Sale Report
    public function saleReport(Request $request)
    {
        abort_if(!auth()->user()->can('reports_sales'), 403);

        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input)->startOfDay();
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date_input)->endOfDay();

        $orders = Order::whereBetween('created_at', [$start_date, $end_date])
            ->with('customer')
            ->get();

        $data = [
            'orders' => $orders,
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'net_profit' => $orders->sum('profit'),
            'start_date' => $start_date->format('Y-m-d'),
            'end_date' => $end_date->format('Y-m-d'),
        ];

        return view('backend.reports.sale-report', $data);
    }

    // Sale Summary
    public function saleSummery(Request $request)
    {
        abort_if(!auth()->user()->can('reports_summary'), 403);

        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input)->startOfDay();
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date_input)->endOfDay();

        $orders = Order::whereBetween('created_at', [$start_date, $end_date])->get();
        $total_purchase = Purchase::whereBetween('created_at', [$start_date, $end_date])->sum('grand_total');

        $net_profit_including_due = $orders->sum('profit');          
        $net_profit_excluding_due = $orders->sum('profit') - $orders->sum('due'); 

        $data = [
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'total_purchase' => $total_purchase,
            'net_profit_including_due' => $net_profit_including_due,
            'net_profit_excluding_due' => $net_profit_excluding_due,
            'start_date' => $start_date->format('Y-m-d'),
            'end_date' => $end_date->format('Y-m-d'),
        ];

        return view('backend.reports.sale-summery', $data);
    }

    // Inventory Report
    public function inventoryReport(Request $request)
    {
        abort_if(!auth()->user()->can('reports_inventory'), 403);

        if ($request->ajax()) {
            $products = Product::with('unit:id,short_name')->select(['id', 'name', 'sku', 'purchase_price', 'expire_date', 'quantity', 'unit_id'])->active()->latest();
            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('name', fn($data) => $data->name)
                ->addColumn('sku', fn($data) => $data->sku)
                ->addColumn('purchase_price', fn($data) => number_format($data->purchase_price ?? 0, 2))
                ->addColumn('discount', fn($data) => number_format($data->discounted_price ?? 0, 2))
                ->addColumn('sale_price', fn($data) => number_format($data->discounted_price ?? 0, 2))
                ->addColumn('expire_date', function($data) {
                    return $data->expire_date ? Carbon::parse($data->expire_date)->format('Y-m-d') : '-';
                })
                ->addColumn('quantity', fn($data) => $data->quantity . ' ' . optional($data->unit)->short_name)
                ->rawColumns(['name', 'sku', 'purchase_price', 'sale_price', 'expire_date', 'quantity'])
                ->toJson();
        }

        return view('backend.reports.inventory');
    }
}
