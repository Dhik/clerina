<!-- resources/views/layouts/footer.blade.php -->
<div class="footer footer-menu">
    <a href="{{ route('attendance.absensi') }}">
        <i class="fas fa-clock"></i>
        <div>Absensi</div>
    </a>
    <a href="{{ route('attendance.log') }}">
        <i class="fas fa-calendar-check"></i>
        <div>Log</div>
    </a>
    <a href="{{ route('overtimes.index') }}">
        <i class="fas fa-business-time"></i>
        <div>Overtime</div>
    </a>
    <a href="{{ route('timeoffs.index') }}">
        <i class="fas fa-calendar-alt"></i>
        <div>Time Off</div>
    </a>
    <a href="{{ route('requestChangeShifts.index') }}">
        <i class="fas fa-random"></i>
        <div>Shift</div>
    </a>
</div>

<style>
    .footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: #fff;
        padding: 10px;
        border-top: 1px solid #dee2e6;
        text-align: center;
        display: flex;
        justify-content: space-around;
    }
    
    .footer-menu a {
        text-decoration: none;
        color: #333;
        font-size: 1rem;
    }
    .footer i {
        font-size: 1.5rem;
        color: #333;
    }
</style>
