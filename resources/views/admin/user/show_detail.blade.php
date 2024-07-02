@extends('adminlte::page')

@section('title', trans('labels.user'))

@section('content_header')
    <h1>{{ $user->name }}</h1>
@stop

@section('content')
    <div class="container">
        <h2>Add New Employee</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Point Circle Steps -->
        <div class="steps">
            <div class="step step-active" data-step="0">1</div>
            <div class="step" data-step="1">2</div>
            <div class="step" data-step="2">3</div>
            <div class="step" data-step="3">4</div>
        </div>
        <br>

        <form id="multiStepForm" action="" method="POST">
            @csrf

            <!-- Step 1: Personal Data -->
            <div class="form-step form-step-active">
                <h3>Personal Data</h3>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="birth_date">Birth Date</label>
                    <input type="date" name="birth_date" class="form-control" required>
                </div>
                <!-- Add other fields similar to above -->
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
            </div>

            <!-- Step 2: Employment Data -->
            <div class="form-step">
                <h3>Employment Data</h3>
                <div class="form-group">
                    <label for="organization">Organization</label>
                    <input type="text" name="organization" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="job_position">Job Position</label>
                    <input type="text" name="job_position" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="join_date">Join Date</label>
                    <input type="date" name="join_date" class="form-control" required>
                </div>
                <!-- Add other fields similar to above -->
                <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
            </div>

            <!-- Step 3: Payroll -->
            <div class="form-step">
                <h3>Payroll</h3>
                <div class="form-group">
                    <label for="bank_name">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="bank_account">Bank Account</label>
                    <input type="text" name="bank_account" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="bank_account_holder">Bank Account Holder</label>
                    <input type="text" name="bank_account_holder" class="form-control" required>
                </div>
                <!-- Add other fields similar to above -->
                <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                <button type="button" class="btn btn-primary" onclick="nextStep()">Next</button>
            </div>

            <!-- Step 4: Invite Employee -->
            <div class="form-step">
                <h3>Invite Employee</h3>
                <div class="form-group">
                    <label for="user_id">User ID</label>
                    <input type="number" name="user_id" class="form-control" required>
                </div>
                <!-- Add other fields similar to above -->
                <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                <button type="submit" class="btn btn-success">Submit</button>
            </div>
        </form>
    </div>

    <script>
        let currentStep = 0;
        const formSteps = document.querySelectorAll(".form-step");
        const steps = document.querySelectorAll(".step");

        function showStep(step) {
            formSteps.forEach((formStep, index) => {
                formStep.classList.toggle("form-step-active", index === step);
            });

            steps.forEach((stepElement, index) => {
                stepElement.classList.toggle("step-active", index === step);
            });
        }

        function nextStep() {
            if (currentStep < formSteps.length - 1) {
                currentStep++;
                showStep(currentStep);
            }
        }

        function prevStep() {
            if (currentStep > 0) {
                currentStep--;
                showStep(currentStep);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            showStep(currentStep);
        });
    </script>

    <style>
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .step {
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background-color: #ccc;
            text-align: center;
            color: white;
        }

        .step-active {
            background-color: #007bff;
        }

        .form-step {
            display: none;
        }

        .form-step-active {
            display: block;
        }
    </style>
@endsection

@section('js')
    <script>
        $(document).ready(function () {
            $('.delete-user').click(function (e) {
                e.preventDefault();

                Swal.fire({
                    title: '{{ trans('labels.are_you_sure') }}',
                    text: '{{ trans('labels.not_be_able_to_recover') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '{{ trans('buttons.confirm_swal') }}',
                    cancelButtonText: '{{ trans('buttons.cancel_swal') }}',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('users.destroy', $user->id) }}',
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                Swal.fire(
                                    '{{ trans('labels.success') }}',
                                    '{{ trans('messages.success_delete') }}',
                                    'success'
                                ).then(() => {
                                    window.location.href = "{{ route('users.index') }}";
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@stop
