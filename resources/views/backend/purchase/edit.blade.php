@extends('backend.master')

@section('content')
<div class="container">
    <h4>Edit Purchase #{{ $purchase->id }}</h4>
    <form action="{{ route('backend.admin.purchase.update', $purchase->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="supplier_id" class="form-label">Supplier</label>
            <select name="supplier_id" id="supplier_id" class="form-control" required>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" {{ $purchase->supplier_id == $supplier->id ? 'selected' : '' }}>
                        {{ $supplier->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label>Date</label>
            <input type="date" name="date" class="form-control" value="{{ $purchase->date }}">
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
                @foreach ($purchase->items as $item)
                <tr>
                    <td>
                        <select name="products[{{ $loop->index }}][id]" class="form-control" required>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}" {{ $item->product_id == $prod->id ? 'selected' : '' }}>
                                    {{ $prod->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" name="products[{{ $loop->index }}][qty]" class="form-control qty" value="{{ $item->quantity }}" min="1" required></td>
                    <td><input type="number" name="products[{{ $loop->index }}][purchase_price]" class="form-control pprice" value="{{ $item->purchase_price }}" step="0.01" required></td>
                    <td><input type="number" name="products[{{ $loop->index }}][price]" class="form-control sprice" value="{{ $item->price }}" step="0.01" required></td>
                    <td class="total">{{ $item->quantity * $item->purchase_price }}</td>
                    <td><button type="button" class="btn btn-danger remove-row">X</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <button type="button" id="addRow" class="btn btn-secondary mb-3">Add Product</button>

        <div class="mb-3">
            <label>Sub Total</label>
            <input type="number" name="totals[subTotal]" id="subTotal" class="form-control" value="{{ $purchase->sub_total }}" readonly>
        </div>

        <div class="mb-3">
            <label>Grand Total</label>
            <input type="number" name="totals[grandTotal]" id="grandTotal" class="form-control" value="{{ $purchase->grand_total }}" readonly>
        </div>

        <button type="submit" class="btn btn-success">Update Purchase</button>
    </form>
</div>

<script>
document.addEventListener('input', function (e) {
    if (e.target.classList.contains('qty') || e.target.classList.contains('pprice')) {
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
    document.querySelector('#subTotal').value = subTotal.toFixed(2);
    document.querySelector('#grandTotal').value = subTotal.toFixed(2);
}

document.querySelector('#addRow').addEventListener('click', function() {
    let rowCount = document.querySelectorAll('#productBody tr').length;
    let row = `
    <tr>
        <td>
            <select name="products[${rowCount}][id]" class="form-control" required>
                @foreach ($products as $prod)
                    <option value="{{ $prod->id }}">{{ $prod->name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" name="products[${rowCount}][qty]" class="form-control qty" min="1" value="1"></td>
        <td><input type="number" name="products[${rowCount}][purchase_price]" class="form-control pprice" min="0" step="0.01" value="0"></td>
        <td><input type="number" name="products[${rowCount}][price]" class="form-control sprice" min="0" step="0.01" value="0"></td>
        <td class="total">0.00</td>
        <td><button type="button" class="btn btn-danger remove-row">X</button></td>
    </tr>`;
    document.querySelector('#productBody').insertAdjacentHTML('beforeend', row);
});

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-row')) {
        e.target.closest('tr').remove();
        updateTotals();
    }
});
</script>
@endsection
