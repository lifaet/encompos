<?php

namespace App\Http\Controllers\Backend\Report;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{
    // Sale Report (list of orders)
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
            'net_profit' => $orders->sum('profit'), // total profit for selected range
            'start_date' => $start_date->format('Y-m-d'), // date only
            'end_date' => $end_date->format('Y-m-d'),     // date only
        ];

        return view('backend.reports.sale-report', $data);
    }

    // Sale Summary (totals)
    public function saleSummery(Request $request)
    {
        abort_if(!auth()->user()->can('reports_summary'), 403);

        $start_date_input = $request->input('start_date', Carbon::today()->subDays(29)->format('Y-m-d'));
        $end_date_input = $request->input('end_date', Carbon::today()->format('Y-m-d'));

        $start_date = Carbon::createFromFormat('Y-m-d', $start_date_input)->startOfDay();
        $end_date = Carbon::createFromFormat('Y-m-d', $end_date_input)->endOfDay();

        $orders = Order::whereBetween('created_at', [$start_date, $end_date])->get();

        $data = [
            'sub_total' => $orders->sum('sub_total'),
            'discount' => $orders->sum('discount'),
            'paid' => $orders->sum('paid'),
            'due' => $orders->sum('due'),
            'total' => $orders->sum('total'),
            'net_profit' => $orders->sum('profit'), // total profit for selected range
            'start_date' => $start_date->format('Y-m-d'), // date only
            'end_date' => $end_date->format('Y-m-d'),     // date only
        ];

        return view('backend.reports.sale-summery', $data);
    }

    // Inventory Report
    public function inventoryReport(Request $request)
    {
        abort_if(!auth()->user()->can('reports_inventory'), 403);

        if ($request->ajax()) {
            $products = Product::latest()->active()->get();
            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('name', fn($data) => $data->name)
                ->addColumn('sku', fn($data) => $data->sku)
                ->addColumn(
                    'price',
                    fn($data) => $data->discounted_price .
                        ($data->price > $data->discounted_price
                            ? '<br><del>' . $data->price . '</del>'
                            : '')
                )
                ->addColumn('quantity', fn($data) => $data->quantity . ' ' . optional($data->unit)->short_name)
                ->rawColumns(['name', 'sku', 'price', 'quantity'])
                ->toJson();
        }

        return view('backend.reports.inventory');
    }
}
