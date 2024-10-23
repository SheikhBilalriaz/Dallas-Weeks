<div class="col-lg-4">
    <style>
        .arrow-list {
            list-style-type: none;
            padding-left: 20px;
        }

        .arrow-list li {
            position: relative;
            margin-bottom: 10px;
        }

        .arrow-list li::before {
            content: "> ";
            position: absolute;
            left: -20px;
            font-size: 18px;
            color: #fff;
        }
    </style>
    <div class="sidebar dashboard dashboard_cont">
        <div class="">
            <h4>Teams</h4>
            <ul class="arrow-list">
                @if ($teams->isNotEmpty())
                    @foreach ($teams as $item)
                        <li><a href="{{ route('dashboardPage', ['slug' => $item->slug]) }}">{{ $item->name }}</a></li>
                    @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>
