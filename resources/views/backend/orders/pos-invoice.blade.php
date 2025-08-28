@extends('backend.master')
@section('title', 'Receipt_'.$order->id)
@section('content')
<link rel="preload" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
<noscript>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</noscript>
<div class="card">
  <!-- Main content -->
<div class="receipt-container" id="printable-section" style="max-width: {{ $maxWidth }}; font-family:'Roboto', sans-serif !important; font-size: 16px; font-weight: 500; border: 1px solid #ccc; padding: 15px; margin: 5px;">
  
  <!-- Header -->
  <div class="text-center" style="margin-bottom: 10px;">
    @if(readConfig('is_show_site_invoice'))
      <h5 style="margin: 0;"><strong>{{ readConfig('site_name_extended') }}</strong></h5>
      <p style="margin: 2px 0; font-size: 13px; color: #333;">
        Branch: {{$current_db}}
       </p>
    @endif
    @if(readConfig('is_show_address_invoice'))
      <p style="margin: 2px 0;">{{ readConfig('contact_address') }}</p>
    @endif
    @if(readConfig('is_show_email_invoice'))
      <p style="margin: 2px 0;">Email: {{ readConfig('contact_email') }}</p>
    @endif
        @if(readConfig('is_show_phone_invoice'))
      <p style="margin: 2px 0;">Phone: {{ readConfig('contact_phone') }}</p>
    @endif
  </div>

  <!-- Order Info -->
  <div style="margin-bottom: 10px;">
    <strong>Seller:</strong> {{ $order->seller->name ?? 'N/A' }}<br>
    <strong>Order #:</strong> {{ $order->id }}
  </div>

  <hr style="border-top: 1px dashed #999;">

  <!-- Customer & Date Info -->
  <div class="row" style="display: flex; justify-content: space-between; margin-bottom: 10px;">
    <div class="customer-info">
      @if(readConfig('is_show_customer_invoice'))
        <strong>Customer:</strong><br>
        {{ $order->customer->name ?? 'N/A' }}<br>

        @if(!empty($order->customer->address))
          {{ $order->customer->address }}<br>
        @endif

        @if(!empty($order->customer->phone))
          {{ $order->customer->phone }}
        @endif
      @endif
    </div>
    <div class="date-info" style="text-align: right;">
      <strong>Date:</strong> {{ date('d-M-Y', strtotime($order->created_at)) }}<br>
      <strong>Time:</strong> {{ date('h:i:s A', strtotime($order->created_at)) }}
    </div>
  </div>

  <hr style="border-top: 1px dashed #999;">

  <!-- Product Table -->
  <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
    <thead>
      <tr style="border-bottom: 1px solid #ddd;">
        <th style="text-align: left; padding: 5px 0;">Product</th>
        <th style="text-align: center; padding: 5px 0;">Qty x Price</th>
        <th style="text-align: right; padding: 5px 0;">Total</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($order->products as $item)
      <tr>
        <td style="padding: 5px 0;">{{ $item->product->name }}</td>
        <td style="text-align: center; padding: 5px 0;">{{ $item->quantity }} x {{ number_format((float)$item->discounted_price, 2) }}</td>
        <td style="text-align: right; padding: 5px 0;">{{ number_format($item->total, 2) }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <hr style="border-top: 1px dashed #999;">

  <!-- Summary -->
  <table style="width: 100%; margin-bottom: 10px; text-align: right;" >
    <tr>
      <td>Subtotal:</td>
      <td >{{ number_format($order->sub_total, 2) }}</td>
    </tr>
    <tr>
      <td>Discount:</td>
      <td >{{ number_format($order->discount, 2) }}</td>
    </tr>
    <tr>
      <td><strong>Total:</strong></td>
      <td ><strong>{{ number_format($order->total, 2) }}</strong></td>
    </tr>
    <tr>
      <td>Paid:</td>
      <td >{{ number_format($order->paid, 2) }}</td>
    </tr>
    <tr >
      <td>Due:</td>
      <td >{{ number_format($order->due, 2) }}</td>
    </tr>
  </table>

  <hr style="border-top: 1px dashed #999;">

  <!-- Footer / Note -->
  @if(readConfig('is_show_note_invoice'))
    <div class="text-center" style="font-size: 12px; color: #666;">
      {{ readConfig('note_to_customer_invoice') }}
    </div>
  @endif

</div>


  <!-- Print Button -->
  <div class="text-center mt-3 no-print pb-3">
    <button type="button" onclick="window.print()" class="btn bg-gradient-primary text-white"><i class="fas fa-print"></i> Print</button>
  </div>
</div>
@endsection

@push('style')
<style>
  .receipt-container {
    border: 1px dotted #000;
    padding: 8px;
  }

  hr {
    border: none;
    border-top: 1px dashed #000;
    margin: 5px 0;
  }

  table {
    width: 100%;
  }

  td,
  th {
    padding: 2px 0;
  }

  .text-right {
    text-align: right;
  }

  @media print {
    @page {
      margin-top: 5px !important;
      margin-left: 0px !important;
      padding-left: 0px !important;
    }

    footer {
      display: none !important;
    }
  }
</style>
@endpush

@push('script')
<script>
  window.print();
</script>
@endpush