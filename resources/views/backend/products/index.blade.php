@extends('backend.master')

@section('title', 'Products')

@section('content')
<div class="card">
  @can('product_create')
  <div class="mt-n5 mb-3 d-flex justify-content-end">
    <a href="{{ route('backend.admin.products.create') }}" class="btn bg-gradient-primary">
      <i class="fas fa-plus-circle"></i>
      Add New
    </a>
  </div>
  @endcan

  <div class="card-body p-2 p-md-4 pt-0">
    <div class="row g-4">
      <div class="col-md-12">
        <!-- Make table scrollable -->
        <div class="table-responsive">
          <table id="datatables" class="table table-hover w-100">
            <thead class="table-light">
              <tr>
                <th style="width:40px;">#</th>
                <th style="width:60px;">ID</th>
                <th style="min-width:150px;">Name</th>
                <th style="min-width:120px;">SKU</th>
                <th style="min-width:120px;">Category</th>
                <th style="min-width:120px;">Brand</th>
                <th style="min-width:100px;">Unit</th>
                <th style="min-width:100px;">Price {{ currency()->symbol ?? '' }}</th>
                <th style="min-width:100px;">Discount</th>
                <th style="min-width:120px;">Discount Type</th>
                <th style="min-width:130px;">Purchase Price</th>
                <th style="min-width:100px;">Quantity</th>
                <th style="min-width:130px;">Expire Date</th>
                <th style="min-width:100px;">Status</th>
                <th style="min-width:120px;">Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('script')
<script type="text/javascript">
  $(function() {
    $('#datatables').DataTable({
      processing: true,
      serverSide: true,
      scrollX: true, // horizontal scroll
      ajax: {
        url: "{{ route('backend.admin.products.index') }}"
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'id', name: 'id' },
        { data: 'name', name: 'name' },
        { data: 'sku', name: 'sku' },
        { data: 'category_name', name: 'category_name' },
        { data: 'brand_name', name: 'brand_name' },
        { data: 'unit_name', name: 'unit_name' },
        { data: 'price', name: 'price' },
        { data: 'discount', name: 'discount' },
        { data: 'discount_type', name: 'discount_type' },
        { data: 'purchase_price', name: 'purchase_price' },
        { data: 'quantity', name: 'quantity' },
        { data: 'expire_date', name: 'expire_date' },
        { data: 'status', name: 'status' },
        { data: 'action', name: 'action', orderable: false, searchable: false },
      ]
    });
  });
</script>
@endpush
