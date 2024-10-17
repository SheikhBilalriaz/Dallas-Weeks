@extends('back/partials/header')
@section('content')
    <script src="{{ asset('assets/js/chart_query.js') }}"></script>
    <section class="main_dashboard blacklist  report_sec ">
        <div class="container_fluid">
            <div class="row">
                <div class="col-lg-1">
                    @include('back/partials/sidebar')
                </div>
                <div class="col-lg-11 col-sm-12">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="report_head_tabs d-flex justify-content-between align-items-center">
                                <h3>Reports</h3>
                                <ul class="d-flex list-unstyled justify-content-end p-0 m-0 ">
                                    <li><a href="javascript:;" class="active">Main graph</a></li>
                                    <li><a href="#table">Table view</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="chart_box">
                                <div class="border_box">
                                    <div class="chart_filter d-flex justify-content-between">
                                        <h4>Main graph</h4>
                                    </div>
                                    <div class="chart_canvas_report">
                                        <div id="chartContainer" style="height: 388px; width: 100%;"></div>
                                    </div>
                                    <ul class="chart_status d-flex justify-content-between list-unstyled p-0">
                                        <li><span></span>Views</li>
                                        <li><span></span>Follows</li>
                                        <li><span></span>Connections sent</li>
                                        <li><span></span>Invite via email sent</li>
                                        <li><span></span>Messages sent</li>
                                        <li><span></span>InMails sent</li>
                                        <li><span></span>Emails sent</li>
                                        <li><span></span>Connections accepted</li>
                                        <li><span></span>Replies Received</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12" id="table">
                            <div class="chart_data_list">
                                <div class="border_box">
                                    <h4>Table view</h4>
                                    <div class="scroll_div">
                                        <table class="data_table w-100" id="chat_table">
                                            <thead>
                                                <tr>
                                                    <th width="15%">Date</th>
                                                    <th width="20%">Views</th>
                                                    <th width="30%">Email sent</th>
                                                    <th width="20%" class="">Follows</th>
                                                    <th width="15%">Connections sent</th>
                                                </tr>
                                            </thead>
                                            <tbody id="report_data">
                                                @php
                                                    $total_view = 0;
                                                    $total_email = 0;
                                                    $total_follow = 0;
                                                    $total_invite = 0;
                                                @endphp
                                                @if (!empty($reports))
                                                    @foreach ($reports as $date => $counts)
                                                        @php
                                                            $total_view += $counts['view_count'];
                                                            $total_email += $counts['email_count'];
                                                            $total_follow += $counts['follow_count'];
                                                            $total_invite += $counts['invite_count'];
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $date }}</td>
                                                            <td>{{ $counts['view_count'] ?? 0 }}</td>
                                                            <td>{{ $counts['email_count'] ?? 0 }}</td>
                                                            <td>{{ $counts['follow_count'] ?? 0 }}</td>
                                                            <td>{{ $counts['invite_count'] ?? 0 }}</td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td>{{ now()->format('Y-m-d') }}</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                        <td>0</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td>Total</td>
                                                    <td>{{ $total_view }}</td>
                                                    <td>{{ $total_email }}</td>
                                                    <td>{{ $total_follow }}</td>
                                                    <td>{{ $total_invite }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
