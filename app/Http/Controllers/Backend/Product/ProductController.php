<?php

namespace App\Http\Controllers\Backend\Product;

use App\Exports\DemoProductsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Imports\ProductsImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Trait\FileHandler;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    public $fileHandler;

    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('product_view'), 403);

        if ($request->ajax()) {
            $products = Product::select(['id', 'name', 'price', 'quantity', 'status', 'created_at'])->latest();
            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('name', fn($data) => $data->name)
                ->addColumn('price', fn($data) =>
                    $data->discounted_price .
                    ($data->price > $data->discounted_price
                        ? '<br><del>' . $data->price . '</del>'
                        : '')
                )
                ->addColumn('quantity', fn($data) =>
                    $data->quantity . ' ' . optional($data->unit)->short_name
                )
                ->addColumn('created_at', fn($data) =>
                    $data->created_at->format('d M, Y')
                )
                ->addColumn('status', fn($data) =>
                    $data->status
                        ? '<span class="badge bg-primary">Active</span>'
                        : '<span class="badge bg-danger">Inactive</span>'
                )
                ->addColumn('action', function ($data) {
                    $buttons = '';

                    // Edit button
                    if (auth()->user()->can('product_update')) {
                        $buttons .= '<a href="' . route('backend.admin.products.edit', $data->id) . '" 
                                        class="btn btn-sm bg-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a> ';
                    }

                    // Delete button
                    if (auth()->user()->can('product_delete')) {
                        $buttons .= '<form action="' . route('backend.admin.products.destroy', $data->id) . '" 
                                        method="POST" style="display:inline;">
                                        ' . csrf_field() . method_field("DELETE") . '
                                        <button type="submit" class="btn btn-sm bg-danger" 
                                            onclick="return confirm(\'Are you sure?\')">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </form> ';
                    }

                    // // Purchase button
                    // if (auth()->user()->can('product_purchase')) {
                    //     $buttons .= '<a href="' . route('backend.admin.purchase.create', ['barcode' => $data->sku]) . '" 
                    //                     class="btn btn-sm bg-success">
                    //                     <i class="fas fa-cart-plus"></i> Purchase
                    //                 </a>';
                    // }

                    return $buttons;
                })
                ->rawColumns(['image', 'name', 'price', 'quantity', 'status', 'created_at', 'action'])
                ->toJson();
        }

        if ($request->wantsJson()) {
            $request->validate([
                'search' => 'required|string|max:255',
            ]);

            $products = Product::query()
                ->where(function ($query) use ($request) {
                    $query->where('name', 'LIKE', "%{$request->search}%")
                        ->orWhere('sku', $request->search);
                })
                ->get();

            return ProductResource::collection($products);
        }

        return view('backend.products.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        abort_if(!auth()->user()->can('product_create'), 403);
        $brands = Brand::whereStatus(true)->get();
        $categories = Category::whereStatus(true)->get();
        $units = Unit::all();
        return view('backend.products.create', compact('brands', 'categories', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {

        abort_if(!auth()->user()->can('product_create'), 403);
        $validated = $request->validated();
        $product = Product::create($validated);
        if ($request->hasFile("product_image")) {
            $product->image = $this->fileHandler->fileUploadAndGetPath($request->file("product_image"), "/public/media/products");
            $product->save();
        }

        return redirect()->route('backend.admin.products.index')->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {

        abort_if(!auth()->user()->can('product_update'), 403);

        $product = Product::findOrFail($id);
        $brands = Brand::whereStatus(true)->get();
        $categories = Category::whereStatus(true)->get();
        $units = Unit::all();
        return view('backend.products.edit', compact('brands', 'categories', 'units', 'product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, $id)
    {

        abort_if(!auth()->user()->can('product_update'), 403);
        $validated = $request->validated();
        $product = Product::findOrFail($id);
        $oldImage = $product->image;
        $product->update($validated);
        if ($request->hasFile("product_image")) {
            $product->image = $this->fileHandler->fileUploadAndGetPath($request->file("product_image"), "/public/media/products");
            $product->save();
            $this->fileHandler->secureUnlink($oldImage);
        }

        return redirect()->route('backend.admin.products.index')->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {

        abort_if(!auth()->user()->can('product_delete'), 403);
        $product = Product::findOrFail($id);
        if ($product->image != '') {
            $this->fileHandler->secureUnlink($product->image);
        }
        $product->delete();
        return redirect()->back()->with('success', 'Product Deleted Successfully');
    }
    public function import(Request $request)
    {
        if ($request->query('download-demo')) {
            return Excel::download(new DemoProductsExport, 'demo_products.xlsx');
        }
        if ($request->isMethod('post') && $request->hasFile('file')) {
            Excel::import(new ProductsImport, $request->file('file'));
            return redirect()->back()->with('success', 'Products imported successfully.');
        }
        return view('backend.products.import');
    }
}
