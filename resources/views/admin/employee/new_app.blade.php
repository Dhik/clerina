@extends('attendance.base')

@section('title', 'Attendance App')

@section('navbar')
<nav class="navbar navbar-custom">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
        <img src="{{ $profile_picture ? asset('storage/' . $profile_picture) : asset('img/user.png') }}" alt="Profile Picture" width="60" class="rounded-circle shadow">
            <div class="info ml-3">
                <h5 class="font-weight-bold">Hello, {{ $full_name }}</h5>
                <p class="text-muted">{{ $shift_name }} (08.00 - 16.30)</p>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-muted">{{ \Carbon\Carbon::now()->format('Y.m.d') }}</span>
            <button class="btn btn-danger ml-3 shadow" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <span class="fas fa-sign-out-alt"></span> Logout
            </button>
            <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
    </div>
</nav>
@stop

@section('content')
<div class="container mt-4">
    @if($attendance && $attendance->clock_in)
    <div class="card card-custom shadow-sm">
        <div class="dot dot-start"></div>
        <div class="text-left card-indent">Start time 08.00</div>
        <div class="line"></div>
    </div>
    <div class="status card-indent mt-3">
        <div class="alert alert-secondary shadow" role="alert">
            Clocked in at: {{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}
        </div>
    </div>
    @else
    <div class="card card-custom shadow-sm">
        <div class="dot dot-start"></div>
        <div class="text-left card-indent">Start time 08.00</div>
        <div class="line2"></div>
    </div>
    <div class="card card-custom card-indent shadow-sm mt-3">
        <div class="clock">
            <div class="time" id="clock">--:--</div>
            <form id="clockInForm" action="{{ route('attendance.clockin') }}" method="POST">
                @csrf
                <button type="submit" id="clockInBtn" class="btn btn-primary mx-2 @if($attendance && $attendance->clock_in) d-none @endif">
                    Clock In
                    <span id="clockInLoading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>
        </div>
    </div>
    @endif
    <div class="card card-custom shadow-sm mt-3">
        <div class="dot dot-end"></div>
        <div class="text-left card-indent">End time 16.30</div>
    </div>
    @if($attendance && $attendance->clock_out)
    <div class="status card-indent mt-3">
        <div class="alert alert-secondary shadow" role="alert">
            Clocked out at: {{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}
        </div>
    </div>
    @else
    <div class="card card-custom card-indent shadow-sm mt-3">
        <div class="clock">
            <div class="time" id="clock">--:--</div>
            <form id="clockOutForm" action="{{ route('attendance.clockout') }}" method="POST">
                @csrf
                <button type="submit" id="clockOutBtn" class="btn btn-secondary mx-2 @if(!$attendance || !$attendance->clock_in || $attendance->clock_out) d-none @endif">
                    Clock Out
                    <span id="clockOutLoading" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>
        </div>
    </div>
    @endif
</div>

<!-- Clock In Modal -->
<div class="modal fade" id="clockInModal" tabindex="-1" aria-labelledby="clockInModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clockInModalLabel">Clock In Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('/img/success.png') }}" alt="Clocked In" class="img-fluid mb-3" style="max-width: 150px;">
                <h5>Clocked In at <span id="modalClockTime">--:--</span></h5>
                <p class="mb-0">Another day, another opportunity to shine!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Clock Out Modal -->
<div class="modal fade" id="clockOutModal" tabindex="-1" aria-labelledby="clockOutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="clockOutModalLabel">Clock Out Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('/img/success.png') }}" alt="Clocked Out" class="img-fluid mb-3" style="max-width: 150px;">
                <h5>Clocked Out at <span id="modalClockOutTime">--:--</span></h5>
                <p class="mb-0">Great job today! Time to rest and recharge.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
    @include('attendance.footer')
@stop

