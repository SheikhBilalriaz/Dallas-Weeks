@extends('dashboard/partials/master')
@section('content')
    <script src="{{ asset('assets/js/invoice.js') }}"></script>
    <style>
        .empty_blacklist img {
            width: 30% !important;
            height: 100% !important;
            margin-bottom: 25px;
        }
    </style>
    @php
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));    
    @endphp
    <section class="blacklist invoice_sec">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="filter_head_row d-flex">
                        <div class="cont">
                            <h3>Invoices</h3>
                            <p>Click on the link to download invoice</p>
                        </div>
                        <div class="filt_opt d-flex">
                            <select name="name" id="seat_options">
                                <option value="all" selected>All seats</option>
                                @foreach ($seats as $seat)
                                    @php
                                        $company_info = \App\Models\Company_Info::find($seat->company_info_id);
                                    @endphp
                                    <option value="{{ 'seat_' . $seat->id }}">
                                        {{ $company_info->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="data_row">
                        <div class="data_head">
                            <table class="data_table w-100">
                                <thead>
                                    <tr>
                                        <th width="25%">Account</th>
                                        <th width="15%">Email</th>
                                        <th width="30%">Invoice data</th>
                                        <th width="15%">Date</th>
                                        <th width="15%">Download invoice</th>
                                    </tr>
                                </thead>
                                <tbody id="invoice_row">
                                    @if ($invoices->isNotEmpty())
                                        @foreach ($invoices as $invoice)
                                            @php
                                                $seat = \App\Models\Seat::find($invoice->seat_id);
                                                $company_info = \App\Models\Company_Info::find($seat->company_info_id);
                                                $seat_info = \App\Models\Seat_Info::find($seat->seat_info_id);
                                                $stripe_invoice = \Stripe\Invoice::retrieve($invoice->invoice_id);
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="{{ asset('assets/img/acc.png') }}" alt="">
                                                        <strong>{{ $company_info->name }}</strong>
                                                    </div>
                                                </td>
                                                <td>{{ $seat_info->email }}</td>
                                                <td class="inv_data">
                                                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem
                                                </td>
                                                <td class="inv_date">
                                                    {{ date('d-M-Y', $stripe_invoice->created) }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('downloadInvoice', ['slug' => $team->slug, 'id' => $invoice->id]) }}"
                                                        class="black_list_activate download">Download</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5">
                                                <div style="width: 50%; margin: 0 auto;"
                                                    class="empty_blacklist text-center">
                                                    <img style="margin-right: 0px;"
                                                        src="{{ asset('assets/img/empty.png') }}" alt="">
                                                    <p>
                                                        Sorry, no results for that query
                                                    </p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        var emptyImage = "{{ asset('assets/img/empty.png') }}";
        var accImage = "{{ asset('assets/img/acc.png') }}";
        var downloadInvoiceRoute = "{{ route('downloadInvoice', ['slug' => $team->slug, 'id' => ':id']) }}";
    </script>
    <script>
        var invoiceBySeatRoute = "{{ route('invoiceBySeat', ['slug' => $team->slug, 'id' => ':id']) }}";
    </script>
@endsection
