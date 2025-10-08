@extends('backend.master')

@section('title', 'Create New Purchase')

@section('content')
<div class="container">
    <form action="{{ route('backend.admin.purchase.store') }}" method="POST">
        @csrf

        <div class="mb-3">
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

        <div class="mb-3">
            <label for="date" class="form-label">Purchase Date</label>
            <input type="date" name="date" class="form-control" value="{{ old('date', now()->toDateString()) }}">
        </div>

        <hr>
        <h5>Products</h5>

        <table class="table table-bordered" id="productTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Purchase Price</th>
                    <th>Sell Price</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="productBody">
                <tr>
                    <td>
                        <select name="products[0][id]" class="form-control" required>
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="products[0][qty]" class="form-control qty" min="1" value="1" required></td>
                    <td><input type="number" name="products[0][purchase_price]" class="form-control pprice" min="0" step="0.01" value="0" required></td>
                    <td><input type="number" name="products[0][price]" class="form-control sprice" min="0" step="0.01" value="0" required></td>
                    <td class="total">0.00</td>
                    <td><button type="button" class="btn btn-danger remove-row">X</button></td>
                </tr>
            </tbody>
        </table>

        <button type="button" id="addRow" class="btn btn-secondary mb-3">Add Product</button>

        <div class="mb-3">
            <label>Sub Total</label>
            <input type="number" name="totals[subTotal]" id="subTotal" class="form-control" placeholder="Subtotal" readonly required>
        </div>

        <div class="mb-3">
            <label>Tax</label>
            <input type="number" name="totals[tax]" id="tax" class="form-control" placeholder="Tax">
        </div>

        <div class="mb-3">
            <label>Discount</label>
            <input type="number" name="totals[discount]" id="discount" class="form-control" placeholder="Discount">
        </div>

        <div class="mb-3">
            <label>Shipping</label>
            <input type="number" name="totals[shipping]" id="shipping" class="form-control" placeholder="Shipping">
        </div>

        <div class="mb-3">
            <label>Grand Total</label>
            <input type="number" name="totals[grandTotal]" id="grandTotal" class="form-control" placeholder="Grand Total" readonly required>
        </div>

        <button type="submit" class="btn btn-primary">Make Purchase</button>
    </form>
</div>

<script>
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty') || 
        e.target.classList.contains('pprice') || 
        e.target.id === 'tax' || 
        e.target.id === 'discount' || 
        e.target.id === 'shipping') {
        updateTotals();
    }
});

function updateTotals() {
    let subTotal = 0;
    document.querySelectorAll('#productBody tr').forEach(tr => {
        let qty = parseFloat(tr.querySelector('.qty').value) || 0;
        let pprice = parseFloat(tr.querySelector('.pprice').value) || 0;
        let total = qty * pprice;
        tr.querySelector('.total').textContent = total.toFixed(2);
        subTotal += total;
    });

    const tax = parseFloat(document.querySelector('#tax').value) || 0;
    const discount = parseFloat(document.querySelector('#discount').value) || 0;
    const shipping = parseFloat(document.querySelector('#shipping').value) || 0;

    document.querySelector('#subTotal').value = subTotal.toFixed(2);
    document.querySelector('#grandTotal').value = (subTotal + tax + shipping - discount).toFixed(2);
}

document.querySelector('#addRow').addEventListener('click', function() {
    let rowCount = document.querySelectorAll('#productBody tr').length;
    let row = `
    <tr>
        <td>
            <select name="products[${rowCount}][id]" class="form-control" required>
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="products[${rowCount}][qty]" class="form-control qty" min="1" value="1" required></td>
        <td><input type="number" name="products[${rowCount}][purchase_price]" class="form-control pprice" min="0" step="0.01" value="0" required></td>
        <td><input type="number" name="products[${rowCount}][price]" class="form-control sprice" min="0" step="0.01" value="0" required></td>
        <td class="total">0.00</td>
        <td><button type="button" class="btn btn-danger remove-row">X</button></td>
    </tr>`;
    document.querySelector('#productBody').insertAdjacentHTML('beforeend', row);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        const rows = document.querySelectorAll('#productBody tr');
        if (rows.length > 1) {
            e.target.closest('tr').remove();
            updateTotals();
        }
    }
});
</script>
@endsection
