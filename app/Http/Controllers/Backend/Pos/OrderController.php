<?php

namespace App\Http\Controllers\Backend\Pos;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTransaction;
use App\Models\PosCart;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $orders = Order::with('customer')->get();
            return DataTables::of($orders)
                ->addIndexColumn()
                ->addColumn('saleId', fn($data) => "#" . $data->id)
                ->addColumn('saleDate', fn($data) => $data->created_at->format('Y-m-d H:i:s'))
                ->addColumn('customer', fn($data) => $data->customer->name ?? '-')
                ->addColumn('item', fn($data) => $data->total_item)
                ->addColumn('sub_total', fn($data) => number_format($data->sub_total, 2, '.', ','))
                ->addColumn('discount', fn($data) => number_format($data->discount, 2, '.', ','))
                ->addColumn('total', fn($data) => number_format($data->total, 2, '.', ','))
                ->addColumn('profit', fn($data) => number_format($data->profit, 2, '.', ','))
                ->addColumn('paid', fn($data) => number_format($data->paid, 2, '.', ','))
                ->addColumn('due', fn($data) => number_format($data->due, 2, '.', ','))
                ->addColumn('status', fn($data) => $data->status
                    ? '<span class="badge bg-primary">Paid</span>'
                    : '<span class="badge bg-danger">Due</span>')
                ->addColumn('action', function ($data) {
                    $buttons = '';
                    $buttons .= '<a class="btn btn-success btn-sm" href="' . route('backend.admin.orders.invoice', $data->id) . '"><i class="fas fa-file-invoice"></i> Invoice</a>';
                    $buttons .= '<a class="btn btn-secondary btn-sm" href="' . route('backend.admin.orders.pos-invoice', $data->id) . '"><i class="fas fa-file-invoice"></i> Pos Invoice</a>';
                    if (!$data->status) {
                        $buttons .= '<a class="btn btn-warning btn-sm" href="' . route('backend.admin.due.collection', $data->id) . '"><i class="fas fa-receipt"></i> Due Collection</a>';
                    }
                    $buttons .= '<a class="btn btn-primary btn-sm" href="' . route('backend.admin.orders.transactions', $data->id) . '"><i class="fas fa-exchange-alt"></i> Transactions</a>';
                    return $buttons;
                })
                ->rawColumns(['saleId', 'customer', 'item', 'sub_total', 'discount', 'total', 'profit', 'paid', 'due', 'status', 'action'])
                ->toJson();
        }

        return view('backend.orders.index');
    }

    /**
     * Store a newly created order.
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => ['required', 'exists:customers,id', 'integer'],
            'order_discount' => ['nullable', 'numeric', 'min:0'],
            'paid' => ['nullable', 'numeric', 'min:0'],
        ]);

        $carts = PosCart::with('product')->where('user_id', auth()->id())->get();

        $totalPurchase = 0;
        $totalSell = 0;

        foreach ($carts as $cart) {
            $totalPurchase += $cart->product->purchase_price * $cart->quantity;
            $totalSell += $cart->product->discounted_price * $cart->quantity;
        }

        $orderDiscount = $request->order_discount ?? 0;
        $totalAmount = $totalSell - $orderDiscount;
        $dueAmount = $totalAmount - ($request->paid ?? 0);
        $profit = $totalSell - $totalPurchase - $orderDiscount;

        $order = Order::create([
            'customer_id' => $request->customer_id,
            'user_id' => $request->user()->id,
            'sub_total' => $totalSell,
            'discount' => $orderDiscount,
            'total' => round($totalAmount, 2),
            'paid' => $request->paid ?? 0,
            'due' => round($dueAmount, 2),
            'profit' => round($profit, 2),
            'status' => round($dueAmount, 2) <= 0,
        ]);

        // Deduct stock and save order products
        foreach ($carts as $cart) {
            $order->products()->create([
                'product_id' => $cart->product->id,
                'quantity' => $cart->quantity,
                'price' => $cart->product->price,
                'purchase_price' => $cart->product->purchase_price,
                'sub_total' => $cart->product->price * $cart->quantity,
                'discount' => ($cart->product->price - $cart->product->discounted_price) * $cart->quantity,
                'total' => $cart->product->discounted_price * $cart->quantity,
            ]);

            $cart->product->quantity -= $cart->quantity;
            $cart->product->save();
        }

        // Create order transaction if paid
        if (($request->paid ?? 0) > 0) {
            $order->transactions()->create([
                'amount' => $request->paid,
                'customer_id' => $order->customer_id,
                'user_id' => auth()->id(),
                'paid_by' => 'cash',
            ]);
        }

        PosCart::where('user_id', auth()->id())->delete();

        return response()->json(['message' => 'Order completed successfully', 'order' => $order], 200);
    }

    /**
     * Invoice view.
     */
    public function invoice($id)
    {
        $order = Order::with(['customer', 'products.product'])->findOrFail($id);
        return view('backend.orders.print-invoice', compact('order'));
    }

    /**
     * POS Invoice view.
     */
    public function posInvoice($id)
    {
        $order = Order::with(['customer', 'products.product'])->findOrFail($id);
        $maxWidth = readConfig('receiptMaxwidth') ?? '300px';
        return view('backend.orders.pos-invoice', compact('order', 'maxWidth'));
    }

    /**
     * Due collection.
     */
    public function collection(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($request->isMethod('post')) {
            $data = $request->validate(['amount' => 'required|numeric|min:1']);

            $order->paid += $data['amount'];
            $order->due -= $data['amount'];
            $order->status = $order->due <= 0;
            $order->save();

            $transaction = $order->transactions()->create([
                'amount' => $data['amount'],
                'customer_id' => $order->customer_id,
                'user_id' => auth()->id(),
                'paid_by' => 'cash',
            ]);

            return to_route('backend.admin.collectionInvoice', $transaction->id);
        }

        return view('backend.orders.collection.create', compact('order'));
    }

    /**
     * Collection invoice by transaction.
     */
    public function collectionInvoice($id)
    {
        $transaction = OrderTransaction::findOrFail($id);
        $order = $transaction->order;
        $collection_amount = $transaction->amount;
        return view('backend.orders.collection.invoice', compact('order', 'collection_amount', 'transaction'));
    }

    /**
     * Order transactions.
     */
    public function transactions($id)
    {
        $order = Order::with('transactions')->findOrFail($id);
        return view('backend.orders.collection.index', compact('order'));
    }
}
