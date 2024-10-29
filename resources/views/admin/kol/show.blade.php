@extends('adminlte::page')

@section('title', trans('labels.key_opinion_leader'))

@section('content_header')
    <h1>Key Opinion Leader</h1>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row align-items-center mb-3">
            <div class="col-md-1">
                @if ($keyOpinionLeader->channel === 'tiktok_video')
                    <img src="{{ asset('img/tiktok.png') }}" alt="Channel Logo" class="logo" style="width: 100%; height: 100%;">
                @elseif ($keyOpinionLeader->channel === 'instagram_feed')
                    <img src="{{ asset('img/instagram.png') }}" alt="Channel Logo" class="logo" style="width: 100%; height: 100%;">
                @elseif ($keyOpinionLeader->channel === 'youtube_video')
                    <img src="{{ asset('img/youtube.png') }}" alt="Channel Logo" class="logo" style="width: 100%; height: 100%;">
                @elseif ($keyOpinionLeader->channel === 'twitter_post')
                    <img src="{{ asset('img/x.png') }}" alt="Channel Logo" class="logo" style="width: 100%; height: 100%;">
                @elseif ($keyOpinionLeader->channel === 'shopee_video')
                    <img src="{{ asset('img/shopee.png') }}" alt="Channel Logo" class="logo" style="width: 100%; height: 100%;">
                @endif
            </div>
            <div class="col-md-5">
                <h3>{{ $keyOpinionLeader->username }}</h3>
                <h5>Tiering: {{ $tiering }}</h5>
            </div>
            <div class="col-md-2 text-center">  
                <div class="card">
                    <h2 id="followers-count" class="mt-3">{{ number_format($keyOpinionLeader->followers) }}</h2>
                    <p>Followers</p>
                </div>
            </div>
            <div class="col-md-2 text-center">
                <div class="card">
                    <h2 id="following-count" class="mt-3">{{ number_format($keyOpinionLeader->following) }}</h2>
                    <p>Following</p>
                </div>
            </div>
            <div class="col-md-2 text-center">
                <div class="card">
                    <h2 id="rate-card" class="mt-3">{{ number_format($keyOpinionLeader->rate) }}</h2>
                    <p>Rate Card</p>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12 text-right">
                <button type="button" id="refresh-followers-following" class="btn btn-success">
                <i class="fas fa-sync-alt"></i> Refresh Profile
                </button>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Engagement Rate (ER)</h5>
                    </div>
                    <div class="card-body">
                        <h5>ER Ideal: {{ $er_bottom*100 }}% - {{ $er_top*100 }}%</h5>
                        <h5>ER Actual: {{ number_format($er_actual, 2) }}%</h5>
                        <div class="gauge" id="gauge"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Cost Per Mile (CPM)</h5>
                    </div>
                    <div class="card-body">
                        <h5>CPM Target: Rp. {{ number_format($cpm_target) }}</h5>
                        <h5>CPM Actual: Rp. {{ number_format($cpm_actual) }}</h5>
                        <div class="gauge" id="gauge2"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('css')
<style>
    .gauge {
        width: 100%;
        height: 300px;
        position: relative;
    }

    .gauge-background {
        fill: #f0f0f0;
        stroke: #ccc;
        stroke-width: 2px;
    }

    .gauge-value.green {
        fill: #4CAF50;
        stroke: #357a38;
        stroke-width: 2px;
        transition: all 1s ease-in-out;
    }

    .gauge-value.orange {
        fill: orange;
        stroke: #FFA500;
        stroke-width: 2px;
        transition: all 1s ease-in-out;
    }

    .gauge-label.green {
        font-size: 36px;
        font-weight: bold;
        text-anchor: middle;
        dominant-baseline: central;
        fill: #4CAF50;
    }

    .gauge-label.orange {
        font-size: 36px;
        font-weight: bold;
        text-anchor: middle;
        dominant-baseline: central;
        fill: orange;
    }
</style>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/6.7.0/d3.min.js"></script>
<script>
    const erActual = @json($er_actual);
    const erTop = @json($er_top * 100);  // Converting to percentage
    const erBottom = @json($er_bottom * 100);  // Converting to percentage
    const cpmActual = @json($cpm_actual);
    const cpmTarget = @json($cpm_target);

    function drawGauge(containerId, value, min, max, colorClass) {
        const width = 600;
        const height = 300;
        const radius = Math.min(width, height) / 2 - 30;
        const center = { x: width / 2, y: height / 2 };

        const svg = d3.select(`#${containerId}`)
            .append('svg')
            .attr('width', width)
            .attr('height', height);

        const background = svg.append('g')
            .attr('transform', `translate(${center.x}, ${center.y})`);

        background.append('path')
            .attr('class', 'gauge-background')
            .attr('d', d3.arc()
                .startAngle(0)
                .endAngle(2 * Math.PI)
                .innerRadius(radius * 0.6)
                .outerRadius(radius));

        const valueArc = d3.arc()
            .startAngle(-Math.PI / 2)
            .endAngle((value - min) / (max - min) * Math.PI - Math.PI / 2)
            .innerRadius(radius * 0.6)
            .outerRadius(radius);

        const valueGroup = svg.append('g')
            .attr('class', `gauge-value ${colorClass}`)
            .attr('transform', `translate(${center.x}, ${center.y})`);

        const valuePath = valueGroup.append('path')
            .attr('d', valueArc);

        valueGroup.append('text')
            .attr('class', `gauge-label ${colorClass}`)
            .attr('x', 0)
            .attr('y', 10)
            .text(`${value.toFixed(2)}`);

        // Animate the gauge value
        valuePath.transition()
            .duration(2000)
            .attrTween('d', function(d) {
                const interpolate = d3.interpolate(0, value);
                return function(t) {
                    const currentValue = interpolate(t);
                    const currentValueArc = d3.arc()
                        .startAngle(-Math.PI / 2)
                        .endAngle((currentValue - min) / (max - min) * Math.PI - Math.PI / 2)
                        .innerRadius(radius * 0.6)
                        .outerRadius(radius);
                    return currentValueArc();
                };
            });
    }

    // Determine color class for ER gauge based on condition
    const erColorClass = erActual < erBottom ? 'orange' : 'green';

    // Draw gauge charts with actual values and dynamic color classes
    drawGauge('gauge', erActual, 0, erTop, erColorClass);       // For ER Actual with erTop as max
    drawGauge('gauge2', cpmActual, 0, cpmTarget, 'green'); // For CPM Actual with cpmTarget as max and green color

    document.getElementById('refresh-followers-following').addEventListener('click', function() {
        const url = "{{ route('kol.refresh_follow', ['username' => $keyOpinionLeader->username]) }}";

        Swal.fire({
            title: 'Refreshing...',
            text: 'Updating followers and following counts',
            didOpen: () => {
                Swal.showLoading();
            },
        });

        fetch(url)
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.error,
                    });
                } else {
                    const formatter = new Intl.NumberFormat('en-US');

                    document.getElementById('followers-count').textContent = data.followers;
                    document.getElementById('following-count').textContent = data.following;

                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                }
            })
            .catch(error => {
                Swal.close(); 
                console.error('Error:', error);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while refreshing data. Please try again later.',
                });
            });
    });
</script>
@endsection




