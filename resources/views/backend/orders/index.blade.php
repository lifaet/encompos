@extends('backend.master')

@section('title', 'Sale')

@section('content')
<style>
  /* ✅ Center text horizontally, align header content to top, and prevent wrapping */
  #datatables thead th {
    vertical-align: top !important;  /* align to top */
    text-align: center;              /* center horizontally */
    white-space: nowrap;             /* prevent wrapping */
  }

  /* ✅ also keep table cells aligned nicely */
  #datatables tbody td {
    vertical-align: middle; /* data rows vertically middle */
    white-space: nowrap;    /* prevent wrapping in body too */
  }
</style>
<div class="card">
  <div class="card-body p-0"><!-- 🔹 remove extra padding -->
    <div id="table_data" class="table-responsive"><!-- 🔹 only keep scroll wrapper -->
      <table id="datatables" class="table table-hover mb-0 w-100"><!-- 🔹 full width, no bottom margin -->
        <thead class="table-light"><!-- 🔹 optional: light background for header -->
          <tr>
            <th data-orderable="false">#</th>
            <th>SaleId</th>
            <th>SaleDate</th>
            <th>Customer</th>
            <th>Item</th>
            <th>Sub Total {{ currency()->symbol ?? '' }}</th>
            <th>Discount {{ currency()->symbol ?? '' }}</th>
            <th>Total {{ currency()->symbol ?? '' }}</th>
            <th>Paid {{ currency()->symbol ?? '' }}</th>
            <th>Due {{ currency()->symbol ?? '' }}</th>
            <th>Status</th>
            <th data-orderable="false">Action</th>
          </tr>
        </thead>
      </table>
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
      ordering: true,
      responsive: true,   // ✅ auto adjust
      scrollX: true,      // ✅ allow horizontal scroll if needed
      autoWidth: false,
      order: [[1, 'desc']],
      ajax: {
        url: "{{ route('backend.admin.orders.index') }}"
      },
      columns: [
        { data: 'DT_RowIndex', name: 'DT_RowIndex' },
        { data: 'saleId', name: 'saleId' },
        { data: 'saleDate', name: 'saleDate' },
        { data: 'customer', name: 'customer' },
        { data: 'item', name: 'item' },
        { data: 'sub_total', name: 'sub_total' },
        { data: 'discount', name: 'discount' },
        { data: 'total', name: 'total' },
        { data: 'paid', name: 'paid' },
        { data: 'due', name: 'due' },
        { data: 'status', name: 'status' },
        { data: 'action', name: 'action' },
      ]
    });
  });
</script>
@endpush
