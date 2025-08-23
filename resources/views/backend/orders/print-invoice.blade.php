@extends('backend.master')
@section('title', 'Invoice_'.$order->id)
@section('content')
<div class="container bg-white p-4" style="font-family:Arial,sans-serif; color:#333;">
  <div class="invoice-wrapper" id="invoice-content">
      <!-- HEADER -->
      <div class="invoice-header" style="display:flex; justify-content:space-between; align-items:flex-start; border-bottom:1px solid #ccc; padding-bottom:15px; margin-bottom:10px;">
          <div class="company-info" style="display:flex; align-items:center;">
              <!-- @if(readConfig('is_show_logo_invoice'))
              <img src="{{ assetImage(readConfig('site_logo')) }}" height="60" style="max-width:80px; margin-right:15px;" alt="Logo">
              @endif -->
              <div>
                  <h2 style="margin:0; font-size:24px;">{{ readConfig('site_name_extended') }}</h2>
                  @if(readConfig('is_show_address_invoice'))<div>{{ readConfig('contact_address') }}</div>@endif
                  @if(readConfig('is_show_phone_invoice'))<div>Phone: {{ readConfig('contact_phone') }}</div>@endif
                  @if(readConfig('is_show_email_invoice'))<div>Email: {{ readConfig('contact_email') }}</div>@endif
              </div>
          </div>
          <div class="invoice-title" style="text-align:right;">
              <h3 style="margin:0;">Invoice</h3>
              <div>Date: {{ date('d/m/Y') }}</div>
              <div>Invoice #: {{ $order->id }}</div>
          </div>
      </div>

      <!-- CUSTOMER & SALE INFO -->
      <div style="display:flex; justify-content:space-between; margin-bottom:20px;">
          <div style="width:48%;">
              <strong>Bill To:</strong>
              <div>{{ $order->customer->name ?? "N/A" }}</div>
              <div>{{ $order->customer->address ?? "N/A" }}</div>
              <div>Phone: {{ $order->customer->phone ?? "N/A" }}</div>
          </div>
          <div style="width:48%; text-align:right;">
              <strong>Sale Info:</strong>
              <div>Sale Date: {{ date('d/m/Y', strtotime($order->created_at)) }}</div>
              @if($order->note)<div><em>{{ $order->note }}</em></div>@endif
          </div>
      </div>

      <!-- PRODUCTS TABLE -->
      <table style="width:100%; border-collapse:collapse;">
          <thead style="background:#f5f5f5;">
              <tr>
                  <th style="width:5%; border:1px solid #dee2e6; padding:2px;">#</th>
                  <th style="width:45%; border:1px solid #dee2e6; padding:2px;">Product</th>
                  <th style="width:10%; border:1px solid #dee2e6; padding:2px; text-align:center;">Qty</th>
                  <th style="width:20%; border:1px solid #dee2e6; padding:2px; text-align:center;">Unit Price ({{currency()->symbol??''}})</th>
                  <th style="width:20%; border:1px solid #dee2e6; padding:2px; text-align:center;">Subtotal ({{currency()->symbol??''}})</th>
              </tr>
          </thead>
          <tbody>
              @foreach ($order->products as $item)
              <tr>
                  <td style="border:1px solid #dee2e6; padding:2px; text-align:center;">{{ $loop->iteration }}</td>
                  <td style="border:1px solid #dee2e6; padding:2px;">{{ $item->product->name }}</td>
                  <td style="border:1px solid #dee2e6; padding:2px; text-align:center;">{{ $item->quantity }} {{ optional($item->product->unit)->short_name }}</td>
                  <td style="border:1px solid #dee2e6; padding:2px; text-align:right;">{{ number_format($item->discounted_price, 2) }}</td>
                  <td style="border:1px solid #dee2e6; padding:2px; text-align:right;">{{ number_format($item->total, 2) }}</td>
              </tr>
              @endforeach
          </tbody>
      </table>

      <!-- TOTALS -->
      <div style="display:flex; justify-content:flex-end;">
          <div style="width:40%;">
              <table style="width:100%; border-collapse:collapse;">
                  <!-- <tr>
                      <th style="text-align:right; padding:2px;">Subtotal:</th>
                      <td style="text-align:right; padding:2px;">{{ currency()->symbol.' '.number_format($order->sub_total,2) }}</td>
                  </tr> -->
                  <tr>
                      <th style="text-align:right; padding:2px;">Discount:</th>
                      <td style="text-align:right; padding:2px;">{{ currency()->symbol.' '.number_format($order->discount,2) }}</td>
                  </tr>
                  <tr>
                      <th style="text-align:right; padding:2px;">Total:</th>
                      <td style="text-align:right; padding:2px;">{{ currency()->symbol.' '.number_format($order->total,2) }}</td>
                  </tr>
                  <tr>
                      <th style="text-align:right; padding:2px;">Paid:</th>
                      <td style="text-align:right; padding:2px;">{{ currency()->symbol.' '.number_format($order->paid,2) }}</td>
                  </tr>
                  <tr>
                      <th style="text-align:right; padding:2px;">Due:</th>
                      <td style="text-align:right; padding:2px;">{{ currency()->symbol.' '.number_format($order->due,2) }}</td>
                  </tr>
              </table>
          </div>
      </div>

      <!-- FOOTER / CUSTOMER NOTE -->
      <div class="invoice-footer" style="text-align:center; font-style:italic; color:#555;">
          @if(readConfig('is_show_note_invoice'))
          <p>{{ readConfig('note_to_customer_invoice') }}</p>
          @else
          <p>Thank you for your business! Please come again.</p>
          @endif
      </div>
  </div>

  <!-- CENTERED PRINT BUTTON -->
  <div style="text-align:center;">
      <button class="btn btn-success" onclick="printInvoice()">Print Invoice</button>
  </div>
</div>
@endsection

@push('script')
<script>
function printInvoice() {
    // Create hidden iframe
    var iframe = document.createElement('iframe');
    iframe.style.position = 'absolute';
    iframe.style.width = '0';
    iframe.style.height = '0';
    iframe.style.border = '0';
    iframe.style.visibility = 'hidden';
    document.body.appendChild(iframe);

    var doc = iframe.contentWindow.document;
    doc.open();
    doc.write('<html><head><title>Invoice</title><style>');
    doc.write(`
        body { font-family: Arial,sans-serif; color:#333; }
        .company-info { display:flex; align-items:center; }
        table { width:100%; border-collapse: collapse;}
        th, td { border:1px solid #dee2e6;}
        th { background:#f5f5f5; }
        td { text-align:right; }
        td:nth-child(2), td:nth-child(1), td:nth-child(3) { text-align:center; }
        .invoice-footer { position: absolute; bottom: 0mm; width: 100%; text-align:center; font-style:italic; color:#555; }
    `);
    doc.write('</style></head><body>');
    doc.write(document.getElementById('invoice-content').outerHTML);
    doc.write('<script>window.onload=function(){ window.print(); setTimeout(()=>window.close(),500); }<\/script>');
    doc.write('</body></html>');
    doc.close();
}
</script>
@endpush
