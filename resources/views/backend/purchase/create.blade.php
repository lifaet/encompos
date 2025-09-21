@extends('backend.master')

@section('title', 'Product Purchase')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('backend.admin.purchase.store') }}" method="POST">
            @csrf

            {{-- Supplier and Date --}}
            <div class="row mb-3">
                <div class="col-md-6 p-1">
                    <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                    <select name="supplier_id" id="supplier_id" class="form-control" required>
                        <option value="">Select Supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 p-1">
                    <label for="date" class="form-label">Purchase Date</label>
                    <input type="date" name="date" class="form-control" value="{{ old('date', \Carbon\Carbon::now()->toDateString()) }}">
                </div>
            </div>

            <hr>

            {{-- Products --}}
            <h5>Products</h5>
            <div id="products-wrapper">
                <div class="product-row row mb-2">
                    <div class="col-md-4 p-1">
                        <select name="products[0][id]" class="form-control" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 p-1">
                        <input type="number" name="products[0][qty]" class="form-control" placeholder="Qty" required>
                    </div>
                    <div class="col-md-2 p-1">
                        <input type="number" name="products[0][purchase_price]" class="form-control" placeholder="Purchase Price" required>
                    </div>
                    <div class="col-md-2 p-1">
                        <input type="number" name="products[0][price]" class="form-control" placeholder="Sale Price" required>
                    </div>
                    <div class="col-md-2 p-1">
                        <button type="button" class="btn btn-danger remove-product">X</button>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-sm btn-secondary mb-3" id="add-product">Add Product</button>

            <hr>

            {{-- Totals --}}
            <div class="row mb-3">
                <div class="col-md-2 p-1">
                    <input type="number" name="totals[subTotal]" class="form-control" placeholder="Subtotal" required readonly>
                </div>
                <div class="col-md-2 p-1">
                    <input type="number" name="totals[tax]" class="form-control" placeholder="Tax">
                </div>
                <div class="col-md-2 p-1">
                    <input type="number" name="totals[discount]" class="form-control" placeholder="Discount">
                </div>
                <div class="col-md-2 p-1">
                    <input type="number" name="totals[shipping]" class="form-control" placeholder="Shipping">
                </div>
                <div class="col-md-2 p-1">
                    <input type="number" name="totals[grandTotal]" class="form-control" placeholder="Grand Total" required readonly>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Save Purchase</button>
        </form>
    </div>
</div>
@endsection

@push('script')
<script>
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('#products-wrapper .product-row').forEach(row => {
        const qty = parseFloat(row.querySelector('input[name*="[qty]"]').value) || 0;
        const price = parseFloat(row.querySelector('input[name*="[purchase_price]"]').value) || 0;
        subtotal += qty * price;
    });

    document.querySelector('input[name="totals[subTotal]"]').value = subtotal.toFixed(2);

    const tax = parseFloat(document.querySelector('input[name="totals[tax]"]').value) || 0;
    const discount = parseFloat(document.querySelector('input[name="totals[discount]"]').value) || 0;
    const shipping = parseFloat(document.querySelector('input[name="totals[shipping]"]').value) || 0;

    const grandTotal = subtotal + tax + shipping - discount;
    document.querySelector('input[name="totals[grandTotal]"]').value = grandTotal.toFixed(2);
}

// Recalculate totals on input
document.addEventListener('input', function(e){
    if (e.target.matches('input[name*="[qty]"], input[name*="[purchase_price]"], input[name="totals[tax]"], input[name="totals[discount]"], input[name="totals[shipping]"]')) {
        calculateTotals();
    }
});

// Add product row
document.getElementById('add-product').addEventListener('click', function(){
    const wrapper = document.getElementById('products-wrapper');
    const rows = wrapper.querySelectorAll('.product-row');
    const lastRow = rows[rows.length - 1];
    const newRow = lastRow.cloneNode(true);

    // Clear values
    newRow.querySelectorAll('input, select').forEach(input => input.value = '');

    // Update input names with new index
    const index = rows.length;
    newRow.querySelector('select[name*="[id]"]').name = `products[${index}][id]`;
    newRow.querySelector('input[name*="[qty]"]').name = `products[${index}][qty]`;
    newRow.querySelector('input[name*="[purchase_price]"]').name = `products[${index}][purchase_price]`;
    newRow.querySelector('input[name*="[price]"]').name = `products[${index}][price]`;

    wrapper.appendChild(newRow);
    newRow.querySelector('select').focus(); // focus newly added select
    calculateTotals();
});

// Remove product row (delegate click to wrapper to avoid interfering with selects)
document.getElementById('products-wrapper').addEventListener('click', function(e){
    if(e.target.classList.contains('remove-product')){
        const rows = document.querySelectorAll('#products-wrapper .product-row');
        if(rows.length > 1){ // always keep at least one row
            e.target.closest('.product-row').remove();
            calculateTotals();
        }
    }
});
</script>
@endpush
