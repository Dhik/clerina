@extends('adminlte::page')

@section('title', trans('labels.performances'))

@section('content_header')
    <h1>{{ trans('labels.performances') }}</h1>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-info p-2">
                            <h5 class="text-center">Average Work Hours</h5>
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="inner">
                                        <h4 id="averageWorkHours">0 hours</h4>
                                        <p>Compare to Target (40)</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-success p-2">
                            <h5 class="text-center">Most Work Hours</h5>
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="inner">
                                        <h4 id="mostWorkHours">0</h4>
                                        <p>Employee</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-6">
                        <div class="small-box bg-warning p-2">
                            <h5 class="text-center">Most Diligent Part</h5>
                            <div class="row text-center">
                                <div class="col-12">
                                    <div class="inner">
                                        <h4 id="mostDiligentPart">0</h4>
                                        <p>Department</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <input type="text" id="nameFilter" class="form-control" placeholder="Search by name...">
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="mostWorkHoursChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <canvas id="mostDiligentPartChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <table id="workHoursTable" class="table table-bordered table-striped mt-4">
                    <thead>
                        <tr>
                            <th>{{ trans('labels.employee_id') }}</th>
                            <th>{{ trans('labels.name') }}</th>
                            <th>{{ trans('labels.total_work_hours') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be inserted here via JavaScript -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    let rawData = [];

    function renderChartsAndTable(filteredData) {
        let tableBody = $('#workHoursTable tbody');
        tableBody.empty();

        let totalWorkHours = 0;
        let maxWorkHours = 0;
        let mostDiligentPart = {};
        let employeeNames = [];
        let employeeWorkHours = [];

        filteredData.forEach(function(employee) {
            tableBody.append(`
                <tr>
                    <td>${employee.employee_id}</td>
                    <td>${employee.full_name}</td>
                    <td>${employee.totalWorkHours.toFixed(2)} hours</td>
                </tr>
            `);

            totalWorkHours += employee.totalWorkHours;
            if (employee.totalWorkHours > maxWorkHours) {
                maxWorkHours = employee.totalWorkHours;
                $('#mostWorkHours').text(`${employee.full_name} (${employee.totalWorkHours.toFixed(2)} hours)`);
            }

            if (!mostDiligentPart[employee.organization]) {
                mostDiligentPart[employee.organization] = 0;
            }
            mostDiligentPart[employee.organization] += employee.totalWorkHours;

            employeeNames.push(employee.full_name);
            employeeWorkHours.push(employee.totalWorkHours);
        });

        let averageWorkHours = totalWorkHours / filteredData.length;
        $('#averageWorkHours').text(`${averageWorkHours.toFixed(2)} hours`);

        let percentageChange = (averageWorkHours / 40) * 100;
        if (percentageChange >= 100) {
            $('#averageWorkHours').append(` <i class="fas fa-arrow-up" style="color: green;"></i>`);
        } else {
            $('#averageWorkHours').append(` <i class="fas fa-arrow-down" style="color: red;"></i>`);
        }

        let mostDiligentPartName = Object.keys(mostDiligentPart).reduce((a, b) => mostDiligentPart[a] > mostDiligentPart[b] ? a : b);
        $('#mostDiligentPart').text(`${mostDiligentPartName} (${mostDiligentPart[mostDiligentPartName].toFixed(2)} hours)`);

        let mostWorkHoursChart = new Chart(document.getElementById('mostWorkHoursChart'), {
            type: 'bar',
            data: {
                labels: employeeNames,
                datasets: [{
                    label: 'Work Hours',
                    data: employeeWorkHours,
                    backgroundColor: 'rgba(255, 159, 64, 0.2)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        let mostDiligentPartChart = new Chart(document.getElementById('mostDiligentPartChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(mostDiligentPart),
                datasets: [{
                    label: 'Work Hours',
                    data: Object.values(mostDiligentPart),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        return { mostWorkHoursChart, mostDiligentPartChart };
    }

    $.ajax({
        url: "{{ route('performances.weekly_work_hours') }}",
        method: 'GET',
        success: function(data) {
            rawData = data;
            let charts = renderChartsAndTable(rawData);

            $('#nameFilter').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                let filteredData = rawData.filter(function(employee) {
                    return employee.full_name.toLowerCase().includes(value);
                });

                // Destroy previous charts before rendering new ones
                charts.mostWorkHoursChart.destroy();
                charts.mostDiligentPartChart.destroy();
                
                charts = renderChartsAndTable(filteredData);
            });
        },
        error: function() {
            alert('Failed to fetch data.');
        }
    });
});
</script>
@stop
