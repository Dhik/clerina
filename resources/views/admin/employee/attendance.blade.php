@extends('adminlte::page')

@section('title', 'Attendance App')

@section('content_header')
    <h1></h1>
@stop

@section('content')
    <div class="container mt-5">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-lg-12 col-12">
                        <div class="small-box bg-maroon">
                            <div class="inner">
                                <h2>Hello, Elvara Arlianda</h2>
                                <h1 id='clock'>18:51</h1>
                                <p>13 June 2024</p>
                                <p>Shift: HO (08.00 - 16.30)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex justify-content-center mt-3">
            <form id="clockInForm" action="{{ route('attendance.clockin') }}" method="POST">
                @csrf
                <button type="submit" id="clockInBtn" class="btn btn-primary mx-2 @if($attendance && $attendance->clock_in) d-none @endif" disabled>Clock In</button>
            </form>
            <form id="clockOutForm" action="{{ route('attendance.clockout') }}" method="POST">
                @csrf
                <button type="submit" id="clockOutBtn" class="btn btn-secondary mx-2 @if(!$attendance || !$attendance->clock_in || $attendance->clock_out) d-none @endif">Clock Out</button>
            </form>
        </div>
        
        @if($attendance)
            <div class="container mt-3">
                <div class="row">
                    <div class="col">
                        <div class="card text-center">
                            <div class="card-body">
                                <img src="{{ asset('img/clock_in.png') }}" alt="Clock In Icon" class="img-fluid" style="max-width: 50px;">
                                <h5 class="card-title">08:30</h5>
                                <p class="card-text">Clock In</p>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card text-center">
                            <div class="card-body">
                                <img src="{{ asset('img/clock_out.png') }}" alt="Clock Out Icon" class="img-fluid" style="max-width: 50px;">
                                <h5 class="card-title">18:51</h5>
                                <p class="card-text">Clock Out</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 3rem;
            margin: 20px auto;
            transition: background-color 0.5s ease, transform 0.3s ease;
        }
        .circle:hover {
            background-color: #e2e6ea;
            transform: scale(1.05);
        }
        .photo-box {
            transition: transform 0.3s ease;
        }
        .photo-box:hover {
            transform: scale(1.05);
        }
        .btn {
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .btn:hover {
            transform: scale(1.1);
        }
        .circle-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: transform 0.3s ease;
        }
        .circle-img:hover {
            transform: scale(1.1);
        }
        .img-fluid:hover {
            transform: rotate(360deg);
            transition: transform 0.6s ease;
        }
        .font-large {
            font-size: 1.5rem;
        }
    </style>
@stop

@section('js')
    <script>
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const currentTime = `${hours}:${minutes}`;
            document.getElementById('clock').textContent = currentTime;
        }

        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371; // Radius of the earth in km
            const dLat = deg2rad(lat2 - lat1);  // deg2rad below
            const dLon = deg2rad(lon2 - lon1); 
            const a = 
                Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                Math.sin(dLon / 2) * Math.sin(dLon / 2); 
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a)); 
            const d = R * c; // Distance in km
            return d;
        }

        function deg2rad(deg) {
            return deg * (Math.PI / 180);
        }

        $(document).ready(function() {
            setInterval(updateClock, 1000); // Update every second
            updateClock(); // Initial call to set the time immediately

            const targetLat = -6.969788604742309;
            const targetLng = 107.63952348097007;

            // Check if geolocation is available
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition((position) => {
                    const userLat = position.coords.latitude;
                    const userLng = position.coords.longitude;

                    // Calculate the distance to the target
                    const distance = calculateDistance(userLat, userLng, targetLat, targetLng);

                    // Enable the button if within 20km radius
                    if (distance <= 20) {
                        document.getElementById("clockInBtn").disabled = false;
                    }
                }, (error) => {
                    console.error("Error getting location: ", error);
                });
            } else {
                console.error("Geolocation is not supported by this browser.");
            }
        });
    </script>
@stop
