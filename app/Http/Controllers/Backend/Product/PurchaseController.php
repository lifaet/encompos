<?php

namespace App\Http\Controllers\Backend\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Models\Supplier;


class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        abort_if(!auth()->user()->can('purchase_view'), 403);
        if ($request->ajax()) {
            $purchases = Purchase::with('supplier')->latest()->get();
            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('supplier', fn($data) => $data->supplier->name)
                ->addColumn('id', function ($data) {
                    return '#' . $data->id;
                })
                ->addColumn('total', fn($data) => $data->grand_total)
                ->addColumn('created_at', fn($data) => \Carbon\Carbon::parse($data->date)->format('d M, Y')) // Using Carbon for formatting
                ->addColumn('action', function ($data) {
                    $actions = '<div class="btn-group">
                        <button type="button" class="btn bg-gradient-primary btn-flat">Action</button>
                        <button type="button" class="btn bg-gradient-primary btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false">
                        <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                        <a class="dropdown-item" href="' . route('backend.admin.purchase.create', ['purchase_id' => $data->id]) . '">
                            <i class="fas fa-edit"></i> Edit
                        </a> 
                        <a class="dropdown-item" href="' . route('backend.admin.purchase.products', $data->id) . '">
                            <i class="fas fa-eye"></i> View
                        </a>';

                    if (auth()->user()->can('purchase_delete')) {
                        $actions .= '
                        <form action="' . route('backend.admin.purchase.delete_purchase', $data->id) . '" 
                                method="POST" 
                                onsubmit="return confirm(\'Are you sure you want to delete this purchase?\')" 
                                style="display:inline;">
                            ' . csrf_field() . method_field('DELETE') . '
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </form>';
                    }

                    $actions .= '</div></div>';

                    return $actions;
                })

                ->rawColumns(['supplier', 'id', 'total', 'created_at', 'action'])
                ->toJson();
        }


        return view('backend.purchase.index');
    }

    /**
     * Show the form for creating a new resource.
     */

    public function create()
    {
        abort_if(!auth()->user()->can('purchase_create'), 403);

        // Fetch suppliers and products for the Blade form
        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();

        return view('backend.purchase.create', compact('suppliers', 'products'));
    }


        /**
         * Store a newly created resource in storage.
         */
    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('purchase_create'), 403);

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'nullable|date',
            'products' => 'required|array',
            'products.*.id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.purchase_price' => 'required|numeric|min:0',
            'products.*.price' => 'required|numeric|min:0',
            'totals.subTotal' => 'required|numeric',
            'totals.grandTotal' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'sub_total' => $request->totals['subTotal'],
                'tax' => $request->totals['tax'] ?? 0,
                'discount_value' => $request->totals['discount'] ?? 0,
                'shipping' => $request->totals['shipping'] ?? 0,
                'grand_total' => $request->totals['grandTotal'],
                'date' => $request->date ?? now()->toDateString(),
                'status' => 1,
            ]);

            foreach ($request->products as $product) {
                $prod = Product::findOrFail($product['id']);
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $prod->id,
                    'purchase_price' => $product['purchase_price'],
                    'price' => $product['price'],
                    'quantity' => $product['qty'],
                ]);
                $prod->increment('quantity', $product['qty']);
            }

            DB::commit();
            return redirect()->route('backend.admin.purchase.index')
                ->with('success', 'Purchase created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to create purchase: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id)
    {

        if ($request->wantsJson()) {
            $purchase = Purchase::with('items', 'supplier')->findOrFail($id);
            return $purchase;
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        abort_if(!auth()->user()->can('purchase_update'), 403);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {

        abort_if(!auth()->user()->can('purchase_update'), 403);
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function delete_purchase(Purchase $purchase)
    {
        abort_if(!auth()->user()->can('purchase_delete'), 403);

        DB::beginTransaction();
        try {
            // Restore stock before deleting
            foreach ($purchase->items as $item) {
                $item->product->decrement('quantity', $item->quantity);
                $item->delete();
            }

            $purchase->delete();
            DB::commit();

            return redirect()->route('backend.admin.purchase.index')->with('success', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to delete purchase: ' . $e->getMessage());
        }
    }
    
    // purchaseProducts list by Purchase id
    public function purchaseProducts(Request $request, $id)
    {
        $purchase = Purchase::with('items.product')->findOrFail($id);
        return view('backend.purchase.products', compact('id', 'purchase'));
    }
}