@section('js')
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    function updateClock() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const currentTime = `${hours}:${minutes}`;
        document.getElementById('clock').textContent = currentTime;
        document.getElementById('modalClockTime').textContent = currentTime;
        document.getElementById('modalClockOutTime').textContent = currentTime;
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

    function getRandomClockInMessage() {
        const messages = [
            "Welcome back! Let's make today amazing!",
            "You’re going to achieve great things today!",
            "Let's start the day with positivity and productivity!",
            "Your hard work makes a difference. Let’s do this!",
            "Another day, another opportunity to shine!",
            "Ready to make today count? Let’s go!",
            "You’ve got this! Let’s have a productive day!",
            "Rise and shine! Time to make an impact!",
            "Good morning! Let's tackle today’s challenges together!",
            "Your dedication is inspiring. Let's make today great!",
            "Let's start the day strong and finish even stronger!",
            "Every day is a new chance to excel. Let’s make it happen!",
            "Excited to see what you’ll achieve today! Let’s get started!"
        ];
        return messages[Math.floor(Math.random() * messages.length)];
    }

    function getRandomClockOutMessage() {
        const messages = [
            "Great job today! Time to rest and recharge.",
            "Well done! You've earned your relaxation time.",
            "Another day, another milestone. Enjoy your evening!",
            "Your hard work paid off. Have a great rest of your day!",
            "Clocked out and feeling accomplished. Well done!",
            "Take a well-deserved break. See you tomorrow!",
            "You made it through another productive day. Time to unwind!",
            "Great work today! Now, go relax and rejuvenate.",
            "Day’s end brings new beginnings. Enjoy your downtime!",
            "Fantastic effort today! Time to relax and recharge.",
            "Well done! Another successful day in the books.",
            "Time to clock out and celebrate today’s achievements.",
            "You gave it your all today. Enjoy your evening!",
            "Finished strong! Have a great rest of your day.",
            "Excellent work! Now it’s time to relax and enjoy."
        ];
        return messages[Math.floor(Math.random() * messages.length)];
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
                if (distance <= 2000) {
                    document.getElementById("clockInBtn").disabled = false;
                }
            }, (error) => {
                console.error("Error getting location: ", error);
            });
        } else {
            console.error("Geolocation is not supported by this browser.");
        }

        // Handle clock in form submission
        $('#clockInForm').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission
            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();

            $('#clockInBtn').prop('disabled', true);
            $('#clockInLoading').removeClass('d-none');

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function(response) {
                    $('#clockInLoading').addClass('d-none');
                    $('#modalClockTime').text(response.clock_in_time);
                    $('#clockInModal .modal-body p.mb-0').text(getRandomClockInMessage());
                    $('#clockInModal').modal('show');
                    $('#clockInModal').on('hidden.bs.modal', function () {
                        location.reload();
                    });
                },
                error: function(response) {
                    // Handle the error
                    console.error('Clock-in failed:', response);
                    $('#clockInLoading').addClass('d-none');
                    $('#clockInBtn').prop('disabled', false);
                }
            });
        });

        // Handle clock out form submission
        $('#clockOutForm').submit(function(event) {
            event.preventDefault(); // Prevent the default form submission
            const form = $(this);
            const url = form.attr('action');
            const formData = form.serialize();

            $('#clockOutBtn').prop('disabled', true);
            $('#clockOutLoading').removeClass('d-none');

            $.ajax({
                type: 'POST',
                url: url,
                data: formData,
                success: function(response) {
                    $('#clockOutLoading').addClass('d-none');
                    $('#modalClockOutTime').text(response.clock_out_time);
                    $('#clockOutModal .modal-body p.mb-0').text(getRandomClockOutMessage());
                    $('#clockOutModal').modal('show');
                    $('#clockOutModal').on('hidden.bs.modal', function () {
                        location.reload();
                    });
                },
                error: function(response) {
                    // Handle the error
                    console.error('Clock-out failed:', response);
                    $('#clockOutLoading').addClass('d-none');
                    $('#clockOutBtn').prop('disabled', false);
                }
            });
        });
    });

</script>
@stop
