@extends('backend.master')

@section('title', 'Sale Report')

@section('content')
<div class="card">
  <div class="mt-n5 mb-3 d-flex justify-content-end">
    <div class="form-group">
      <div class="input-group">
        <button type="button" class="btn btn-default float-right" id="daterange-btn">
          <i class="far fa-calendar-alt"></i> Filter by date
          <i class="fas fa-caret-down"></i>
        </button>
      </div>
    </div>
  </div>
  <div class="card-body p-2 p-md-4 pt-0">
    <div class="row g-4">
      <div class="col-md-12">
        <div class="card-body p-0">
          <section class="invoice">
            <!-- info row -->
            <div class="row invoice-info">
              <div class="col-sm-4">
              </div>
              <div class="col-sm-4">
                <address>
                  <strong>Sale Report ({{$start_date}} - {{$end_date}})</strong><br>
                </address>
              </div>
              <div class="col-sm-2">
              </div>
            </div>

            <!-- Table row -->
            <div class="row justify-content-center">
              <div class="col-12 table-responsive">
                <table id="datatables" class="table table-hover">
                  <thead>
                    <tr>
                      <th data-orderable="false">#</th>
                      <th>SaleId</th>
                      <th>Customer</th>
                      <th>Date</th>
                      <th>Item</th>
                      <th>SubTotal {{currency()->symbol??''}}</th>
                      <th>Discount {{currency()->symbol??''}}</th>
                      <th>Total {{currency()->symbol??''}}</th>
                      <th>Paid {{currency()->symbol??''}}</th>
                      <th>Due {{currency()->symbol??''}}</th>
                      <th>Profit {{currency()->symbol??''}}</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </section>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
@push('style')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<style>
  .invoice {
    border: none !important;
  }
  .dataTables_length select {
    margin-right: 6px;
    height: 37px !important;
    border: 1px solid rgba(0, 0, 0, 0.3);
  }
  .dataTables_length label {
    display: flex;
    align-items: center;
  }
</style>
@endpush

@push('script')
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
  $(function() {
  const orders = @json($orders); // Laravel will inject your PHP data as JSON

  const tableData = orders.map((order, index) => ({
    index: index + 1,
    id: '#' + order.id,
    customer: order.customer?.name ?? '-',
    date: moment(order.created_at).format('DD-MM-YYYY'),
    total_item: order.total_item,
    sub_total: Number(order.sub_total).toFixed(2),
    discount: Number(order.discount).toFixed(2),
    total: Number(order.total).toFixed(2),
    paid: Number(order.paid).toFixed(2),
    due: Number(order.due).toFixed(2),
    profit: Number(order.profit).toFixed(2),
    status: order.status ? 'Paid' : 'Due'
  }));

  $('#datatables').DataTable({
    data: tableData,
    columns: [
      { data: 'index', title: '#' },
      { data: 'id', title: 'SaleId' },
      { data: 'customer', title: 'Customer' },
      { data: 'date', title: 'Date' },
      { data: 'total_item', title: 'Item' },
      { data: 'sub_total', title: 'Sub Total' },
      { data: 'discount', title: 'Discount' },
      { data: 'total', title: 'Total' },
      { data: 'paid', title: 'Paid' },
      { data: 'due', title: 'Due' },
      { data: 'profit', title: 'Profit' },
      { data: 'status', title: 'Status' }
    ],
    dom: 'lBfrtip',
    buttons: [
      'excel',
      {
        extend: 'pdfHtml5',
        text: 'PDF',
        exportOptions: {
          columns: ':visible'
        },
        customize: function (doc) {
          for (let i = 0; i < doc.content[1].table.body.length; i++) {
            for (let j = 0; j < doc.content[1].table.body[i].length; j++) {
              if (typeof doc.content[1].table.body[i][j].text === 'string') {
                doc.content[1].table.body[i][j].text =
                  doc.content[1].table.body[i][j].text.replace(/[৳$€£¥₹]/g, '');
              }
            }
          }
        }
      },
      'print'
    ],
    order: [[3, 'desc']],
    pageLength: 25,
    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
  });
});
</script>
@endpush
