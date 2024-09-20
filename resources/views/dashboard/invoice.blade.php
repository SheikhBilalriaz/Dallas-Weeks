@extends('dashboard/partials/master')
@section('content')
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
                            <select name="name" id="name">
                                <option value="all" selected>All team members</option>
                                @foreach ($members as $member)
                                    @php
                                        $member_details = \App\Models\User::find($member->user_id);
                                    @endphp
                                    <option value="{{ $member_details->id }}">
                                        {{ $member_details->name }}
                                    </option>
                                @endforeach
                            </select>
                            <select name="num" id="num">
                                <option value="01">10</option>
                                <option value="02">20</option>
                                <option value="03">30</option>
                                <option value="04">40</option>
                            </select>
                        </div>
                    </div>
                    {{-- <div class="data_row">
                        <div class="data_head">
                            <table class="data_table w-100">
                                <thead>
                                    <tr>
                                        <th width="15%">Account</th>
                                        <th width="15%">Email</th>
                                        <th width="40%">Invoice data</th>
                                        <th width="15%">Date</th>
                                        <th width="15%">Download invoice</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @for ($i = 0; $i < 7; $i++)
                                        @php
                                            $asset_id = $i + 1;
                                            $asset = 'assets/img/acc_img' . $asset_id . '.png';
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center"><img src="{{ asset($asset) }}"
                                                        alt=""><strong>John doe</strong></div>
                                            </td>
                                            <td>info@johndoe.com</td>
                                            <td class="inv_data">Sed ut perspiciatis unde omnis iste natus error sit
                                                voluptatem</td>
                                            <td class="inv_date">28 August 2023</td>

                                            <td><a href="javascript:;" class="black_list_activate download">Download</a>
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </section>
@endsection
