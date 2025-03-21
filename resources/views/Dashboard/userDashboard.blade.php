@extends('layouts.app3')
@section('content')
    <div class="card card-custom mb-5 custom-card" style="background-color: #D9D9D9" id="uDashboard">
        <div class="card-body" style="background-color: #D9D9D9;padding: 0.25rem !important">

            <div class="row">
                <div class="col-md-6 pr-0">
                    <div class="card" style="height:252px">
                        <div class="dash_filter mt-4 ml-4">
                            <p><b><span id="first_crd_header">Today</span> Claims</b></p>
                            <div>
                                {!! Form::select(
                                    'calendar_id',
                                    [
                                        // '0' => 'Today',
                                        // 'week' => 'Week',
                                        'month' => 'Month',
                                        'year' => 'Year'
                                    ],
                                    null,
                                    [
                                        'class' => 'form-control white-smoke kt_select2_project',
                                        'id' => 'calendar_id',
                                    ],
                                ) !!}
                            </div>
                        </div>

                        <div class="card-body" style="padding-top: 0.25rem">
                            <div class="row">
                                <div class="col-2 p-2">
                                    <div class="card bg_assign text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/assign_dash.svg') }}" class="dash_icon">
                                        <span id="total_assigned" class="dash_card_font">{{ $totalAssignedCount }}</span>
                                        Assigned
                                    </div>
                                </div>
                                <div class="col-2 p-2">
                                    <div class="card bg-comp text-black dash_card  mt-2">
                                        <img src="{{ asset('/assets/media/bg/complete_dash.svg') }}" class="dash_icon">
                                        <span id="total_complete" class="dash_card_font">{{ $totalCompleteCount }}</span>
                                        Completed
                                    </div>
                                </div>
                                <div class="col-2 p-2">
                                    <div class="card bg_pend text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/pending_dash.svg') }}" class="dash_icon">
                                        <span id="total_pending" class="dash_card_font">{{ $totalPendingCount }}</span>
                                        Pending
                                    </div>
                                </div>
                                <div class="col-2 p-2">
                                    <div class="card bg_hold text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/hold_dash.svg') }}" class="dash_icon">
                                        <span id="total_hold" class="dash_card_font">{{ $totalHoldCount }}</span>
                                        On hold
                                    </div>
                                </div>
                                <div class="col-2 p-2">
                                    <div class="card bg_rework text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/rework_dash.svg') }}" class="dash_icon">
                                        <span id="total_rework" class="dash_card_font">{{ $totalReworkCount }}</span>
                                        Rework
                                    </div>
                                </div>
                                <div class="col-2 p-2">
                                    <div class="card bg_total text-black dash_card mt-2">
                                        <img src="{{ asset('/assets/media/bg/totalC_dash.svg') }}" class="dash_icon">
                                        <span id="total_count" class="dash_card_font">{{ $totalCount }}</span>
                                        Total
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 pl-2">
                    <div class="card" style="height:352px">
                        <span class="mt-4 ml-4"><b>Aging</b></span>
                        <div class="card-body scrollable">
                            {{-- <table class="table table-separate table-head-custom no-footer" id="agingList">
                                <thead>
                                    <tr>
                                        <th>Aging</th>
                                        @foreach ($agingHeader as $data)
                                            <th>{{ $data['days'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($agingCount) && count($agingCount) > 0)
                                        @foreach ($agingCount as $key => $data)
                                            <tr>
                                                <td>{{ $key }}</td>
                                                @foreach ($data as $countData)
                                                    <th>{{ $countData }}</th>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table> --}}
                            <div class="chart-container">
                                <canvas id="agingChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-6 pr-0" style="margin-top:-8rem">
                    <div class="card" style="height:356px">
                        <div class="dash_card3_filter mt-4 ml-4">
                            <span><b>Projects</b></span>
                            <div>
                                {!! Form::select('prj_calendar_id', [ 
                                    //  '0' => 'Today',
                                     'month' => 'Month', 'year' => 'Year'], null, [
                                    'class' => 'form-control white-smoke kt_select2_project',
                                    'id' => 'prj_calendar_id',
                                ]) !!}
                            </div>
                        </div>

                        {{-- <div class="card-body" data-scroll="true" data-height="300"> --}}
                        <div class="card-body" style="padding-top: 0.25rem;">
                            <div class="table-responsive" id="reportTable">
                                {{-- <table class="table table-separate table-head-custom no-footer"
                                    id="uDashboard_clients_list">
                                    <thead>
                                        <tr>
                                            <th width="15px"></th>
                                            <th>Project</th>
                                            <th>Assigned</th>
                                            <th>Completed</th>
                                            <th>Pending</th>
                                            <th>On Hold</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($projects) && count($projects) > 0)
                                            @foreach ($projects as $data)
                                                @php
                                                    $loginEmpId =
                                                        Session::get('loginDetails') &&
                                                        Session::get('loginDetails')['userDetail'] &&
                                                        Session::get('loginDetails')['userDetail']['emp_id'] != null
                                                            ? Session::get('loginDetails')['userDetail']['emp_id']
                                                            : '';
                                                    $empDesignation =
                                                        Session::get('loginDetails') &&
                                                        Session::get('loginDetails')['userDetail']['user_hrdetails'] &&
                                                        Session::get('loginDetails')['userDetail']['user_hrdetails'][
                                                            'current_designation'
                                                        ] != null
                                                            ? Session::get('loginDetails')['userDetail'][
                                                                'user_hrdetails'
                                                            ]['current_designation']
                                                            : '';
                                                    $projectName = App\Http\Helper\Admin\Helpers::projectName($data["id"])->project_name;//$data['client_name'];
                                                    if (
                                                        isset($data['subprject_name']) &&
                                                        !empty($data['subprject_name'])
                                                    ) {
                                                        $subproject_name = $data['subprject_name'];
                                                        $model_name = collect($subproject_name)
                                                            ->map(function ($item) use ($projectName) {
                                                                return Str::studly(
                                                                    Str::slug(
                                                                        Str::lower($projectName) .
                                                                            '_' .
                                                                            Str::lower($item),
                                                                        '_',
                                                                    ),
                                                                );
                                                            })
                                                            ->all();
                                                    } else {
                                                        $model_name = collect(
                                                            Str::studly(
                                                                Str::slug(Str::lower($projectName) . '_project', '_'),
                                                            ),
                                                        );
                                                    }

                                                    $assignedTotalCount = 0;
                                                    $completedTotalCount = 0;
                                                    $pendingTotalCount = 0;
                                                    $holdTotalCount = 0;
                                                    $modelTFlag = 0;
                                                    foreach ($model_name as $model) {
                                                        $modelClass = 'App\\Models\\' . $model;
                                                        // $days = Carbon\Carbon::now()->daysInMonth;
                                                        // $startDate = Carbon\Carbon::now()
                                                        //     ->subDays($days)
                                                        //     ->startOfDay()
                                                        //     ->toDateString();
                                                        // $endDate = Carbon\Carbon::now()->endOfDay()->toDateString();
                                                        // $startDate = Carbon\Carbon::now()
                                                        //     ->startOfDay()
                                                        //     ->toDateString();
                                                        // $endDate = Carbon\Carbon::now()
                                                        //     ->endOfDay()
                                                        //     ->toDateString();
                                                        $startDate =  Carbon\Carbon::now()->startOfMonth()->startOfDay()->toDateString();
                                                        $endDate =  Carbon\Carbon::now()->endOfMonth()->endOfDay()->toDateString();
                                                        $assignedCount = 0;
                                                        $completedCount = 0;
                                                        $pendingCount = 0;
                                                        $holdCount = 0;
                                                        $modelFlag = 0;
                                                        if (class_exists($modelClass) == true) {
                                                            $assignedCount = $modelClass
                                                                ::where('chart_status', 'CE_Assigned')
                                                                ->whereBetween('invoke_date', [$startDate, $endDate])
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                ->count();
                                                            $completedCount = $modelClass
                                                                ::where('chart_status', 'CE_Completed')
                                                                
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                ->whereBetween('invoke_date', [$startDate, $endDate])
                                                                ->count();
                                                            $pendingCount = $modelClass
                                                                ::where('chart_status', 'CE_Pending')
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                ->whereBetween('invoke_date', [$startDate, $endDate])
                                                                ->count();
                                                            $holdCount = $modelClass
                                                                ::where('chart_status', 'CE_Hold')
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                ->whereBetween('invoke_date', [$startDate, $endDate])
                                                                ->count();
                                                            $modelFlag = 1;
                                                        } else {
                                                            $assignedCount = 0;
                                                            $completedCount = 0;
                                                            $pendingCount = 0;
                                                            $holdCount = 0;
                                                            $modelFlag = 0;
                                                        }
                                                        $assignedTotalCount += $assignedCount;
                                                        $completedTotalCount += $completedCount;
                                                        $pendingTotalCount += $pendingCount;
                                                        $holdTotalCount += $holdCount;
                                                        $modelTFlag += $modelFlag;
                                                    }
                                                @endphp
                                                @if ($modelTFlag > 0)
                                                    <tr class="clickable-client cursor_hand">
                                                        <td class="details-control"></td>
                                                        <td>{{ $data['client_name'] }} <input type="hidden"
                                                                value={{ $data['id'] }}></td>
                                                        <td>{{ $assignedTotalCount }}</td>
                                                        <td>{{ $completedTotalCount }}</td>
                                                        <td>{{ $pendingTotalCount }}</td>
                                                        <td>{{ $holdTotalCount }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table> --}}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 pl-2">
                    <div class="card" style="height:252px">
                        <span class="mt-4 ml-4"><b>On hold</b></span>
                        {{-- <div class="card-body" data-scroll="true" data-height="300"> --}}
                        <div class="card-body">
                            <div class="table-responsive" id="reportHoldTable">
                                <table class="table table-separate table-head-custom no-footer"
                                    id="uDashboard_on_hold_clients_list">
                                    <thead>
                                        <tr>
                                            <th width="15px"></th>
                                            <th>Project</th>
                                            <th>On Hold</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (isset($projects) && count($projects) > 0)
                                            @foreach ($projects as $data)
                                                @php
                                                    $loginEmpId =
                                                        Session::get('loginDetails') &&
                                                        Session::get('loginDetails')['userDetail'] &&
                                                        Session::get('loginDetails')['userDetail']['emp_id'] != null
                                                            ? Session::get('loginDetails')['userDetail']['emp_id']
                                                            : '';
                                                    $empDesignation =
                                                        Session::get('loginDetails') &&
                                                        Session::get('loginDetails')['userDetail']['user_hrdetails'] &&
                                                        Session::get('loginDetails')['userDetail']['user_hrdetails'][
                                                            'current_designation'
                                                        ] != null
                                                            ? Session::get('loginDetails')['userDetail'][
                                                                'user_hrdetails'
                                                            ]['current_designation']
                                                            : '';
                                                            $projectName = App\Http\Helper\Admin\Helpers::projectName($data["id"])->project_name;// $data['client_name'];
                                                    if (
                                                        isset($data['subprject_name']) &&
                                                        !empty($data['subprject_name'])
                                                    ) {
                                                        $subproject_name = $data['subprject_name'];
                                                        $model_name = collect($subproject_name)
                                                            ->map(function ($item) use ($projectName) {
                                                                return Str::studly(
                                                                    Str::slug(
                                                                        Str::lower($projectName) .
                                                                            '_' .
                                                                            Str::lower($item),
                                                                        '_',
                                                                    ),
                                                                );
                                                            })
                                                            ->all();
                                                    } else {
                                                        $model_name = collect(
                                                            Str::studly(
                                                                Str::slug(Str::lower($projectName) . '_project', '_'),
                                                            ),
                                                        );
                                                    }

                                                    $assignedTotalCount = 0;
                                                    $completedTotalCount = 0;
                                                    $pendingTotalCount = 0;
                                                    $holdTotalCount = 0;
                                                    foreach ($model_name as $model) {
                                                        $modelClass = 'App\\Models\\' . $model;
                                                        $days = Carbon\Carbon::now()->daysInMonth;
                                                        $startDate = Carbon\Carbon::now()
                                                            ->subDays($days)
                                                            ->startOfDay()
                                                            ->toDateString();
                                                        $endDate = Carbon\Carbon::now()->endOfDay()->toDateString();
                                                        $assignedCount = 0;
                                                        $completedCount = 0;
                                                        $pendingCount = 0;
                                                        $holdCount = 0;
                                                        if (class_exists($modelClass)) {
                                                            $assignedCount = $modelClass
                                                                ::where('chart_status', 'CE_Assigned')
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                ->count();
                                                            $completedCount = $modelClass
                                                                ::where('chart_status', 'CE_Completed')
                                                               
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                // ->whereBetween('invoke_date', [$startDate, $endDate])
                                                                ->count();
                                                            $pendingCount = $modelClass
                                                                ::where('chart_status', 'CE_Pending')
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                // ->whereBetween('invoke_date', [$startDate, $endDate])
                                                                ->count();
                                                            $holdCount = $modelClass
                                                                ::where('chart_status', 'CE_Hold')
                                                                ->where('CE_emp_id', $loginEmpId)
                                                                // ->whereBetween('invoke_date', [$startDate, $endDate])
                                                                ->count();
                                                        } else {
                                                            $assignedCount = 0;
                                                            $completedCount = 0;
                                                            $pendingCount = 0;
                                                            $holdCount = 0;
                                                        }
                                                        $assignedTotalCount += $assignedCount;
                                                        $completedTotalCount += $completedCount;
                                                        $pendingTotalCount += $pendingCount;
                                                        $holdTotalCount += $holdCount;
                                                    }
                                                @endphp
                                                @if ($holdTotalCount > 0)
                                                    <tr class="clickable-client cursor_hand">
                                                        <td class="details-control"></td>
                                                        <td>{{ $data['client_name'] }} <input type="hidden"
                                                                value={{ $data['id'] }}></td>
                                                        <td>{{ $holdTotalCount }}</td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .scrollable {
        overflow: auto;
        scrollbar-color: rgba(0, 0, 0, 0.5) transparent;
        scrollbar-width: thin;
    }

    .chart-container {
        position: relative;
        margin: auto;
        height: 300;
        width: auto;
    }

    .scrollable::-webkit-scrollbar {
        width: 2px;
        height: 2px;
    }

    .scrollable::-webkit-scrollbar-thumb {
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 20px;
    }

    .scrollable::-webkit-scrollbar-track {
        background: transparent;
    }
</style>
@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('agingChart').getContext('2d');
            const agingData = @json($agingCount);
            // const labels = ['5', '10', '15', '20', '25', '30', '35', '40', '45'];
            const labels = [];
            $.each(@json($agingHeader), function(key, val) {
                labels.push(val.days_range);
            });
            const datasets = [];
            Object.keys(agingData).forEach((key) => {
                datasets.push({
                    label: key,
                    data: agingData[key],
                    backgroundColor: getRandomColor(),
                    borderColor: getRandomColor(),
                    borderWidth: 1,
                    fontSize: 1,
                    barThickness: 40
                });
            });
            console.log(datasets, 'datasets');
            const agingChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Aging - Analysis',
                            padding: {
                                top: 0,
                                bottom: 10,
                            }
                        },
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 8
                                },
                                boxWidth: 10,
                                boxHeight: 5
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMin: 0,
                            ticks: {
                                stepSize: 10,
                                font: {
                                    size: 10
                                },
                            },
                            title: {
                                display: true,
                                text: 'Count'
                            },
                            grid: {
                                display: true // Keep vertical grid lines for y-axis
                            }
                        },
                        x: {
                            ticks: {
                                autoSkip: false,
                                font: {
                                    size: 10
                                }
                            },
                            title: {
                                display: true,
                                text: 'Days Range'
                            },
                            grid: {
                                display: false 
                            }
                        }
                    }
                }
            });

            function getRandomColor() {
                const letters = '0123456789ABCDEF';
                let color = '';
                for (let i = 0; i < agingData.length; i++) {
                    color = labelColors[i];
                }
                return color;
            }
        });
        $(document).ready(function() {
            var subprojectCountData;
            KTApp.block('#uDashboard', {
                overlayColor: '#000000',
                state: 'danger',
                opacity: 0.1,
                message: 'Fetching...',
            });
            clientList1();

            function clientList() {
                var subProjects;
                var table = $("#uDashboard_clients_list").DataTable({
                    processing: true,
                    lengthChange: false,
                    searching: false,
                    pageLength: 5,
                    "info": false,
                    paging: false,
                    scrollCollapse: true,
                    scrollX: true,
                    scrollY: 200,
                    "initComplete": function(settings, json) {
                        $('body').find('.dataTables_scrollBody').addClass("scrollbar");
                        $('body').find('.dataTables_scrollBody').css("width",'98%','important');
                    },
                    // columnDefs: [{
                    //     className: 'details-control',
                    //     targets: [0],
                    //     orderable: false,
                    // }, ],
                    // responsive: true
                })
                table.buttons().container()
                    .appendTo('.outside');

                $('#uDashboard_clients_list tbody').on('click', 'td.details-control', function() {
                    var client_id = $(this).closest('tr').find('td:eq(1) input').val();
                    var tr = $(this).closest('tr');
                    var row = table.row(tr);
                    var subProjectName = '--';
                    var CalendarId = $('#prj_calendar_id').val();
                    console.log(CalendarId, 'sub');
                    if (row.child.isShown()) {
                        row.child.hide();
                        tr.removeClass('shown');
                    } else {
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            type: "GET",
                            url: "{{ url('dashboard/sub_projects') }}",
                            data: {
                                project_id: client_id,
                                CalendarId: CalendarId,
                            },
                            success: function(res) {
                                subProjects = res.subprojects;
                                subprojectCountData = Object.keys(subProjects).length;
                                if (typeof subprojectCountData !== 'undefined' &&
                                    subprojectCountData > 0) {
                                    row.child(format(row.data(), subProjects)).show();
                                } else {
                                    if (typeof subprojectCountData !== 'undefined') {
                                        window.location.href = baseUrl + 'projects_assigned/' +
                                            btoa(client_id) + '/' +
                                            subProjectName + "?parent=" +
                                            getUrlVars()["parent"] + "&child=" + getUrlVars()[
                                                "child"];
                                    }
                                }
                                tr.addClass('shown');
                            },
                            error: function(jqXHR, exception) {}
                        });
                    }
                });
            }

            function format(data, subProjects) {
                if (subprojectCountData > 0) {
                    var html =
                        '<table id="practice_list" class="inv_head" cellpadding="5" cellspacing="0" border="0" style="width:97%;border-radius: 10px !important;overflow: hidden;margin-left: 1.5rem;">' +
                        '<tr><th></th><th>Sub Project</th><th>Assigned</th> <th>Completed</th> <th>Pending</th><th>On Hold</th> </tr>';
                    $.each(subProjects, function(index, val) {
                        // if(val.assignedCount > 0 || val.CompletedCount > 0 || val.PendingCount > 0 || val.holdCount > 0) {
                            html +=
                                '<tbody><tr class="clickable-row cursor_hand">' +
                                '<td><input type="hidden" value=' + val.client_id + '></td>' +
                                '<td>' + val.sub_project_name + '<input type="hidden" value=' + val
                                .sub_project_id + '></td>' +
                                '<td>' + val.assignedCount + '</td>' +
                                '<td>' + val.CompletedCount + '</td>' +
                                '<td>' + val.PendingCount + '</td>' +
                                '<td>' + val.holdCount + '</td>' +
                                '</tr></tbody>';
                        // }
                    });
                    html += '</table>';
                    return html;
                }
            }

            $(document).on('click', '.clickable-row', function(e) {
                var clientName = $(this).closest('tr').find('td:eq(0) input').val();
                var subProjectName = $(this).closest('tr').find('td:eq(1) input').val();

                if (!clientName) {
                    console.error('encodedclientname is undefined or empty');
                    return;
                }
                if(subProjectName == '--') {
                    decodeSubProjectName = subProjectName;
                } else {
                    decodeSubProjectName = btoa(subProjectName);
                }
                window.location.href = baseUrl + 'projects_assigned/' + btoa(clientName) + '/' + decodeSubProjectName + "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];
            })

            var holdTable = $("#uDashboard_on_hold_clients_list").DataTable({
                processing: true,
                lengthChange: false,
                searching: false,
                pageLength: 5,
                "info": false,
                paging: false,
                scrollCollapse: true,
                scrollY: 100,
                columnDefs: [{
                    className: 'details-control',
                    targets: [0],
                    orderable: false,
                }, ],
                responsive: true
            })
            holdTable.buttons().container()
                .appendTo('.outside');

            $('#uDashboard_on_hold_clients_list tbody').on('click', 'td.details-control', function() {
                var client_id = $(this).closest('tr').find('td:eq(1) input').val();
                var tr = $(this).closest('tr');
                var row = holdTable.row(tr);
                var subProjectName = '--';
                var CalendarId = 'hold';
                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "GET",
                        url: "{{ url('dashboard/sub_projects') }}",
                        data: {
                            project_id: client_id,
                            CalendarId: CalendarId,
                        },
                        success: function(res) {
                            subProjects = res.subprojects;
                            subprojectCountData = Object.keys(subProjects).length;
                            if (typeof subprojectCountData !== 'undefined' &&
                                subprojectCountData > 0) {
                                row.child(holdFormat(row.data(), subProjects)).show();
                            } else {
                                if (typeof subprojectCountData !== 'undefined') {
                                    window.location.href = baseUrl + 'projects_hold/' +
                                        btoa(client_id) + '/' +
                                        subProjectName + "?parent=" +
                                        getUrlVars()["parent"] + "&child=" + getUrlVars()[
                                            "child"];
                                }
                            }
                            tr.addClass('shown');
                        },
                        error: function(jqXHR, exception) {}
                    });
                }
            });

            function holdFormat(data, subProjects) {
                if (subprojectCountData > 0) {
                    var html =
                        '<table id="practice_list" class="inv_head" cellpadding="5" cellspacing="0" border="0" style="width:97%;border-radius: 10px !important;overflow: hidden;margin-left: 1.5rem;">' +
                        '<tr><th></th><th>Sub Project</th><th>On Hold</th> </tr>';
                    $.each(subProjects, function(index, val) {
                        html +=
                            '<tbody><tr class="hold-clickable-row cursor_hand">' +
                            '<td><input type="hidden" value=' + val.client_id + '></td>' +
                            '<td>' + val.sub_project_name + '<input type="hidden" value=' + val
                            .sub_project_id + '></td>' +
                            '<td>' + val.holdCount + '</td>' +
                            '</tr></tbody>';
                    });
                    html += '</table>';
                    return html;
                }
            }

            $(document).on('click', '.hold-clickable-row', function(e) {
                var clientName = $(this).closest('tr').find('td:eq(0) input').val();
                var subProjectName = $(this).closest('tr').find('td:eq(1) input').val();

                if (!clientName) {
                    console.error('encodedclientname is undefined or empty');
                    return;
                }
                if(subProjectName == '--') {
                    decodeSubProjectName = subProjectName;
                } else {
                    decodeSubProjectName = btoa(subProjectName);
                }
                window.location.href = baseUrl + 'projects_hold/' + btoa(clientName) + '/' + 
                        decodeSubProjectName + "?parent=" +
                    getUrlVars()["parent"] + "&child=" + getUrlVars()["child"];

            });

            var agingTable = $("#agingList").DataTable({
                processing: true,
                lengthChange: false,
                searching: false,
                pageLength: 5,
                "info": false,
                paging: false,
                scrollCollapse: true,
                scrollX: true,
                scrollY: 100,
                "initComplete": function(settings, json) {
                    $('body').find('.dataTables_scrollBody').addClass("scrollbar");
                },
            });
            $(document).on('change', '#calendar_id', function(e) {
                var CalendarId = $('#calendar_id').val();
                var CalendarText = $('#calendar_id option:selected').text();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "GET",
                    url: "{{ url('dashboard/calendar_filter') }}",
                    data: {
                        CalendarId: CalendarId,
                        type: "user",
                    },
                    success: function(res) {
                        $('#first_crd_header').text(CalendarText);
                        $('#total_assigned').text(res.totalAssignedCount);
                        $('#total_complete').text(res.totalCompleteCount);
                        $('#total_pending').text(res.totalPendingCount);
                        $('#total_hold').text(res.totalHoldCount);
                        $('#total_rework').text(res.totalReworkCount);
                        $('#total_count').text(res.totalCount);
                    }
                });
            });

            $(document).on('change', '#prj_calendar_id', function(e) {
                clientList1();
            });
            function clientList1(){
                var CalendarId = $('#prj_calendar_id').val();
                var CalendarText = $('#prj_calendar_id option:selected').text();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "GET",
                    url: "{{ url('dashboard/projects_calendar_filter') }}",
                    data: {
                        CalendarId: CalendarId,
                        // type: "user",
                    },
                    success: function(res) {
                        if (res.body_info) {
                            console.log(res, 'res');
                            $('#reportTable').html('');
                            $('#reportTable').html(res.body_info);
                            clientList();
                            KTApp.unblock('#uDashboard');
                        }
                    }
                });
            }
        })
    </script>
@endpush
