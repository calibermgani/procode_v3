<?php

namespace App\Http\Controllers\QA;

use App\Http\Controllers\Controller;
use App\Http\Helper\Admin\Helpers as Helpers;
use App\Models\CallerChartsWorkLogs;
use App\Models\formConfiguration;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\project;
use App\Models\subproject;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use App\Models\QualitySampling;
use App\Models\QASubStatus;
use Illuminate\Support\Facades\Mail;
use App\Mail\ManagerRebuttalMail;
use App\Models\CCEmailIds;
use App\Models\qaClassCatScope;
use App\Models\ProjectColSearchConfig;
use App\Exports\ProductionExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Jobs\getemailsAboveTlLevelJob;
use Illuminate\Support\Facades\Cache;
use App\Jobs\GetProjJob;
use App\Jobs\GetSubPrjJob;

ini_set('memory_limit', '1024M');
class QAProductionController extends Controller
{
    public function clients()
    {
        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'user_id' => $userId,
                ];
                $client = new Client(['verify' => false]);
                $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_clients_on_user', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                } else {
                    return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }
                $projects = $data['clientList'];
                return view('QAProduction/clients', compact('projects'));
            } catch (\Exception $e) {
                log::debug($e->getMessage());
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
            $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_practice_on_client', [
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
            foreach ($subprojects as $key => $data) {
                $subProjectsWithCount[$key]['client_id'] = $clientDetails['id'];
                $subProjectsWithCount[$key]['client_name'] = Helpers::projectName($clientDetails["id"])->project_name;//$clientDetails['client_name'];
                $subProjectsWithCount[$key]['sub_project_id'] = $data['id'];
                $subProjectsWithCount[$key]['sub_project_name'] = $data['name'];
                $projectName = $subProjectsWithCount[$key]['client_name'];
                $table_name = Str::slug((Str::lower($projectName) . '_' . Str::lower($subProjectsWithCount[$key]['sub_project_name'])), '_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        $subProjectsWithCount[$key]['assignedCount'] = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                        $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    } else {
                        $subProjectsWithCount[$key]['assignedCount'] = '--';
                        $subProjectsWithCount[$key]['CompletedCount'] = '--';
                        $subProjectsWithCount[$key]['PendingCount'] = '--';
                        $subProjectsWithCount[$key]['holdCount'] = '--';
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $subProjectsWithCount[$key]['assignedCount'] = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $subProjectsWithCount[$key]['CompletedCount'] = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['PendingCount'] = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $subProjectsWithCount[$key]['holdCount'] = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    } else {
                        $subProjectsWithCount[$key]['assignedCount'] = '--';
                        $subProjectsWithCount[$key]['CompletedCount'] = '--';
                        $subProjectsWithCount[$key]['PendingCount'] = '--';
                        $subProjectsWithCount[$key]['holdCount'] = '--';
                    }
                }

            }
            return response()->json(['subprojects' => $subProjectsWithCount]);
        } catch (\Exception $e) {
            log::debug($e->getMessage());
        }

    }

    public function clientAssignedTab(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            $client = new Client(['verify' => false]);
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName);
                if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
                    $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
                   }
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                if (class_exists($modelClass)) {
                    $query = $modelClass::query();
                    $searchData = [];   
                    if($request['_token'] != null) {
                        foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                        $searchData[$key] = $value;
                            if (is_array($value)) {
                                $value = implode('_el_', $value); 
                            }

                            // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                            if (is_numeric($value) || is_bool($value)) {
                                $query->where($key, $value);  // Exact match for numeric/boolean
                            } elseif ($this->isDate($value)) {  // Check if it's a date
                                $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                            } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                            } else {
                                if($value != null) {
                                $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                }
                            }
                        }
                    }
                } else {
                    return redirect()->back();
                }
                $modelClassDatas = "App\\Models\\" . $modelName . 'Datas';
                $assignedProjectDetails = collect();
                $assignedDropDown = [];
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $existingCallerChartsWorkLogs = [];
                $assignedProjectDetailsStatus = [];
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                // if($decodedPracticeName == '--') {
                // $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName);
                // } else {
                //     $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName,'else');
                // }
             
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','QA_Inprocess')->orderBy('id','desc')->pluck('record_id')->toArray();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        // $assignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->orderBy('id', 'ASC')->paginate(50);
                        $assignedProjectDetails = $query->whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNotNull('QA_emp_id')->where('qa_work_status','Sampling');                        
                        if (!empty($existingCallerChartsWorkLogs)) {
                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                        if (!empty($existingCallerChartsWorkLogsInprocess)) {
                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                        }
                        $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(50);
                        $assignedDropDownIds = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->select('QA_emp_id')->groupBy('QA_emp_id')->pluck('QA_emp_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        // $payload = [
                        //     'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        //     'client_id' => $decodedProjectName,
                        //     'user_id' => $userId,
                        // ];

                        // $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                        //     'json' => $payload,
                        // ]);
                        // if ($response->getStatusCode() == 200) {
                        //     $data = json_decode($response->getBody(), true);
                        // } else {
                        //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                        // }
                        // $assignedDropDown = array_filter($data['userDetail']);
                    } else {
                        return redirect()->back();
                       }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        // $assignedProjectDetails = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->paginate(50);//dd($assignedProjectDetails);
                        $existingCallerChartsWorkLogsInprocess = CallerChartsWorkLogs::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('emp_id',$loginEmpId)->where('record_status','QA_Inprocess')->orderBy('id','desc')->pluck('record_id')->toArray();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedProjectDetails = $query->whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id',$loginEmpId);
                        if (!empty($existingCallerChartsWorkLogs)) {
                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                        if (!empty($existingCallerChartsWorkLogsInprocess)) {
                            $assignedProjectDetails = $assignedProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogsInprocess) . ') DESC');
                        }
                        $assignedProjectDetails = $assignedProjectDetails->orderBy('id', 'ASC')->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    } else {
                        return redirect()->back();
                       }
                }//dd($assignedProjectDetails);
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientAssignedTab', compact('assignedProjectDetails', 'columnsHeader', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'modelClass', 'clientName', 'subProjectName', 'assignedDropDown', 'existingCallerChartsWorkLogs', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'assignedProjectDetailsStatus','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount','arActionListVal','rebuttalCount','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData','arStatusList'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientPendingTab(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClass::query();
                $searchData = [];
                if($request['_token'] != null) {
                        foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                        $searchData[$key] = $value;
                            if (is_array($value)) {
                                $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                            }

                            // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                            if (is_numeric($value) || is_bool($value)) {
                                $query->where($key, $value);  // Exact match for numeric/boolean
                            } elseif ($this->isDate($value)) {  // Check if it's a date
                                $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                            } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                            } else {
                                if($value != null) {
                                  $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                }
                            }
                        }
                    }
                $pendingProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $existingCallerChartsWorkLogs = [];
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        // $pendingProjectDetails = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Pending')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $pendingProjectDetails = $query->where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate]);
                        if (!empty($existingCallerChartsWorkLogs)) {
                            $pendingProjectDetails = $pendingProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                        $pendingProjectDetails = $pendingProjectDetails->orderBy('id', 'ASC')->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        // $pendingProjectDetails = $modelClass::where('chart_status', 'QA_Pending')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Pending')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $pendingProjectDetails = $query->where('chart_status', 'QA_Pending')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate]);
                        if (!empty($existingCallerChartsWorkLogs)) {
                            $pendingProjectDetails = $pendingProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                        $pendingProjectDetails = $pendingProjectDetails->orderBy('id', 'ASC')->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal =  Helpers::qaSubStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientPendingTab', compact('pendingProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'existingCallerChartsWorkLogs', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount','arActionListVal','rebuttalCount','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData','arStatusList'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientHoldTab(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date', 
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClass::query();
                $searchData = [];
                if($request['_token'] != null) {
                        foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                        $searchData[$key] = $value;
                            if (is_array($value)) {
                                $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                            }

                            // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                            if (is_numeric($value) || is_bool($value)) {
                                $query->where($key, $value);  // Exact match for numeric/boolean
                            } elseif ($this->isDate($value)) {  // Check if it's a date
                                $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                            } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                            } else {
                                if($value != null) {
                                  $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                }
                            }
                        }
                    }
                $holdProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $existingCallerChartsWorkLogs = [];
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        // $holdProjectDetails = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Hold')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $holdProjectDetails = $query->where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate]);
                        if (!empty($existingCallerChartsWorkLogs)) {
                            $holdProjectDetails = $holdProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                        $holdProjectDetails = $holdProjectDetails->orderBy('id', 'ASC')->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        // $holdProjectDetails = $modelClass::where('chart_status', 'QA_Hold')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Hold')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $holdProjectDetails = $query->where('chart_status', 'QA_Hold')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate]);
                        if (!empty($existingCallerChartsWorkLogs)) {
                            $holdProjectDetails = $holdProjectDetails->orderByRaw('FIELD(id, ' . implode(',', $existingCallerChartsWorkLogs) . ') DESC'); 
                        }
                        $holdProjectDetails = $holdProjectDetails->orderBy('id', 'ASC')->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal =  Helpers::qaSubStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientOnholdTab', compact('holdProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'existingCallerChartsWorkLogs','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount','arActionListVal','rebuttalCount','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData','arStatusList'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientCompletedTab(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClass::query();
                $searchData = [];
                if($request['_token'] != null) {
                        foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                        $searchData[$key] = $value;
                            if (is_array($value)) {
                                $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                            }

                            // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                            if (is_numeric($value) || is_bool($value)) {
                                $query->where($key, $value);  // Exact match for numeric/boolean
                            } elseif ($this->isDate($value)) {  // Check if it's a date
                                $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                            } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                            } else {
                                if($value != null) {
                                  $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                }
                            }
                        }
                    }
                $completedProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        $completedProjectDetails = $query->where('chart_status', 'QA_Completed')->orderBy('id', 'ASC')->whereBetween('updated_at',[$startDate,$endDate])->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $completedProjectDetails = $query->where('chart_status', 'QA_Completed')->orderBy('id', 'ASC')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $qaStatusList = Helpers::qaStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientCompletedTab', compact('completedProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields','popupQAEditableFields','qaSubStatusListVal','qaStatusList','autoCloseCount','unAssignedCount','arStatusList','arActionListVal','rebuttalCount','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientReworkTab(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClass::query();
                $searchData = [];
                if($request['_token'] != null) {
                        foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                        $searchData[$key] = $value;
                            if (is_array($value)) {
                                $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                            }

                            // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                            if (is_numeric($value) || is_bool($value)) {
                                $query->where($key, $value);  // Exact match for numeric/boolean
                            }  elseif ($this->isDate($value)) {  // Check if it's a date
                                $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                            } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                            } else {
                                if($value != null) {
                                  $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                }
                            }
                        }
                    }
                $revokeProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        $revokeProjectDetails = $query->where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $modelClassDuplcates = "App\\Models\\" . $modelName;
                        $duplicateCount = $modelClassDuplcates::count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $revokeProjectDetails = $query->where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->pagiante(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientReworkTab', compact('revokeProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function clientDuplicateTab(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            try {
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['id', 'duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClassDuplcates = "App\\Models\\" . $modelName . "Duplicates";
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClassDuplcates::query();
                $searchData = [];
                if($request['_token'] != null) {
                        foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                        $searchData[$key] = $value;
                            if (is_array($value)) {
                                $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                            }

                            // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                            if (is_numeric($value) || is_bool($value)) {
                                $query->where($key, $value);  // Exact match for numeric/boolean
                            } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                            } else {
                                $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                $duplicateProjectDetails = collect();
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClassDuplcates)) {
                        $duplicateProjectDetails = $query->orderBy('id', 'ASC')->whereBetween('updated_at',[$startDate,$endDate])->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClassDuplcates)) {
                        $duplicateProjectDetails =$query->where('chart_status', 'CE_Assigned')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id', 'ASC')->paginate(50);
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();                    }
                }
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientDuplicateTab', compact('duplicateProjectDetails', 'columnsHeader', 'clientName', 'subProjectName', 'modelClass', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount','rebuttalCount','projectColSearchFields','projectColSearchFieldsType','searchData'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function qaClientCompletedDatasDetails(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $currentTime = Carbon::now();
                $data['emp_id'] = Session::get('loginDetails')['userDetail']['emp_id'];
                $data['project_id'] = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $data['sub_project_id'] = $data['subProjectName'] == '--' ? NULL : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($data['project_id'])->project_name;
                $decodedsubProjectName = $data['sub_project_id'] == NULL ? 'project' :Helpers::subProjectName($data['project_id'] ,$data['sub_project_id'])->sub_project_name;
                $data['start_time'] = $currentTime->format('Y-m-d H:i:s');
                $data['record_status'] = 'QA_'.ucwords($data['urlDynamicValue']);
                 $existingRecordId = CallerChartsWorkLogs::where('project_id', $data['project_id'])->where('sub_project_id',$data['sub_project_id'])->where('record_id',$data['record_id'])->where('record_status',$data['record_status'])->where('end_time',NULL)->first();

                if(empty($existingRecordId)) {
                    $startTimeVal = $data['start_time'];
                    $save_flag = CallerChartsWorkLogs::create($data);
                } else {
                    $startTimeVal = $existingRecordId->start_time;
                    $save_flag = 1;
                }
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                $clientData = $modelClassDatas::where('parent_id',$data['record_id'])->orderBy('id','desc')->first();
                if($clientData != null) {
                    $clientData = $clientData->toArray();
                } else {
                    $clientData = $modelClass::where('id',$data['record_id'])->first();
                }
                if(isset($clientData) && !empty($clientData)) {
                   return response()->json(['success' => true,'clientData'=>$clientData,'startTimeVal'=>$startTimeVal]);
                } else {
                    return response()->json(['success' => false]);
                }
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function qaclientViewDetails(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data =  $request->all();
                $decodedProjectName = Helpers::encodeAndDecodeID($data['clientName'], 'decode');
                $decodedPracticeName = $data['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($data['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                // $decodedsubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                // $modelClassDatas = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName)).'Datas';
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName));
                $clientData = $modelClassDatas::where('parent_id',$data['record_id'])->orderBy('id','desc')->first();
                if($clientData != null) {
                    $clientData = $clientData->toArray();
                } else {
                    $clientData = $modelClass::where('id',$data['record_id'])->first();
                }
                if(isset($clientData) && !empty($clientData)) {
                   return response()->json(['success' => true,'clientData'=>$clientData]);
                } else {
                    return response()->json(['success' => false]);
                }
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientsStore(Request $request,$clientName,$subProjectName) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                // $data = $request->all();
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName =  $subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                // $decodedsubProjectName = Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $originalModelClass = "App\\Models\\" . $modelName;
                $modelClassRevokeHistory = "App\\Models\\" . $modelName.'RevokeHistory';
                // $modelClass = "App\\Models\\" . preg_replace('/[^A-Za-z0-9]/', '',ucfirst($decodedClientName).ucfirst($decodedsubProjectName)).'Datas';
                $data = [];
                foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                    if (is_array($value)) {
                        $data[$key] = in_array(null, $value, true) ? null : implode('_el_', $value);
                    } else {
                        $data[$key] = $value;
                    }
                }
                $data['invoke_date'] = date('Y-m-d',strtotime($data['invoke_date']));
                $data['parent_id'] = $data['idValue'];
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','desc')->first();
                $record = $originalModelClass::where('id', $data['parent_id'])->first();
                $data['QA_rework_comments']=$data['QA_rework_comments'] != null ? str_replace("\r\n", '_el_', $data['QA_rework_comments']) : $data['QA_rework_comments'];
                $data['QA_rework_comments'] = preg_replace('/(_el_){2,}/', '_el_', $data['QA_rework_comments']);
                $data['QA_comments_count'] = $data['QA_rework_comments'] != null ? count(explode('_el_', $data['QA_rework_comments'])) : 0;
                $data['qa_work_date'] = NULL;
                $data['qa_at'] = Carbon::now()->format('Y-m-d H:i:s');
                if($data['chart_status'] == "QA_Completed") {
                    $data['qa_work_date'] = Carbon::now()->format('Y-m-d');
                }
                // if($data['chart_status'] == "Revoke") {
                //     if($datasRecord['coder_error_count'] >= 1) {
                //         $data['tl_error_count'] = $datasRecord['tl_error_count']+1;
                //         $data['coder_error_count'] = $datasRecord['coder_error_count'];
                //      } else {dd($datasRecord['coder_error_count'],'coder_error_count');
                //          $data['coder_error_count'] = $datasRecord['coder_error_count']+1;
                //          $data['tl_error_count'] = $datasRecord['tl_error_count'];
                //      }
                // } else {
                //     $data['coder_error_count'] = $datasRecord['coder_error_count'];
                //     $data['tl_error_count'] = $datasRecord['tl_error_count'];
                // }

                 if($data['chart_status'] == "QA_Completed" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['qa_error_count'] = 1;
                } else {//dd($datasRecord['qa_error_count']);
                    $data['qa_error_count'] = $datasRecord['qa_error_count'];
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['tl_error_count'] = 1;
                    // $toMailId = "mgani@caliberfocus.com";
                    // $ccMailId = "vijayalaxmi@caliberfocus.com";
                    // $mailHeader = "Rebuttal Mail";dd($mailHeader,$data);
                    // Mail::to($toMailId)->cc($ccMailId)->send(new ManagerRebuttalMail($mailHeader));
                } else {
                    $data['tl_error_count'] = $datasRecord['tl_error_count'];
                }
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                  $data['annex_coder_trends'] = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ? str_replace("\r\n", '_el_', $data['annex_coder_trends']) : null;
                }
                if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                   $annex_qa_trends = isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null ?  explode('_el_',str_replace("\r\n", '_el_', $data['annex_qa_trends'])) : null;
                }
                   // $data['annex_qa_trends_count'] = $data['annex_qa_trends'] != null ? count(explode('_el_', $data['annex_qa_trends'])) : 0;
                if(isset($annex_qa_trends) && $annex_qa_trends != null) {
                    foreach( $annex_qa_trends as $trend){
                        if (str_contains($trend, 'CPT -') && !str_contains($trend, 'modifier')) {
                            $array[]= $trend;
                            $data['qa_cpt_trends'] = implode('_el_', $array);
                        }
                        if (str_contains($trend, 'ICD -') && !str_contains($trend, 'modifier')) {
                            $a1[]= $trend;
                            $data['qa_icd_trends'] =implode('_el_', $a1);
                        }
                        if (str_contains($trend, 'modifier ')) {
                            $a2[]= $trend;
                            $data['qa_modifiers'] = implode('_el_', $a2);
                        }
                    }
               }
                if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                    $data['annex_qa_trends'] = $data['annex_qa_trends'] != null ?  str_replace("\r\n", '_el_', $data['annex_qa_trends']) : null;      
                }// dd($data);
                if($data['chart_status'] == "Revoke") {
                    $data['coder_error_count'] = $datasRecord['coder_error_count'] == null ? 1 : $datasRecord['coder_error_count']+1;
                    if($datasRecord != null) {
                        $newDataRecord = $datasRecord->getAttributes();
                        unset($newDataRecord["id"]);
                       $modelClassRevokeHistory::create($newDataRecord);
                    } else {
                        $modelClassRevokeHistory::create($data);
                    }
                } else {
                    $data['coder_error_count'] = $datasRecord['coder_error_count'];
                }
                $currentTime = Carbon::now();
                $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('record_status',$data['record_old_status'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->get();
                    if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                        foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                            $start_time = Carbon::parse($callChartWorkLog->start_time);
                            $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                            $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                        }
                   }
                   $updateData = array_filter([
                        'chart_status' => $data['chart_status'] ?? null,
                        'qa_hold_reason' => $data['qa_hold_reason'] ?? null,
                        'QA_rework_comments' => $data['QA_rework_comments'] ?? null,
                        'coder_error_count' => $data['coder_error_count'] ?? null,
                        'qa_error_count' => $data['qa_error_count'] ?? null,
                        'tl_error_count' => $data['tl_error_count'] ?? null,
                        'QA_status_code' => $data['QA_status_code'] ?? null,
                        'QA_sub_status_code' => $data['QA_sub_status_code'] ?? null,
                        'QA_comments_count' => $data['QA_comments_count'] ?? null,
                        'qa_classification' => $data['qa_classification'] ?? null,
                        'qa_category' => $data['qa_category'] ?? null,
                        'qa_scope' => $data['qa_scope'] ?? null,
                        'qa_work_date' => $data['qa_work_date'] ?? null,
                        'qa_at' => $data['qa_at'] ?? null,
                    ], function ($value) {
                        return !is_null($value);
                    });
                  
                if($datasRecord != null) {
                    $datasRecord->update($data);
                    if (!empty($updateData)) {
                        $record->update($updateData);
                    }
                    // $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count'],
                    // 'qa_classification' => $data['qa_classification'],'qa_category' => $data['qa_category'],'qa_scope' => $data['qa_scope'],'qa_work_date' => $data['qa_work_date'],'qa_at' => $data['qa_at']]);
                } else {
                    if (!empty($updateData)) {
                        $record->update($updateData);
                    }
                    // $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count'],
                    // 'qa_classification' => $data['qa_classification'],'qa_category' => $data['qa_category'],'qa_scope' => $data['qa_scope'],'qa_work_date' => $data['qa_work_date'],'qa_at' => $data['qa_at']]);
                    $modelClass::create($data);
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    getemailsAboveTlLevelJob::dispatch($decodedProjectName)->delay(now()->addSeconds(5));
                    $prjemailsCacheKey = 'project_'.$decodedProjectName.'emailsAboveTlLevel';
                    $apiData = Cache::get($prjemailsCacheKey, 0);  
                    // $client = new Client(['verify' => false]);
                    // $payload = [
                    //     'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    //     'client_id' => $decodedProjectName
                    // ];
                    //  $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_emails_above_tl_level', [
                    //     'json' => $payload
                    // ]);
                    // if ($response->getStatusCode() == 200) {
                    //     $apiData = json_decode($response->getBody(), true);
                    // } else {
                    //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                    // }
                    $toMailId = $apiData['people_email'];
                    $reportingPerson = $apiData['reprting_person'];
                    $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'manager rebuttal')->first();
                    $ccMailId = explode(",", $ccMail->cc_emails);
                    $mailHeader = "Assistance Needed: ".$decodedClientName." Audit Rebuttal";
                    $mailBody = $record;
                    if(isset($toMailId) && !empty($toMailId)) {
                       Mail::to($toMailId)->cc($ccMailId)->send(new ManagerRebuttalMail($mailHeader, $mailBody, $reportingPerson));
                    }
                }
                
                // $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                // ->where('project_id', $decodedProjectName)
                // ->where('sub_project_id', $decodedPracticeName)
                // ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();
                // if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                //     $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                //     $time_difference = $currentTime->diff($start_time);
                //     $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                //     $callChartWorkLogExistingRecord->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );

                // }
                // $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                // ->where('project_id', $decodedProjectName)
                // ->where('sub_project_id', $decodedPracticeName)
                // ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->get();
                //     if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                //         foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                //             $start_time = Carbon::parse($callChartWorkLog->start_time);
                //             $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                //             $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                //         }
                //    }
                return redirect('qa_production/qa_projects_assigned/'.$clientName.'/'.$subProjectName);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
                return redirect('qa_production/qa_projects_assigned/'.$clientName.'/'.$subProjectName)->with('error','An unexpected error occurred. Please recheck data once.');
            }
        } else {
            return redirect('/');
        }
    }
    public function clientsUpdate(Request $request,$clientName,$subProjectName) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                 $data = $request->all();//dd($data);
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName =  $subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $originalModelClass = "App\\Models\\" . $modelName;
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $modelClassRevokeHistory = "App\\Models\\" . $modelName.'RevokeHistory';
                $data = [];
                foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                    if (is_array($value)) {
                        $data[$key] = in_array(null, $value, true) ? null : implode('_el_', $value);
                    } else {
                        $data[$key] = $value;
                    }
                }
                $data['invoke_date'] = date('Y-m-d',strtotime($data['invoke_date']));
                $data['parent_id'] = $data['parentId'];
                $datasRecord = $modelClass::where('parent_id', $data['parent_id'])->orderBy('id','desc')->first();
                $data['QA_rework_comments']=$data['QA_rework_comments'] != null ? str_replace("\r\n", '_el_', $data['QA_rework_comments']) : $data['QA_rework_comments'];
                $data['QA_rework_comments'] = preg_replace('/(_el_){2,}/', '_el_', $data['QA_rework_comments']);
                $data['QA_comments_count'] = $data['QA_rework_comments'] != null ? count(explode('_el_', $data['QA_rework_comments'])) : 0;
                $data['qa_work_date'] = NULL;
                $data['qa_at'] = Carbon::now()->format('Y-m-d H:i:s');
                if($data['chart_status'] == "QA_Completed") {
                    $data['qa_work_date'] = Carbon::now()->format('Y-m-d');
                }
                if($data['chart_status'] == "Revoke") {
                  $data['coder_error_count'] = $datasRecord['coder_error_count']+1;
                }
               if($data['chart_status'] == "QA_Completed" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['qa_error_count'] = 1;
                } else {
                    $data['qa_error_count'] = $datasRecord['qa_error_count'];
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    $data['tl_error_count'] = 1;
                } else {
                    $data['tl_error_count'] = $datasRecord['tl_error_count'];
                }
                if(isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null) {
                    $data['annex_coder_trends'] = isset($data['annex_coder_trends']) && $data['annex_coder_trends'] != null ? str_replace("\r\n", '_el_', $data['annex_coder_trends']) : null ;
                 }  
                if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                  $annex_qa_trends = isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null ?  explode('_el_',str_replace("\r\n", '_el_', $data['annex_qa_trends'])) : null ;
                }
                //$data['annex_qa_trends_count'] = isset($data['annex_qa_trends_count']) && $data['annex_qa_trends'] != null ?? count(explode('_el_', $data['annex_qa_trends'])) ;
               
                if(isset($annex_qa_trends)  && $annex_qa_trends != null) {
                    foreach( $annex_qa_trends as $trend){
                        if (str_contains($trend, 'CPT -') && !str_contains($trend, 'modifier')) {
                            $array[]= $trend;
                            $data['qa_cpt_trends'] = implode('_el_', $array);
                        }
                        if (str_contains($trend, 'ICD -') && !str_contains($trend, 'modifier')) {
                            $a1[]= $trend;
                            $data['qa_icd_trends'] =implode('_el_', $a1);
                        }
                        if (str_contains($trend, 'modifier ')) {
                            $a2[]= $trend;
                            $data['qa_modifiers'] = implode('_el_', $a2);
                        }
                    }
               }
               if(isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null) {
                 $data['annex_qa_trends'] = isset($data['annex_qa_trends']) && $data['annex_qa_trends'] != null ?  str_replace("\r\n", '_el_', $data['annex_qa_trends']) : null ;//dd($data);
               }
               if($data['chart_status'] == "Revoke") {
                $data['coder_error_count'] = $datasRecord['coder_error_count'] == null ? 1 : $datasRecord['coder_error_count']+1;
                if($datasRecord != null) {
                    $newDataRecord = $datasRecord->getAttributes();
                    unset($newDataRecord["id"]);
                   $modelClassRevokeHistory::create($newDataRecord);
                } else {
                    $modelClassRevokeHistory::create($data);
                }
            } else {
                $data['coder_error_count'] = $datasRecord['coder_error_count'];
            }
            $updateData = array_filter([
                'chart_status' => $data['chart_status'] ?? null,
                'qa_hold_reason' => $data['qa_hold_reason'] ?? null,
                'QA_rework_comments' => $data['QA_rework_comments'] ?? null,
                'coder_error_count' => $data['coder_error_count'] ?? null,
                'qa_error_count' => $data['qa_error_count'] ?? null,
                'tl_error_count' => $data['tl_error_count'] ?? null,
                'QA_status_code' => $data['QA_status_code'] ?? null,
                'QA_sub_status_code' => $data['QA_sub_status_code'] ?? null,
                'QA_comments_count' => $data['QA_comments_count'] ?? null,
                'qa_classification' => $data['qa_classification'] ?? null,
                'qa_category' => $data['qa_category'] ?? null,
                'qa_scope' => $data['qa_scope'] ?? null,
                'qa_work_date' => $data['qa_work_date'] ?? null,
                'qa_at' => $data['qa_at'] ?? null,
            ], function ($value) {
                return !is_null($value);
            });

                if($datasRecord != null) {
                    $fieldsToExclude = [
                        'annex_coder_trends',
                        'coder_cpt_trends',
                        'coder_icd_trends',
                        'coder_modifiers',
                        'qa_cpt_trends',
                        'qa_icd_trends',
                        'qa_modifiers',
                        'annex_qa_trends',
                    ];
                    
                    $data = array_diff_key($data, array_flip($fieldsToExclude));
                  $datasRecord->update($data);
                  $record = $originalModelClass::where('id', $data['parent_id'])->first();
                    if (!empty($updateData)) {
                        $record->update($updateData);
                    }
                //   $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count'],'qa_classification' => $data['qa_classification'],'qa_category' => $data['qa_category'],'qa_scope' => $data['qa_scope'],'qa_work_date' => $data['qa_work_date'],'qa_at' => $data['qa_at']]);
                 } else {
                    $data['parent_id'] = $data['idValue'];
                    $record = $originalModelClass::where('id', $data['parent_id'])->first();
                    // $record->update( ['chart_status' => $data['chart_status'],'qa_hold_reason' => $data['qa_hold_reason'],'QA_rework_comments' => $data['QA_rework_comments'],'coder_error_count' => $data['coder_error_count'],'qa_error_count' => $data['qa_error_count'],'tl_error_count' => $data['tl_error_count'],'QA_status_code' => $data['QA_status_code'],'QA_sub_status_code' => $data['QA_sub_status_code'],'QA_comments_count' => $data['QA_comments_count'],'qa_classification' => $data['qa_classification'],'qa_category' => $data['qa_category'],'qa_scope' => $data['qa_scope'],'qa_work_date' => $data['qa_work_date'],'qa_at' => $data['qa_at']]);
                    if (!empty($updateData)) {
                        $record->update($updateData);
                    }
                    $modelClass::create($data);
                }
                if($data['chart_status'] == "Revoke" &&  $datasRecord['coder_rework_status'] == "Rebuttal") {
                    getemailsAboveTlLevelJob::dispatch($decodedProjectName)->delay(now()->addSeconds(5));
                    $prjemailsCacheKey = 'project_'.$decodedProjectName.'emailsAboveTlLevel';
                    $apiData = Cache::get($prjemailsCacheKey, 0); 
                    // $client = new Client(['verify' => false]);
                    // $payload = [
                    //     'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    //     'client_id' => $decodedProjectName
                    // ];
                    //  $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_emails_above_tl_level', [
                    //     'json' => $payload
                    // ]);
                    // if ($response->getStatusCode() == 200) {
                    //     $apiData = json_decode($response->getBody(), true);
                    // } else {
                    //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                    // }
                    $toMailId = $apiData['people_email'];
                    $reportingPerson = $apiData['reprting_person'];
                    $ccMail = CCEmailIds::select('cc_emails')->where('cc_module', 'manager rebuttal')->first();
                    $ccMailId = explode(",", $ccMail->cc_emails);
                    $mailHeader = "Assistance Needed: ".$decodedClientName." Audit Rebuttal";
                    $mailBody = $record;
                    if(isset($toMailId) && !empty($toMailId)) {
                        Mail::to($toMailId)->cc($ccMailId)->send(new ManagerRebuttalMail($mailHeader, $mailBody, $reportingPerson));
                    }
                }
                $currentTime = Carbon::now();
                // $callChartWorkLogExistingRecord = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                // ->where('record_status',$data['record_old_status'])
                // ->where('project_id', $decodedProjectName)
                // ->where('sub_project_id', $decodedPracticeName)
                // ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->first();
                // $start_time = Carbon::parse($callChartWorkLogExistingRecord->start_time);
                // $time_difference = $currentTime->diff($start_time);
                // $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                // if($callChartWorkLogExistingRecord && $callChartWorkLogExistingRecord != null) {
                //     $callChartWorkLogExistingRecord->update([
                //         'record_status' => $data['chart_status'],
                //         'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time
                //     ]);
                // }
                $callChartWorkLogExistingRecords = CallerChartsWorkLogs::where('record_id', $data['parent_id'])
                ->where('record_status',$data['record_old_status'])
                ->where('project_id', $decodedProjectName)
                ->where('sub_project_id', $decodedPracticeName)
                ->where('emp_id', Session::get('loginDetails')['userDetail']['emp_id'])->where('end_time',NULL)->get();
                    if ($callChartWorkLogExistingRecords->isNotEmpty()) {
                        foreach ($callChartWorkLogExistingRecords as $callChartWorkLog) {
                            $start_time = Carbon::parse($callChartWorkLog->start_time);
                            $work_time = $currentTime->diff($start_time)->format('%H:%I:%S');
                            $callChartWorkLog->update( ['record_status' => $data['chart_status'],'end_time' => $currentTime->format('Y-m-d H:i:s'),'work_time' => $work_time] );
                        }
                   }
                $tabUrl = lcfirst(str_replace('QA_', '', $data['record_old_status']));
                return redirect('qa_production/qa_projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName);
             } catch (\Exception $e) {
                log::debug($e->getMessage());
                $data = $request->all();
                $tabUrl = lcfirst(str_replace('QA_', '', $data['record_old_status']));
                return redirect('qa_production/qa_projects_'.$tabUrl.'/'.$clientName.'/'.$subProjectName)->with('error','An unexpected error occurred. Please recheck data once.');
            }
        } else {
            return redirect('/');
        }
    }

    public static function qaSubStatusList(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data = QASubStatus::where('status_code_id', $request['status_code_id'])->pluck('sub_status_code', 'id')->toArray();
                return response()->json(["subStatus" => $data]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientAutoClose(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            $client = new Client(['verify' => false]);
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                $query = $modelClass::query();
                $searchData = [];
                if($request['_token'] != null) {
                        foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                        $searchData[$key] = $value;
                            if (is_array($value)) {
                                $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                            }

                            // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                            if (is_numeric($value) || is_bool($value)) {
                                $query->where($key, $value);  // Exact match for numeric/boolean
                            } elseif ($this->isDate($value)) {  // Check if it's a date
                                $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                            } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                                $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                            } else {
                                if($value != null) {
                                  $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                                }
                            }
                        }
                    }
                $modelClassDatas = "App\\Models\\" . $modelName . 'Datas';
                $assignedProjectDetails = collect();
                $assignedDropDown = [];$userDetail = [];
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $existingCallerChartsWorkLogs = [];
                $assignedProjectDetailsStatus = [];
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                // if($decodedPracticeName == '--') {
                // $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName);
                // } else {
                //     $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName,'else');
                // }
                $payload = [
                    'token' => '1a32e71a46317b9cc6feb7388238c95d',
                    'client_id' => $decodedProjectName,
                 ];

                $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name_on_designation', [
                    'json' => $payload,
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                } else {
                    return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                }
                $userDetail  = array_filter($data['userDetail']);
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        $autoCloseProjectDetails = $query->where('qa_work_status', 'Auto_Close')->orderBy('id', 'ASC')->paginate(50);
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        $assignedDropDown = $userDetail;
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $autoCloseProjectDetails = $query->where('qa_work_status', 'Auto_Close')->orderBy('id', 'ASC')->paginate(50);
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['CE_Completed'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        if (isset($userDetail[$loginEmpId])) {
                            $assignedDropDown[$loginEmpId] = $userDetail[$loginEmpId];
                          }
                          $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientAutoClose', compact('autoCloseProjectDetails', 'columnsHeader', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'modelClass', 'clientName', 'subProjectName', 'assignedDropDown', 'existingCallerChartsWorkLogs', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'assignedProjectDetailsStatus','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount','arActionListVal','rebuttalCount','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData','arStatusList'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function samplingAssignee(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {

            try {

                $assigneeId = $request['assigneeId'];
                $decodedProjectName = Helpers::encodeAndDecodeID($request['clientName'], 'decode');
                $decodedPracticeName = $request['subProjectName'] == '--' ? '--' : Helpers::encodeAndDecodeID($request['subProjectName'], 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $modelClassDatas = "App\\Models\\" . $modelName.'Datas';
                $modelHistory = "App\\Models\\" . $modelName.'History';
                foreach($request['checkedRowValues'] as $data) {
                    $existingRecord = $modelClass::where('id',$data['value'])->first();
                    $historyRecord = $existingRecord->toArray();
                    $historyRecord['parent_id']= $historyRecord['id'];
                    unset($historyRecord['id']);
                    $modelHistory::create($historyRecord);
                    $existingModelClassDatasRecord = $modelClassDatas::where('parent_id',$data['value'])->first();
                    $existingRecord->update(['QA_emp_id' => $assigneeId,'qa_work_status' => 'Sampling','chart_status' => 'CE_Completed']);
                    $existingModelClassDatasRecord->update(['QA_emp_id' => $assigneeId,'qa_work_status' => 'Sampling','chart_status' => 'CE_Completed']);
                    
                }
                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function clientUnAssignedTab(Request $request,$clientName, $subProjectName)
    {

        if (Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null) {
            $client = new Client(['verify' => false]);
            try {
                $userId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] != null ? Session::get('loginDetails')['userDetail']['id'] : "";
                $loginEmpId = Session::get('loginDetails') && Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] != null ? Session::get('loginDetails')['userDetail']['emp_id'] : "";
                $empDesignation = Session::get('loginDetails') && Session::get('loginDetails')['userDetail']['user_hrdetails'] && Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] : "";
                $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
                $decodedPracticeName = $subProjectName == '--' ? '--' : Helpers::encodeAndDecodeID($subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' : Helpers::subProjectName($decodedProjectName, $decodedPracticeName)->sub_project_name;
                $table_name = Str::slug((Str::lower($decodedClientName) . '_' . Str::lower($decodedsubProjectName)), '_');
                $columnsHeader = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClass::query();
                $searchData = [];
               if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                       $searchData[$key] = $value;
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                        }

                        // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
                $modelClassDatas = "App\\Models\\" . $modelName . 'Datas';
                $assignedProjectDetails = collect();
                $assignedDropDown = [];
                $dept = Session::get('loginDetails')['userInfo']['department']['id'];
                $existingCallerChartsWorkLogs = [];
                $assignedProjectDetailsStatus = [];
                $duplicateCount = 0;
                $assignedCount = 0;
                $completedCount = 0;
                $pendingCount = 0;
                $holdCount = 0;
                $reworkCount = 0;
                $autoCloseCount = 0;
                $unAssignedCount = 0;
                $subProjectId = $subProjectName == '--' ? null : $decodedPracticeName;$startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                // if($decodedPracticeName == '--') {
                // $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName);
                // } else {
                //     $qasamplingDetails = QualitySampling::where('project_id',$decodedProjectName)->where('sub_project_id',$decodedPracticeName)->first();//dd($qasamplingDetails,$decodedProjectName,$decodedPracticeName,'else');
                // }

               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if (class_exists($modelClass)) {
                        $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                        $unAssignedProjectDetails =  $query->whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->orderBy('id', 'ASC')->paginate(50);
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedDropDownIds = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->select('QA_emp_id')->groupBy('QA_emp_id')->pluck('QA_emp_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status','Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                        $duplicateCount = $modelClassDuplcates::count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        // $payload = [
                        //     'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        //     'client_id' => $decodedProjectName,
                        //     'user_id' => $userId,
                        // ];

                        // $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name', [
                        //     'json' => $payload,
                        // ]);
                        // if ($response->getStatusCode() == 200) {
                        //     $data = json_decode($response->getBody(), true);
                        // } else {
                        //     return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                        // }
                        // $assignedDropDown = array_filter($data['userDetail']);
                    }
                } elseif ($loginEmpId) {
                    if (class_exists($modelClass)) {
                        $unAssignedProjectDetails =  $query->whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->paginate(50);//dd($assignedProjectDetails);
                        $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->whereIn('record_status', ['QA_Assigned','QA_Inprocess'])->orderBy('id', 'desc')->pluck('record_id')->toArray();
                        $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                        $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $reworkCount = $modelClass::where('chart_status', 'revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                        $assignedProjectDetailsStatus = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->orderBy('id', 'ASC')->pluck('chart_status')->toArray();
                        $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    }
                }
                $popUpHeader = formConfiguration::groupBy(['project_id', 'sub_project_id'])
                    ->where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)
                    ->select('project_id', 'sub_project_id')
                    ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                // $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaClientUnAssignedTab', compact('unAssignedProjectDetails', 'columnsHeader', 'popUpHeader', 'popupNonEditableFields', 'popupEditableFields', 'modelClass', 'clientName', 'subProjectName', 'assignedDropDown', 'existingCallerChartsWorkLogs', 'assignedCount', 'completedCount', 'pendingCount', 'holdCount', 'reworkCount', 'duplicateCount', 'assignedProjectDetailsStatus','popupQAEditableFields','qaSubStatusListVal','autoCloseCount','unAssignedCount','arActionListVal','rebuttalCount','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData','arStatusList'));

            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public function assigneeDropdown(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            $client = new Client(['verify' => false]);
            try {
                $userId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['id'] !=null ? Session::get('loginDetails')['userDetail']['id']:"";
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                    $payload = [
                        'token' => '1a32e71a46317b9cc6feb7388238c95d',
                        'client_id' => $decodedProjectName,
                        'user_id' => $userId,
                    ];

                    $response = $client->request('POST', config("constants.PRO_CODE_URL").'/api/v1_users/get_resource_name_resolv', [
                        'json' => $payload,
                    ]);
                    if ($response->getStatusCode() == 200) {
                        $data = json_decode($response->getBody(), true);
                    } else {
                        return response()->json(['error' => 'API request failed'], $response->getStatusCode());
                    }
                    $assignedDropDown = array_filter($data['userDetail']);
                    return response()->json(['assignedDropDown' => $assignedDropDown]);
                } catch (\Exception $e) {
                    log::debug($e->getMessage());
                }
        } else {
            return redirect('/');
        }
   }

   public function clientRebuttalTab(Request $request,$clientName,$subProjectName) {

    if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
        try {
            $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
            $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
            $decodedProjectName = Helpers::encodeAndDecodeID($clientName, 'decode');
            $decodedPracticeName = $subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($subProjectName, 'decode');
            $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
            $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
            $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
            $columnsHeader=[];
            if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['QA_emp_id','ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at','created_at', 'deleted_at'];
                    $columnsHeader = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($columnsHeader,'aging','aging_range');
                }
            $modelName = Str::studly($table_name);
            $modelClass = "App\\Models\\" . $modelName;
            $query = $modelClass::query();
            $searchData = [];
            if($request['_token'] != null) {
                foreach ($request->except('_token', 'parent', 'child','page') as $key => $value) {
                    $searchData[$key] = $value;
                    if (is_array($value)) {
                        $value = implode('_el_', $value);  // If it's an array, handle it accordingly
                    }

                    // Assuming 'like' is needed for partial match searches (optional), adjust based on requirements
                    if (is_numeric($value) || is_bool($value)) {
                        $query->where($key, $value);  // Exact match for numeric/boolean
                    }  elseif ($this->isDate($value)) {  // Check if it's a date
                        $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                    } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                        $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                    } else {
                        if($value != null) {
                          $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                        }
                    }

                }
            }
            $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();$yesterDayDate = Carbon::yesterday()->endOfDay()->toDateTimeString();$unAssignedCount = 0;
            $revokeProjectDetails = collect(); $duplicateCount = 0; $assignedCount=0; $completedCount = 0; $pendingCount = 0;   $holdCount =0;$reworkCount = 0;$existingCallerChartsWorkLogs = [];$subProjectId = $subProjectName == '--' ?  NULL : $decodedPracticeName;
           if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                if (class_exists($modelClass)) {
                    $rebuttalProjectDetails = $query->where('chart_status','Rebuttal')->orderBy('id','ASC')->where('ar_manager_rebuttal_status','agree')->paginate(50);
                    $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->count();
                    $completedCount = $modelClass::where('chart_status', 'QA_Completed')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $holdCount = $modelClass::where('chart_status', 'QA_Hold')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $reworkCount = $modelClass::where('chart_status', 'Revoke')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $modelClassDuplcates = "App\\Models\\" . $modelName . 'Duplicates';
                    $duplicateCount = $modelClassDuplcates::count();
                    $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Pending')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                    $unAssignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->count();
                    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->count();
            
                }
                } else if ($loginEmpId) {
                    if (class_exists($modelClass)) {
                    $rebuttalProjectDetails = $query->where('chart_status','Rebuttal')->orderBy('id','ASC')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->orderBy('id','ASC')->paginate(50);
                    $assignedCount = $modelClass::whereIn('chart_status',['CE_Completed','QA_Inprocess','Auto_Close'])->where('qa_work_status','Sampling')->where('QA_emp_id', $loginEmpId)->count();
                    $completedCount = $modelClass::where('chart_status', 'QA_Completed')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $pendingCount = $modelClass::where('chart_status', 'QA_Pending')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $holdCount = $modelClass::where('chart_status', 'QA_Hold')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $reworkCount = $modelClass::where('chart_status', 'Revoke')->where('QA_emp_id', $loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $autoCloseCount = $modelClass::where('qa_work_status', 'Auto_Close')->whereBetween('updated_at',[$startDate,$endDate])->count();
                    $existingCallerChartsWorkLogs = CallerChartsWorkLogs::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->where('emp_id', $loginEmpId)->where('end_time', null)->where('record_status', 'QA_Pending')->orderBy('id', 'desc')->pluck('record_id')->toArray();
                    $rebuttalCount = $modelClass::where('chart_status','Rebuttal')->where('ar_manager_rebuttal_status','agree')->where('CE_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->count();
                }
                }
                $dept= Session::get('loginDetails')['userInfo']['department']['id'];
                $popUpHeader =  formConfiguration::groupBy(['project_id', 'sub_project_id'])
                ->where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)
                ->select('project_id', 'sub_project_id')
                ->first();
                $popupNonEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'non_editable')->where('field_type_3', 'popup_visible')->get();
                $popupEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[3,1])->whereIn('user_type',[3,2])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $popupQAEditableFields = formConfiguration::where('project_id', $decodedProjectName)->where('sub_project_id', $subProjectId)->whereIn('input_type_editable',[2])->whereIn('user_type',[3,10])->where('field_type', 'editable')->where('field_type_3', 'popup_visible')->get();
                $qaSubStatusListVal = Helpers::qaSubStatusList();
                $qaStatusList = Helpers::qaStatusList();
                $arStatusList = Helpers::arStatusList();
                $arActionListVal = Helpers::arActionList();
                $qaClassificationVal = Helpers::qaClassification();
                $qaCategoryVal = Helpers::qaCategory();
                $qaScopeVal = Helpers::qaScope();
                $projectColSearchFields = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->get();
                $projectColSearchFieldsType = ProjectColSearchConfig::where('project_id',$decodedProjectName)->where('sub_project_id',$subProjectId)->where('status','Yes')->pluck('column_type','column_name')->toArray();
                return view('QAProduction/qaRebuttalTab',compact('rebuttalProjectDetails','columnsHeader','clientName','subProjectName','modelClass','assignedCount','completedCount','pendingCount','holdCount','reworkCount','duplicateCount','existingCallerChartsWorkLogs','popUpHeader','popupNonEditableFields','popupEditableFields','popupQAEditableFields','qaSubStatusListVal','unAssignedCount','qaStatusList','rebuttalCount','autoCloseCount','arStatusList','arActionListVal','qaClassificationVal','qaCategoryVal','qaScopeVal','projectColSearchFields','projectColSearchFieldsType','searchData'));

        } catch (\Exception $e) {
            log::debug($e->getMessage());
        }
    } else {
        return redirect('/');
    }
}

    public function qaRebuttalUpdate(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                $decodedPracticeName =  $request->subProjectName == '--' ? NULL : Helpers::encodeAndDecodeID($request->subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == NULL ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName)->sub_project_name;
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                $modelName = Str::studly($table_name);
                $originalModelClass = "App\\Models\\" . $modelName;
                $modelClass = "App\\Models\\" . $modelName.'Datas';
                $datasRecord = $modelClass::where('parent_id', $request->parentId)->orderBy('id','desc')->first();
                $record = $originalModelClass::where('id', $request->parentId)->first();
                if($request->qa_manager_rebuttal_status == 'agree') {
                    $chargeStatus  = 'QA_Completed';
                    $qaErrorCount = 1;
                    $arErrorCount = $datasRecord['coder_error_count'];
                } else {
                    $chargeStatus =  'QA_Completed';
                    $arErrorCount = $datasRecord['coder_error_count'] + 1;
                    $qaErrorCount = NULL;
                }
                $datasRecord->update( ['chart_status' => $chargeStatus,'coder_error_count' => $arErrorCount,'qa_error_count' => $qaErrorCount,'qa_manager_rebuttal_status' => $request->qa_manager_rebuttal_status,'qa_manager_rebuttal_comments' => $request->qa_manager_rebuttal_comments] );
                $record->update( ['chart_status' => $chargeStatus,'coder_error_count' => $arErrorCount,'qa_error_count' => $qaErrorCount,'qa_manager_rebuttal_status' => $request->qa_manager_rebuttal_status,'qa_manager_rebuttal_comments' => $request->qa_manager_rebuttal_comments] );

                return response()->json(['success' => true]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }

    public static function qaClassCatScope(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
                $data = qaClassCatScope::where('status_code_id', $request['status_code_id'])->where('sub_status_code_id', $request['sub_status_code_id'])->first();
                $html = '<div class="col-md-4">                                
                                <div class="form-group row">
                                    <label class="col-md-12">
                                       Classification
                                    </label>    
                                     <div class="col-md-10">
                                     <input type="hidden" name="qa_classification" value='.$data->id.'>
                                     <label style="background-color:#F3F3F3 !important">'.$data->qa_classification.'</label></div>                                
                                </div>
                        </div>
                        <div class="col-md-4">                                
                                <div class="form-group row">
                                    <label class="col-md-12">
                                       Category
                                    </label>    
                                     <div class="col-md-10">
                                         <input type="hidden" name="qa_category" value='.$data->id.'>
                                     <label style="background-color:#F3F3F3 !important">'.$data->qa_category.'</label></div>                                
                                </div>
                        </div>
                        <div class="col-md-4">                                
                                <div class="form-group row">
                                    <label class="col-md-12">
                                       Scope
                                    </label>    
                                     <div class="col-md-10">
                                         <input type="hidden" name="qa_scope" value='.$data->id.'>
                                     <label style="background-color:#F3F3F3 !important">'.$data->qa_scope.'</label></div>                                
                                </div>
                        </div>';
                return response()->json(["success" => true,"html"=>$html]);
            } catch (\Exception $e) {
                log::debug($e->getMessage());
            }
        } else {
            return redirect('/');
        }
    }
    public function qualityExport(Request $request) {
        if (Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null) {
            try {
             
                $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && 
                Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                $decodedProjectName = Helpers::encodeAndDecodeID($request->clientName, 'decode');
                $decodedPracticeName = $request->subProjectName == '--' ? '--' :Helpers::encodeAndDecodeID($request->subProjectName, 'decode');
                $decodedClientName = Helpers::projectName($decodedProjectName)->project_name;
                $decodedsubProjectName = $decodedPracticeName == '--' ? 'project' :Helpers::subProjectName($decodedProjectName,$decodedPracticeName);
                if($decodedsubProjectName != null &&  $decodedsubProjectName != 'project') {
                    $decodedsubProjectName= $decodedsubProjectName->sub_project_name;
                }
                $subProjectId = $request->subProjectName == '--' ?  NULL : $decodedPracticeName;
                $startDate = Carbon::now()->subDays(30)->startOfDay()->toDateTimeString();$endDate = Carbon::now()->endOfDay()->toDateTimeString();
                $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');   
                $modelName = Str::studly($table_name);
                $modelClass = "App\\Models\\" . $modelName;
                $query = $modelClass::query();
                if($request['_token'] != null) {
                    foreach ($request->except('_token', 'parent', 'child','clientName','subProjectName','recordStatusVal','page') as $key => $value) {
                      
                        if (is_array($value)) {
                            $value = implode('_el_', $value);  
                        }
                        if (is_numeric($value) || is_bool($value)) {
                            $query->where($key, $value);  // Exact match for numeric/boolean
                        } elseif ($this->isDate($value)) {  // Check if it's a date
                            $query->whereDate($key, '=', $value);  // Use `whereDate` for exact date match
                        } elseif (strpos($value, '$') !== false || strpos($value, '.') !== false) {
                            $query->where($key, $value); // For amounts (e.g., "$214.44"), adjust as needed
                        } else {
                            if($value != null) {
                              $query->where($key, 'like', '%' . $value . '%'); // Use 'like' for partial text matches
                            }
                        }
                    }
                }
               if ($loginEmpId && ($loginEmpId == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)) {
                    if($request->recordStatusVal == "unassigned") {
                        $exportResult = $query->whereIn('chart_status',[$request->chart_status,'QA_Inprocess'])->whereNull('qa_work_status')->whereNull('QA_emp_id')->get();
                        $exStatus = 'UnAssigned';
                    } else if($request->recordStatusVal == "Assigned") {
                        $exportResult = $query->whereIn('chart_status',[$request->chart_status,'QA_Inprocess'])->whereNotNull('QA_emp_id')->where('qa_work_status','Sampling')->get();
                        $exStatus = 'Assigned';
                    } else if($request->recordStatusVal == "Auto_Close") {
                        $exportResult = $query->where('qa_work_status','Auto_Close')->get();
                        $exStatus = 'Auto_Close';
                    }  else {
                        if($request->chart_status == "Rebuttal") {
                            $exportResult = $query->where('chart_status',$request->chart_status)->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->get();
                        } else {
                            $exportResult = $query->where('chart_status',$request->chart_status)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        }
                        // $exportResult = $query->where('chart_status',$request->chart_status)->where('ar_manager_rebuttal_status','agree')->whereBetween('updated_at',[$startDate,$endDate])->get();
                        $exStatus = str_replace('QA_', '', $request['chart_status']);
                    }
                } else if ($loginEmpId) {
                    if($request->recordStatusVal == "Assigned") {
                        $exportResult = $query->whereIn('chart_status',[$request->chart_status,'QA_Inprocess'])->where('QA_emp_id',$loginEmpId)->where('qa_work_status','Sampling')->get();
                        $exStatus = 'Assigned';
                    } else if($request->recordStatusVal == "Auto_Close") {
                        $exportResult = $query->where('qa_work_status','Auto_Close')->get();
                        $exStatus = 'Auto_Close';
                    } else {
                        if($request->chart_status == "Rebuttal") {
                            $exportResult = $query->where('chart_status',$request->chart_status)->where('ar_manager_rebuttal_status','agree')->where('QA_emp_id',$loginEmpId)
                            ->whereBetween('updated_at',[$startDate,$endDate])->get();
                        } else {
                            $exportResult = $query->where('chart_status',$request->chart_status)->where('QA_emp_id',$loginEmpId)->whereBetween('updated_at',[$startDate,$endDate])->get();
                        }
                        if(str_contains($request['chart_status'],'QA_')) {
                           $exStatus = str_replace('QA_', '', $request['chart_status']);
                        } else {
                            $exStatus = $request['chart_status'];
                        }
                    }
                }
                $fields = [];
                if (Schema::hasTable($table_name)) {
                    $column_names = DB::select("DESCRIBE $table_name");
                    $columns = array_column($column_names, 'Field');
                    $columnsToExclude = ['id','ce_hold_reason','qa_hold_reason','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                    'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','coder_cpt_trends','coder_icd_trends','coder_modifiers','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                    'updated_at', 'created_at', 'deleted_at'];
                    $fields = array_filter($columns, function ($column) use ($columnsToExclude) {
                        return !in_array($column, $columnsToExclude);
                    });
                    array_push($fields,'aging','aging_range');
                }
              
                return Excel::download(new ProductionExport($fields,$exportResult), 'Resolv_'.$exStatus.'_Export.xlsx');
                } catch (\Exception $e) {
                    log::debug($e->getMessage());
                }
            } else {
                return redirect('/');
            }       
    }
    protected function isDate($value) {
        return strtotime($value) ? true : false;
    }
}
