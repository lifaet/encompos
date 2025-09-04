<?php

namespace App\Http\Controllers\Backend\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Trait\FileHandler;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
{
    public $fileHandler;

    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('product_view'), 403);

        if ($request->ajax()) {
            $products = Product::with(['category', 'brand', 'unit'])->latest();

            return DataTables::of($products)
                ->addIndexColumn()
                ->addColumn('category_name', fn($data) => optional($data->category)->name)
                ->addColumn('brand_name', fn($data) => optional($data->brand)->name)
                ->addColumn('unit_name', fn($data) => optional($data->unit)->short_name)
                ->addColumn('status', fn($data) => $data->status
                    ? '<span class="badge bg-primary">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>')
                ->addColumn('action', function ($data) {
                    return '<div class="btn-group">
                        <button type="button" class="btn bg-gradient-primary btn-flat">Action</button>
                        <button type="button" class="btn bg-gradient-primary btn-flat dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" href="'.route('backend.admin.products.edit', $data->id).'">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <div class="dropdown-divider"></div>
                            <form action="'.route('backend.admin.products.destroy', $data->id).'" method="POST" style="display:inline;">
                                '.csrf_field().method_field("DELETE").'
                                <button type="submit" class="dropdown-item" onclick="return confirm(\'Are you sure ?\')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>';
                })
                ->rawColumns(['status', 'action'])
                ->toJson();
        }

        if ($request->wantsJson()) {
            $request->validate([
                'search' => 'required|string|max:255',
            ]);

            $products = Product::query()
                ->where('name', 'LIKE', "%{$request->search}%")
                ->orWhere('sku', $request->search)
                ->get();

            return ProductResource::collection($products);
        }

        return view('backend.products.index');
    }

    public function create()
    {
        abort_if(!auth()->user()->can('product_create'), 403);
        $brands = Brand::whereStatus(true)->get();
        $categories = Category::whereStatus(true)->get();
        $units = Unit::all();
        return view('backend.products.create', compact('brands', 'categories', 'units'));
    }

    public function store(StoreProductRequest $request)
    {
        abort_if(!auth()->user()->can('product_create'), 403);
        $validated = $request->validated();
        $product = Product::create($validated);

        if ($request->hasFile("product_image")) {
            $product->image = $this->fileHandler->fileUploadAndGetPath(
                $request->file("product_image"),
                "/public/media/products"
            );
            $product->save();
        }

        return redirect()->route('backend.admin.products.index')->with('success', 'Product created successfully!');
    }

    public function edit($id)
    {
        abort_if(!auth()->user()->can('product_update'), 403);
        $product = Product::findOrFail($id);
        $brands = Brand::whereStatus(true)->get();
        $categories = Category::whereStatus(true)->get();
        $units = Unit::all();
        return view('backend.products.edit', compact('brands', 'categories', 'units', 'product'));
    }

    public function update(UpdateProductRequest $request, $id)
    {
        abort_if(!auth()->user()->can('product_update'), 403);
        $validated = $request->validated();
        $product = Product::findOrFail($id);
        $oldImage = $product->image;
        $product->update($validated);

        if ($request->hasFile("product_image")) {
            $product->image = $this->fileHandler->fileUploadAndGetPath(
                $request->file("product_image"),
                "/public/media/products"
            );
            $product->save();
            $this->fileHandler->secureUnlink($oldImage);
        }

        return redirect()->route('backend.admin.products.index')->with('success', 'Product updated successfully!');
    }

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
}
