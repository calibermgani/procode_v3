<?php

namespace App\Http\Controllers;

use App\Models\Aging;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Http\Helper\Admin\Helpers as Helpers;
use App\Models\InventoryExeFile;
use App\Models\ProjectReason;
use App\Jobs\GetProjJob;
use Illuminate\Support\Facades\Cache;
use App\Jobs\GetSubPrjJob;
class DashboardController extends Controller
{
    public function dashboard(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                // $client = new Client(['verify' => false]);
                // $response = $client->post('https://www.google.com/recaptcha/api/siteverify', [
                //     'form_params' => [
                //         'secret' => env('NOCAPTCHA_SECRET'),
                //         'response' => $request->input('g-recaptcha-response'),
                //     ],
                // ]);
                // $body = json_decode((string) $response->getBody());
               
                if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false)) {
                    return $this->procodeManagerDashboard();
                } else {
                  
                    return $this->procodeUserDashboard();
                }
                // return view('Dashboard/dashboard');
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function procodeTesting()
    {
        return view('Dashboard/procodeTesting');
    }
    public function procodeUserDashboard()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $agingHeader = Aging::select('days','days_range')->get()->toArray();
                 $projects = $this->getProjects();
                $startDate = Carbon::now()->startOfMonth()->toDateString();
                $endDate = Carbon::now()->endOfMonth()->toDateString();
                $models = [];
                $projectIds = [];
                foreach ($projects as $project) {
                    $project["client_name"] = Helpers::projectName($project["id"])->project_name;
                    if (count($project["subprject_name"]) > 0) {
                        foreach ($project["subprject_name"] as $key => $subProject) {
                            $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                            $projectIds[] = $project["client_name"];
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                        $projectIds[] = $project["client_name"];
                    }
                }
                $assignedCounts = $completeCounts = $pendingCounts = $holdCounts = $reworkCounts = $totalCounts = $agingArr1 = $agingArr2 = $agingCount = [];
                foreach ($models as $modelKey => $model) {
                    if (class_exists($model)) {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                        foreach ($agingHeader as $key => $data) {
                            // $startDay = $data["days"] - 1;
                            // $endDumDay = isset($agingHeader[$key - 1]) &&  isset($agingHeader[$key - 1]["days"]) ? $agingHeader[$key - 1]["days"]  : "0";
                            if(str_contains($data["days_range"],'-')) {
                                $splitRange = explode('-', $data["days_range"]);
                                $startDay = $splitRange[1]-1;
                                $endDumDay =  $splitRange[0]-1;
                                // $startDate = Carbon::now()->subDays($startDay)->startOfDay()->toDateString();
                                // $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateString();
                                $AgingStartDate = Carbon::now();
                                $AgingEndDate = Carbon::now();
                                for ($i = 0; $i < $startDay; $i++) {
                                    $AgingStartDate->subDay();
                                    while ($AgingStartDate->isWeekend()) {
                                        $AgingStartDate->subDay();
                                    }
                                }
                                $AgingStartDate = $AgingStartDate->startOfDay()->toDateString();
                                for ($i = 0; $i < $endDumDay; $i++) {
                                    $AgingEndDate->subDay();
                                    while ($AgingEndDate->isWeekend()) {
                                        $AgingEndDate->subDay();
                                    }
                                }
                                $AgingEndDate = $AgingEndDate->endOfDay()->toDateString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$AgingStartDate, $AgingEndDate])->count();
                            } else {
                                    $splitRange = explode('+', $data["days_range"]);
                                    $endDumDay =  $splitRange[0]-1;
                                    $startDay =  $splitRange[1] != "" ? $splitRange[1]-1 : $endDumDay +1;
                                    // $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateString();
                                    $AgingEndDate = Carbon::now();
                                    for ($i = 0; $i < $endDumDay; $i++) {
                                        $AgingEndDate->subDay();
                                        while ($AgingEndDate->isWeekend()) {
                                            $AgingEndDate->subDay();
                                        }
                                    }
                                    $AgingEndDate = $AgingEndDate->endOfDay()->toDateString();
                                    $dataCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->where('invoke_date', '<=', $AgingEndDate)->count();
                                }
                            $agingArr1[$modelKey][$data["days_range"]] = $dataCount;
                            $agingArr2[$modelKey] = $projectIds[$modelKey];
                        }
                    }
                } //dd( $startArray,$endArray,$startDArray,$endDArray);

                foreach ($agingArr2 as $key => $value) {
                    if (!isset($agingCount[$value])) {
                        $agingCount[$value] = [];
                    }
                    foreach ($agingArr1[$key] as $innerKey => $innerValue) {
                        if (!isset($agingCount[$value][$innerKey])) {
                            $agingCount[$value][$innerKey] = 0;
                        }
                        $agingCount[$value][$innerKey] += $innerValue;
                    }
                }
                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalCount = $totalAssignedCount + $totalCompleteCount + $totalPendingCount + $totalHoldCount + $totalReworkCount;
                function allValuesAreZero($array)
                {
                    foreach ($array as $value) {
                        if ($value !== 0) {
                            return false;
                        }
                    }
                    return true;
                }

                foreach ($agingCount as $key => $subArray) {
                    if (allValuesAreZero($subArray)) {
                        unset($agingCount[$key]);
                    }
                }
                return view('Dashboard/userDashboard', compact('projects', 'totalAssignedCount', 'totalCompleteCount', 'totalPendingCount', 'totalHoldCount', 'totalReworkCount', 'totalCount', 'agingHeader', 'agingCount'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function getSubProjects(Request $request)
    {
        try {
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $request->project_id,
            ];
            $client = new Client(['verify' => false]);
            $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_practice_on_client', [
                'json' => $payload,
            ]);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
            } else {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            $subprojects = $data['practiceList'];
            $clientDetails = $data['clientInfo'];           
            $subProjectsWithCount = [];
            $calendarId = $request->CalendarId;
            foreach ($subprojects as $key => $data) {
                $subProjectsWithCount[$key]['client_id'] = $clientDetails['id'];
                $subProjectsWithCount[$key]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                $subProjectsWithCount[$key]['sub_project_id'] = $data['id'];
                $subProjectsWithCount[$key]['sub_project_name'] = $data['name'];
                $projectName = $subProjectsWithCount[$key]['client_name'];
                $table_name = Str::slug((Str::lower($projectName) . '_' . Str::lower($subProjectsWithCount[$key]['sub_project_name'])), '_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                if ($calendarId == "year") {
                    $days = Carbon::now()->daysInYear;
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                } else if ($calendarId == "month") {
                    $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                } else {
                    $days = 0;
                    $startDate = Carbon::now()->startOfDay()->toDateString();
                    $endDate = Carbon::now()->endOfDay()->toDateString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateString();
                // $endDate = Carbon::now()->endOfDay()->toDateString();
                if (class_exists($modelClass)) {
                    $subProjectsWithCount[$key]['assignedCount'] = $modelClass::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                    $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status', 'CE_Completed')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                    $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status', 'CE_Pending')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                    $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status', 'CE_Hold')->where('CE_emp_id', $loginEmpId)
                        ->where(function ($query) use ($startDate, $endDate, $days) {
                            if ($days == 0) {
                                $query;
                            } else {
                                $query->whereBetween('invoke_date', [$startDate, $endDate]);
                            }
                        })->count();
                } else {
                    $subProjectsWithCount[$key]['assignedCount'] = '--';
                    $subProjectsWithCount[$key]['CompletedCount'] = '--';
                    $subProjectsWithCount[$key]['PendingCount'] = '--';
                    $subProjectsWithCount[$key]['holdCount'] = '--';
                }
            }

            return response()->json(['subprojects' => $subProjectsWithCount]);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function procodeManagerDashboard()
    {
        //Log::info('mgr dashboarc');
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
               
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $agingHeader = Aging::select('days', 'days_range')->get()->toArray();
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                 $projects = $this->getProjects();
                $startDate = Carbon::now()->startOfDay()->toDateString();
                $endDate = Carbon::now()->endOfDay()->toDateString();
                $startDate = Carbon::now()->startOfWeek()->startOfDay()->toDateString();
                $endDate = Carbon::now()->endOfWeek()->endOfDay()->toDateString();
                $models = $projectIds = [];
                foreach ($projects as $project) {
                    $project["client_name"] = Helpers::projectName($project["id"])->project_name;
                    if (count($project["subprject_name"]) > 0) {
                        foreach ($project["subprject_name"] as $key => $subProject) {
                            $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                            $projectIds[] = $project["client_name"];
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                        $projectIds[] = $project["client_name"];
                    }
                }
                $assignedCounts = $completeCounts = $pendingCounts = $holdCounts = $reworkCounts = $totalCounts = $agingArr1 = $agingArr2 = $agingCount = $unAssignedCounts = [];
                foreach ($models as $modelKey => $model) {
                    if (class_exists($model)) {
                        $aCount = $model::whereIn('chart_status', ['CE_Assigned','CE_Inprocess'])->whereNotNull('CE_emp_id')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $uACount = $model::where('chart_status', 'CE_Assigned')->whereNull('CE_emp_id')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                        $unAssignedCounts[] = $uACount;
                        foreach ($agingHeader as $key => $data) {
                            // $startDay = $data["days"] - 1;
                            // $endDumDay = isset($agingHeader[$key - 1]) &&  isset($agingHeader[$key - 1]["days"]) ? $agingHeader[$key - 1]["days"]  : "0";
                           if(str_contains($data["days_range"],'-')) {
                                $splitRange = explode('-', $data["days_range"]);
                                $startDay = $splitRange[1]-1;
                                $endDumDay =  $splitRange[0]-1;
                                // $startDate = Carbon::now()->subDays($startDay)->startOfDay()->toDateString();
                                // $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateString();
                                $AgingStartDate = Carbon::now();
                                $AgingEndDate = Carbon::now();
                                for ($i = 0; $i < $startDay; $i++) {
                                    $AgingStartDate->subDay();
                                    while ($AgingStartDate->isWeekend()) {
                                        $AgingStartDate->subDay();
                                    }
                                }
                                $AgingStartDate = $AgingStartDate->startOfDay()->toDateString();
                                for ($i = 0; $i < $endDumDay; $i++) {
                                    $AgingEndDate->subDay();
                                    while ($AgingEndDate->isWeekend()) {
                                        $AgingEndDate->subDay();
                                    }
                                }
                                $AgingEndDate = $AgingEndDate->endOfDay()->toDateString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->whereBetween('invoke_date', [$AgingStartDate, $AgingEndDate])->count();
                            } else {
                                $splitRange = explode('+', $data["days_range"]);
                                $endDumDay =  $splitRange[0]-1;
                                $startDay =  $splitRange[1] != "" ? $splitRange[1]-1 : $endDumDay +1;
                               // $endDate = Carbon::now()->subDays($endDumDay)->endOfDay()->toDateString();
                                $AgingEndDate = Carbon::now();
                                for ($i = 0; $i < $endDumDay; $i++) {
                                    $AgingEndDate->subDay();
                                    while ($AgingEndDate->isWeekend()) {
                                        $AgingEndDate->subDay();
                                    }
                                }
                                $AgingEndDate = $AgingEndDate->endOfDay()->toDateString();
                                $dataCount = $model::where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->where('invoke_date', '<=', $AgingEndDate)->count(); 
                            }
                            $agingArr1[$modelKey][$data["days_range"]] = $dataCount;
                            $agingArr2[$modelKey] = $projectIds[$modelKey];
                        }
                    }
                }

                foreach ($agingArr2 as $key => $value) {
                    if (!isset($agingCount[$value])) {
                        $agingCount[$value] = [];
                    }
                    foreach ($agingArr1[$key] as $innerKey => $innerValue) {
                        if (!isset($agingCount[$value][$innerKey])) {
                            $agingCount[$value][$innerKey] = 0;
                        }
                        $agingCount[$value][$innerKey] += $innerValue;
                    }
                }

                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalUnAssignedCounts = array_sum($unAssignedCounts);
                $totalCount = $totalAssignedCount + $totalCompleteCount + $totalPendingCount + $totalHoldCount + $totalReworkCount + $totalUnAssignedCounts;

                $agingData = [
                    'AMBC' => [50, 0, 0, 0, 0, 100, 0, 153, 0, 45, 45],
                    'Cancer Care Specialists' => [50, 0, 0, 0, 0, 0, 0, 11, 0, 45, 45],
                    "Saco River Medical Group" => [50, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "AIG" => [250, 0, 0, 0, 0, 70, 0, 12, 0, 45, 45],
                    // "Ash Meomorial Hospital" => [250, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "MDCSp" => [230, 0, 0, 0, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro" => [140, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro1" => [100, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro2" => [200, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro3" => [50, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro4" => [40, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro5" => [30, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro6" => [10, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro7" => [1, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro8" => [2, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro9" => [3, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro10" => [4, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro11" => [5, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                    // "Advanced Gastro12" => [6, 0, 0, 60, 0, 0, 0, 12, 0, 45, 45],
                ];
                function allValuesAreZero($array)
                {
                    foreach ($array as $value) {
                        if ($value !== 0) {
                            return false;
                        }
                    }
                    return true;
                }

                foreach ($agingCount as $key => $subArray) {
                    if (allValuesAreZero($subArray)) {
                        unset($agingCount[$key]);
                    }
                }
                // dd($agingCount);
                return view('Dashboard/managerDashboard', compact('projects', 'totalAssignedCount', 'totalCompleteCount', 'totalPendingCount', 'totalHoldCount', 'totalReworkCount', 'totalCount', 'agingHeader', 'agingCount', 'agingData'));
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function getUsersWithSubProjects(Request $request)
    {
        try {
            $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
            $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
            $payload = [
                'token' => '1a32e71a46317b9cc6feb7388238c95d',
                'client_id' => $request->project_id,
            ];
            $client = new Client(['verify' => false]);
            $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_practices_users_on_client', [
                'json' => $payload,
            ]);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
            } else {
                return response()->json(['error' => 'API request failed'], $response->getStatusCode());
            }
            $subprojects = $data['practiceList'];
            $resourceList = $data['resourceList'];
            $clientDetails = $data['clientInfo'];
            $subProjectsWithCount = [];
            if (count($subprojects) > 0) {
                foreach ($subprojects as $key => $data) {
                    $projectName = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                    $table_name = Str::slug((Str::lower($projectName) . '_' . Str::lower($data['name'])), '_');
                    $modelName = Str::studly($table_name);
                    $modelClass = "App\\Models\\" . $modelName;
                    $calendarId = $request->CalendarId;
                    if ($calendarId == "year") {
                        $startDate = Carbon::now()->startOfYear()->toDateString();
                        $endDate = Carbon::now()->endOfYear()->toDateString();
                        $days = Carbon::now()->daysInYear;
                    } else if ($calendarId == "month") {
                        $days =  Carbon::now()->daysInMonth;
                        $startDate = Carbon::now()->startOfMonth()->toDateString();
                        $endDate = Carbon::now()->endOfMonth()->toDateString();
                    } else {
                        $startDate = Carbon::now()->startOfDay()->toDateString();
                        $endDate = Carbon::now()->endOfDay()->toDateString();
                        $days = 0;
                    }
                    // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateString();
                    // $endDate = Carbon::now()->endOfDay()->toDateString();
                    if (class_exists($modelClass)) {
                        $resourceData = $modelClass::whereIn('CE_emp_id', $resourceList)->select('CE_emp_id')->groupBy('CE_emp_id')->get()->toArray();
                        foreach ($resourceData as $resourceKey => $resourceDataVal) {
                            $subProjectsWithCount[$key][$resourceKey]['client_id'] = $clientDetails['id'];
                            $subProjectsWithCount[$key][$resourceKey]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                            $subProjectsWithCount[$key][$resourceKey]['sub_project_id'] = $data['id'];
                            $subProjectsWithCount[$key][$resourceKey]['sub_project_name'] = $data['name'];
                            $subProjectsWithCount[$key][$resourceKey]['resource_emp_id'] = $resourceDataVal["CE_emp_id"];
                            $subProjectsWithCount[$key][$resourceKey]['assignedCount'] = $modelClass::where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('invoke_date', [$startDate, $endDate])->count();
                            $subProjectsWithCount[$key][$resourceKey]['CompletedCount'] = $modelClass::where('chart_status', 'CE_Completed')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('invoke_date', [$startDate, $endDate])->count();
                            $subProjectsWithCount[$key][$resourceKey]['PendingCount'] = $modelClass::where('chart_status', 'CE_Pending')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('invoke_date', [$startDate, $endDate])->count();
                            $subProjectsWithCount[$key][$resourceKey]['holdCount'] = $modelClass::where('chart_status', 'CE_Hold')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                                ->where(function ($query) use ($startDate, $endDate, $days) {
                                    if ($days == 0) {
                                        $query;
                                    } else {
                                        $query->whereBetween('invoke_date', [$startDate, $endDate]);
                                    }
                                })->count();
                        }
                    } else {
                        $subProjectsWithCount[$key][0]['client_id'] = $clientDetails['id'];
                        $subProjectsWithCount[$key][0]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                        $subProjectsWithCount[$key][0]['sub_project_id'] = $data['id'];
                        $subProjectsWithCount[$key][0]['sub_project_name'] = $data['name'];
                        $subProjectsWithCount[$key][0]['assignedCount'] = '--';
                        $subProjectsWithCount[$key][0]['CompletedCount'] = '--';
                        $subProjectsWithCount[$key][0]['PendingCount'] = '--';
                        $subProjectsWithCount[$key][0]['holdCount'] = '--';
                        $subProjectsWithCount[$key][0]['resource_emp_id'] = '--';
                    }
                }
            } else {
                $projectName = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                $table_name = Str::slug((Str::lower($projectName) . '_' . 'project'), '_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $calendarId = $request->CalendarId;
                if ($calendarId == "year") {
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                    $days = Carbon::now()->daysInYear;
                } else if ($calendarId == "month") {
                    $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                } else {
                    $days = 0;
                    $startDate = Carbon::now()->startOfDay()->toDateString();
                    $endDate = Carbon::now()->endOfDay()->toDateString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateString();
                // $endDate = Carbon::now()->endOfDay()->toDateString();
                if (class_exists($modelClass)) {
                    $key = 0;
                    $resourceData = $modelClass::whereIn('CE_emp_id', $resourceList)->select('CE_emp_id')->groupBy('CE_emp_id')->get()->toArray();
                    foreach ($resourceData as $resourceKey => $resourceDataVal) {
                        $subProjectsWithCount[$key][$resourceKey]['client_id'] = $clientDetails['id'];
                        $subProjectsWithCount[$key][$resourceKey]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                        $subProjectsWithCount[$key][$resourceKey]['sub_project_id'] = '--';
                        $subProjectsWithCount[$key][$resourceKey]['sub_project_name'] = '--';
                        $subProjectsWithCount[$key][$resourceKey]['resource_emp_id'] = $resourceDataVal["CE_emp_id"];
                        $subProjectsWithCount[$key][$resourceKey]['assignedCount'] = $modelClass::where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $subProjectsWithCount[$key][$resourceKey]['CompletedCount'] = $modelClass::where('chart_status', 'CE_Completed')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $subProjectsWithCount[$key][$resourceKey]['PendingCount'] = $modelClass::where('chart_status', 'CE_Pending')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $subProjectsWithCount[$key][$resourceKey]['holdCount'] = $modelClass::where('chart_status', 'CE_Hold')->where('CE_emp_id', $resourceDataVal["CE_emp_id"])
                            ->where(function ($query) use ($startDate, $endDate, $days) {
                                if ($days == 0) {
                                    $query;
                                } else {
                                    $query->whereBetween('invoke_date', [$startDate, $endDate]);
                                }
                            })->count();
                    }
                } else {
                    $key = 0;
                    $subProjectsWithCount[$key][0]['client_id'] = $clientDetails['id'];
                    $subProjectsWithCount[$key][0]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                    $subProjectsWithCount[$key][0]['sub_project_id'] = '--';
                    $subProjectsWithCount[$key][0]['sub_project_name'] = '--';
                    $subProjectsWithCount[$key][0]['assignedCount'] = '--';
                    $subProjectsWithCount[$key][0]['CompletedCount'] = '--';
                    $subProjectsWithCount[$key][0]['PendingCount'] = '--';
                    $subProjectsWithCount[$key][0]['holdCount'] = '--';
                    $subProjectsWithCount[$key][0]['resource_emp_id'] = '--';
                }
            }

            return response()->json(['subprojects' => $subProjectsWithCount]);
        } catch (\Exception $e) {
            Log::debug($e->getMessage());
        }
    }

    public function getCalendarFilter(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $calendarId = $request->CalendarId;
                $userType = $request->type;
                if ($calendarId == "week") {
                    $days = 7;
                    $startDate = Carbon::now()->startOfWeek()->startOfDay()->toDateString();
                    $endDate = Carbon::now()->endOfWeek()->endOfDay()->toDateString();
                } else if ($calendarId == "month") {
                    $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                } else if ($calendarId == "year") {
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                } 
                else {
                    $days = $calendarId;
                    $startDate = Carbon::now()->startOfDay()->toDateString();
                    $endDate = Carbon::now()->endOfDay()->toDateString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateString();
                // $endDate = Carbon::now()->endOfDay()->toDateString();
                $models = [];
                 $projects = $this->getProjects();
                foreach ($projects as $project) {
                    $project["client_name"] = Helpers::projectName($project["id"])->project_name;
                    if (count($project["subprject_name"]) > 0) {
                        foreach ($project["subprject_name"] as $key => $subProject) {
                            $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProject)), '_');
                            $modelName = Str::studly($table_name);
                            $modelClass = "App\\Models\\" . $modelName;
                            $models[] = $modelClass;
                        }
                    } else {
                        $subProjectText = "project";
                        $table_name = Str::slug((Str::lower($project["client_name"]) . '_' . Str::lower($subProjectText)), '_');
                        $modelName = Str::studly($table_name);
                        $modelClass = "App\\Models\\" . $modelName;
                        $models[] = $modelClass;
                    }
                }
                $assignedCounts = $completeCounts = $pendingCounts = $holdCounts = $reworkCounts = $totalCounts = $unAssignedCounts = [];
                foreach ($models as $model) {
                    if (class_exists($model) && $userType == "user") {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->where('CE_emp_id', $loginEmpId)->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                    } else if (class_exists($model) && $userType == "manager") {
                        $aCount = $model::where('chart_status', 'CE_Assigned')->whereNotNull('CE_emp_id')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $cCount = $model::where('chart_status', 'CE_Completed')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $pCount = $model::where('chart_status', 'CE_Pending')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $hCount = $model::where('chart_status', 'CE_Hold')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $rCount = $model::where('chart_status', 'Revoke')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $uACount = $model::where('chart_status', 'CE_Assigned')->whereNull('CE_emp_id')->whereBetween('invoke_date', [$startDate, $endDate])->count();
                        $assignedCounts[] = $aCount;
                        $completeCounts[] = $cCount;
                        $pendingCounts[] = $pCount;
                        $holdCounts[] = $hCount;
                        $reworkCounts[] = $rCount;
                        $unAssignedCounts[] = $uACount;
                    }
                }
                $totalAssignedCount = array_sum($assignedCounts);
                $totalCompleteCount = array_sum($completeCounts);
                $totalPendingCount = array_sum($pendingCounts);
                $totalHoldCount = array_sum($holdCounts);
                $totalReworkCount = array_sum($reworkCounts);
                $totalUnAssignedCounts = array_sum($unAssignedCounts);
                $totalCount = $totalAssignedCount + $totalCompleteCount + $totalPendingCount + $totalHoldCount + $totalReworkCount +$totalUnAssignedCounts;
                return response()->json(['totalCount' => $totalCount, 'totalAssignedCount' => $totalAssignedCount, 'totalCompleteCount' => $totalCompleteCount, 'totalPendingCount' => $totalPendingCount, 'totalHoldCount' => $totalHoldCount, 'totalReworkCount' => $totalReworkCount]);
                return $totalCount;
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function getProjects()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'user_id' => $userId,
                ];
                $data = retry(3, function () use ($payload) {
                    $client = new Client(['verify' => false]);
                    $response = $client->request('POST', config("constants.PRO_CODE_URL") . '/api/v1_users/get_clients_on_user', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        // $data = json_decode($response->getBody(), true);
                        $responseData = json_decode($response->getBody(), true);
                        if (isset($responseData)) {
                            return $responseData['clientList'];
                        } else {
                            throw new \Exception('clientList not found in the API response');
                        }
                    } elseif ($response->getStatusCode() == 429) {
                        $retryAfter = $response->getHeader('Retry-After')[0] ?? 60; // Default wait time 2 seconds
                        sleep($retryAfter);
                        throw new \Exception('Rate limit exceeded, retrying after ' . $retryAfter . ' seconds.');
                    } else {
                        throw new \Exception('API request failed with status: ' . $response->getStatusCode());
                    }
                }, 4000);
                return $data;
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function prjCalendarFilter(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $calendarId = $request->CalendarId;
                $projects = $this->getProjects();
                if ($calendarId == "year") {
                    // $days = Carbon::now()->daysInYear;
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                } else if ($calendarId == "month") {
                    // $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                } else {
                    $startDate = Carbon::now()->startOfDay()->toDateString();
                    $endDate = Carbon::now()->endOfDay()->toDateString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateString();
                // $endDate = Carbon::now()->endOfDay()->toDateString();
                $body_info = '<table class="table table-separate table-head-custom no-footer" id="uDashboard_clients_list">
                <thead>
                    <tr>
                        <th width="15px"></th>
                        <th>Client Name</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Pending</th>
                        <th>On Hold</th>
                    </tr>
                </thead>
                <tbody>';
                if (isset($projects) && count($projects) > 0) {
                    foreach ($projects as $data) {
                        $loginEmpId =
                            Session::get('loginDetails') &&
                            Session::get('loginDetails')['userDetail'] &&
                            Session::get('loginDetails')['userDetail']['emp_id'] != null
                            ? Session::get('loginDetails')['userDetail']['emp_id']
                            : '';
                        $projectName = Helpers::projectName($data["id"])->project_name;//$data['client_name'];
                        if (isset($data['subprject_name']) && !empty($data['subprject_name'])) {
                            $subproject_name = $data['subprject_name'];
                            $model_name = collect($subproject_name)
                                ->map(function ($item) use ($projectName) {
                                    return Str::studly(
                                        Str::slug(
                                            Str::lower($projectName) . '_' . Str::lower($item),
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
                            $assignedCount = 0;
                            $completedCount = 0;
                            $pendingCount = 0;
                            $holdCount = 0;
                            $modelFlag = 0;
                            if (class_exists($modelClass)) {
                                $assignedCount = $modelClass
                                    ::where(
                                        'chart_status',
                                        'CE_Assigned'
                                    )
                                    ->where('CE_emp_id', $loginEmpId)
                                    ->whereBetween('invoke_date', [$startDate, $endDate])
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
                        if ($modelTFlag > 0) {
                            $body_info .= '<tr class="clickable-client cursor_hand"><td class="details-control"></td>';
                            $body_info .= '<td>' . $data['client_name'] . '<input type="hidden" value=' . $data['id'] . '></td>';
                            $body_info .= '<td>' . $assignedTotalCount . '</td>';
                            $body_info .= '<td>' . $completedTotalCount . '</td>';
                            $body_info .= '<td>' . $pendingTotalCount . '</td>';
                            $body_info .= '<td>' . $holdTotalCount . '</td>';
                            $body_info .= '</tr>';
                        }
                    }
                }

                $body_info .= '</tbody></table>';
                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function mgrPrjCalendarFilter(Request $request)
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $calendarId = $request->CalendarId;
                 $projects = $this->getProjects();
                if ($calendarId == "year") {
                    // $days = Carbon::now()->daysInYear;
                    $startDate = Carbon::now()->startOfYear()->toDateString();
                    $endDate = Carbon::now()->endOfYear()->toDateString();
                } else if ($calendarId == "month") {
                    // $days =  Carbon::now()->daysInMonth;
                    $startDate = Carbon::now()->startOfMonth()->toDateString();
                    $endDate = Carbon::now()->endOfMonth()->toDateString();
                } else {
                    $startDate = Carbon::now()->startOfDay()->toDateString();
                    $endDate = Carbon::now()->endOfDay()->toDateString();
                }
                // $startDate = Carbon::now()->subDays($days)->startOfDay()->toDateString();
                // $endDate = Carbon::now()->endOfDay()->toDateString();
                $body_info = '<table class="table table-separate table-head-custom no-footer" id="mDashboard_clients_list">
                <thead>
                    <tr>
                        <th width="15px"></th>
                        <th>Client Name</th>
                        <th>Assigned</th>
                        <th>Completed</th>
                        <th>Pending</th>
                        <th>On Hold</th>
                    </tr>
                </thead>
                <tbody>';
                if (isset($projects) && count($projects) > 0) {
                    foreach ($projects as $data) {
                        $projectName = Helpers::projectName($data["id"])->project_name;//$data['client_name'];
                        if (isset($data['subprject_name']) && !empty($data['subprject_name'])) {
                            $subproject_name = $data['subprject_name'];
                            $model_name = collect($subproject_name)
                                ->map(function ($item) use ($projectName) {
                                    return Str::studly(
                                        Str::slug(
                                            Str::lower($projectName) . '_' . Str::lower($item),
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
                            $assignedCount = 0;
                            $completedCount = 0;
                            $pendingCount = 0;
                            $holdCount = 0;
                            $modelFlag = 0;

                            if (class_exists($modelClass)) {
                                $assignedCount = $modelClass
                                    ::whereIn('chart_status', ['CE_Assigned','CE_Inprocess'])
                                    ->whereNotNull('CE_emp_id')
                                    ->whereBetween('invoke_date', [$startDate, $endDate])
                                    ->count();
                                $completedCount = $modelClass
                                    ::where('chart_status', 'CE_Completed')
                                   
                                    ->whereBetween('invoke_date', [$startDate, $endDate])
                                    ->count();
                                $pendingCount = $modelClass
                                    ::where('chart_status', 'CE_Pending')
                                    ->whereBetween('invoke_date', [$startDate, $endDate])
                                    ->count();
                                $holdCount = $modelClass
                                    ::where('chart_status', 'CE_Hold')
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
                        if ($modelTFlag > 0) {
                            $body_info .= '<tr class="clickable-client cursor_hand project-clickable-row"><td class="details-control"></td>';
                            $body_info .= '<td>' . $data['client_name'] . '<input type="hidden" value=' . $data['id'] . '></td>';
                            $body_info .= '<td>' . $assignedTotalCount . '</td>';
                            $body_info .= '<td>' . $completedTotalCount . '</td>';
                            $body_info .= '<td>' . $pendingTotalCount . '</td>';
                            $body_info .= '<td>' . $holdTotalCount . '</td>';
                            $body_info .= '</tr>';
                        }
                    }
                }

                $body_info .= '</tbody></table>';
                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (\Exception $e) {
                Log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function inventoryUploadList(Request $request) {

        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
             try {
           
                if (isset($request->work_date) && !empty($request->work_date)) {
                    $work_date = explode(' - ', $request->work_date);
                    $start_date = date('Y-m-d 00:00:00', strtotime($work_date[0]));
                    $end_date = date('Y-m-d 23:59:59', strtotime($work_date[1]));
                }else{
                    $currentDate = Carbon::now();
                    $start_date = $currentDate->subMonths(2)->startOfMonth()->format('Y-m-d 00:00:00');
                    $end_date = $currentDate->addMonths(2)->endOfMonth()->format('Y-m-d 23:59:59');
                }
                if (isset($request->project_id)) {
                    $client_data = InventoryExeFile::where('project_id', '=', $request->project_id)
                            ->where('sub_project_id', '=', $request->sub_project_id)
                            ->where(function ($query) use ($start_date, $end_date) {
                                    if (!empty($start_date) && !empty($end_date)) {
                                        $query->whereBetween('exe_date', [$start_date, $end_date]);
                                    }else{
                                        $query;
                                    }
                                })->get();
                } else {
                    $client_data = InventoryExeFile::
                    where(function ($query) use ($start_date, $end_date) {
                         if (!empty($start_date) && !empty($end_date)) {
                             $query->whereBetween('exe_date', [$start_date, $end_date]);
                         }else{
                             $query;
                         }
                     })->get();
                }//dd($client_data);
                // if (count($client_data) > 0) {
                $body_info = '<table class="table table-separate table-head-custom no-footer" id="report_list">
                            <thead>
                            <tr>
                                <th>Project</th>
                                <th>Sub Project</th>
                                <th>Uploaded Count</th>
                                <th>Uploaded Date</th>
                                <th>Uploaded Status</th>
                            </tr>
                            </thead><tbody>';

                foreach ($client_data as $data) {
                    $projectName = Helpers::projectName($data->project_id)->aims_project_name;//dd($data->sub_project_id != null);
                    $subProjectName = $data->sub_project_id != null ?  (Helpers::subProjectName($data->project_id,$data->sub_project_id) != null ? Helpers::subProjectName($data->project_id,$data->sub_project_id)->sub_project_name : '--'): '--';
                    $inventoryCount =  $data->inventory_count !=null ? $data->inventory_count : '--';
                    $body_info .= '<tr>';           
                        $body_info .= '<td class="wrap-text">' . $projectName. '</td>
                        <td class="wrap-text">' . $subProjectName . '</td>
                        <td class="wrap-text">' . $inventoryCount . '</td>
                        <td class="wrap-text">' . date('m/d/Y H:i:s',strtotime($data->exe_date)) . '</td>
                        <td class="wrap-text">' . ucfirst($data->upload_status) . '</td>';
                        $body_info .= '</tr>';
                    }                 
                    $body_info .= '</tbody></table>';
            

                return response()->json([
                    'success' => true,
                    'body_info' => $body_info,
                ]);
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function projectReasonSave(Request $request) {
        
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data = $request->all();
                $data['manager_id'] = Session::get('loginDetails')['userDetail']['id'];
                $projectReason = ProjectReason::create($data);
                if ($projectReason) {
                    return response()->json([
                        'success' => true
                    ]);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to save project reason'
                        ]);
                }
            } catch (Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
}
