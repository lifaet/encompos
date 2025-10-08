<?php

namespace App\Http\Controllers\Backend\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('purchase_view'), 403);

        if ($request->ajax()) {
            $purchases = Purchase::with('supplier:id,name')->select(['id', 'supplier_id', 'grand_total', 'date', 'created_at'])->latest();
            return DataTables::of($purchases)
                ->addIndexColumn()
                ->addColumn('supplier', fn($data) => $data->supplier->name)
                ->addColumn('id', fn($data) => '#' . $data->id)
                ->addColumn('total', fn($data) => $data->grand_total)
                ->addColumn('created_at', fn($data) => Carbon::parse($data->date)->format('d M, Y'))
                ->addColumn('action', function ($data) {
                    $actions = '';

                    // ✅ Edit button
                    if (auth()->user()->can('purchase_update')) {
                        $actions .= '
                            <a href="' . route('backend.admin.purchase.edit', $data->id) . '" 
                               class="btn btn-sm bg-primary">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        ';
                    }

                    // View button
                    if (auth()->user()->can('purchase_view')) {
                        $actions .= '
                            <a href="' . route('backend.admin.purchase.products', $data->id) . '" 
                               class="btn btn-sm bg-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                        ';
                    }

                    // Delete button
                    if (auth()->user()->can('purchase_delete')) {
                        $actions .= '
                            <form action="' . route('backend.admin.purchase.delete_purchase', $data->id) . '" 
                                method="POST" 
                                style="display:inline;" 
                                onsubmit="return confirm(\'Are you sure you want to delete this purchase?\')">
                                ' . csrf_field() . method_field('DELETE') . '
                                <button type="submit" class="btn btn-sm bg-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        ';
                    }

                    return $actions ?: '<span class="text-muted">No Action</span>';
                })
                ->rawColumns(['supplier', 'id', 'total', 'created_at', 'action'])
                ->toJson();
        }

        return view('backend.purchase.index');
    }

    public function create()
    {
        abort_if(!auth()->user()->can('purchase_create'), 403);

        $suppliers = Supplier::orderBy('name')->get();
        $products  = Product::orderBy('name')->get();

        return view('backend.purchase.create', compact('suppliers', 'products'));
    }

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

    public function show(Request $request, $id)
    {
        if ($request->wantsJson()) {
            $purchase = Purchase::with('items', 'supplier')->findOrFail($id);
            return $purchase;
        }
    }

/**
 * Show the form for editing the specified purchase.
 */
public function edit($id)
{
    abort_if(!auth()->user()->can('purchase_update'), 403);

    $purchase = Purchase::with('items.product')->findOrFail($id);
    $suppliers = Supplier::orderBy('name')->get();
    $products  = Product::orderBy('name')->get();

    return view('backend.purchase.edit', compact('purchase', 'suppliers', 'products'));
}

/**
 * Update the specified purchase in storage.
 */
public function update(Request $request, $id)
{
    abort_if(!auth()->user()->can('purchase_update'), 403);

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
        $purchase = Purchase::with('items')->findOrFail($id);

        // 🧾 1. Revert previous stock
        foreach ($purchase->items as $item) {
            $item->product->decrement('quantity', $item->quantity);
            $item->delete();
        }

        // 🧾 2. Update purchase info
        $purchase->update([
            'supplier_id' => $request->supplier_id,
            'sub_total' => $request->totals['subTotal'],
            'tax' => $request->totals['tax'] ?? 0,
            'discount_value' => $request->totals['discount'] ?? 0,
            'shipping' => $request->totals['shipping'] ?? 0,
            'grand_total' => $request->totals['grandTotal'],
            'date' => $request->date ?? now()->toDateString(),
        ]);

        // 🧾 3. Recreate purchase items & update stock
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
            ->with('success', 'Purchase updated successfully.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Failed to update purchase: '.$e->getMessage());
    }
}


    public function delete_purchase(Purchase $purchase)
    {
        abort_if(!auth()->user()->can('purchase_delete'), 403);

        DB::beginTransaction();
        try {
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

    public function purchaseProducts(Request $request, $id)
    {
        $purchase = Purchase::with('items.product')->findOrFail($id);
        return view('backend.purchase.products', compact('id', 'purchase'));
    }
}
