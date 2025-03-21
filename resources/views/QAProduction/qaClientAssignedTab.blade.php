@extends('layouts.app3')
@php
use Carbon\Carbon;
@endphp
@section('content')

    <div class="card card-custom custom-card">
        <div class="card-body p-0">
            @php
                $empDesignation =
                    Session::get('loginDetails') &&
                    Session::get('loginDetails')['userDetail']['user_hrdetails'] &&
                    Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation'] != null
                        ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']
                        : '';
                $loginEmpId =
                    Session::get('loginDetails') &&
                    Session::get('loginDetails')['userDetail'] &&
                    Session::get('loginDetails')['userDetail']['emp_id'] != null
                        ? Session::get('loginDetails')['userDetail']['emp_id']
                        : '';
            @endphp
            <div class="card-header border-0 px-4">
                <div class="row">
                    <div class="col-md-6">
                        <span class="project_header" style="margin-left: 4px !important;">Practice List</span>
                    </div>
                    <div class="col-md-6">
                        <div class="row" style="justify-content: flex-end;margin-right:1.4rem">

                            @if (
                                $loginEmpId == 'Admin' ||
                                    strpos($empDesignation, 'Manager') !== false ||
                                    strpos($empDesignation, 'VP') !== false ||
                                    strpos($empDesignation, 'Leader') !== false ||
                                    strpos($empDesignation, 'Team Lead') !== false ||
                                    strpos($empDesignation, 'CEO') !== false ||
                                    strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                                <div class="col-lg-3 mb-lg-0 mb-6" id="assign_div">

                                    <fieldset class="form-group mb-0 white-smoke-disabled">

                                        {!! Form::select('assignee_name', ['' => '--Assignee--'] + $assignedDropDown, null, [
                                            'class' => 'form-control kt_select2_assignee',
                                            'id' => 'assigneeDropdown',
                                            'style' => 'width: 100%;',
                                            'disabled',
                                        ]) !!}
                                    </fieldset>
                                </div>
                            @endif
                            &nbsp;&nbsp;
                            <div>
                                @if ($popUpHeader != null)
                                        @php
                                                $clientNameDetails = App\Http\Helper\Admin\Helpers::projectName(
                                                    $popUpHeader->project_id,
                                                );
                                                $sopDetails = App\Models\SopDoc::where('project_id',$popUpHeader->project_id)->where('sub_project_id',$popUpHeader->sub_project_id)->latest()->first('sop_path');
                                                // $pdfName =  preg_replace('/[^A-Za-z0-9]/', '_',$clientNameDetails->project_name);
                                        @endphp
                                        @else
                                        @php
                                            $sopDetails = '';
                                            // $pdfName = '';
                                        @endphp
                                    @endif
                                <a href= {{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }} target="_blank">
                                <button type="button" class="btn text-white mr-3" style="background-color:#139AB3">SOP</button>
                                </a>
                             </div>
                             <div class="d-flex align-items-center" id="export_div">
                                <a class="btn btn-primary-export text-white ml-2" href="javascript:void(0);" id='assign_export'  style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                                </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></a>
                            </div>
                            {{-- <div class="outside" href="javascript:void(0);"></div> --}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="wizard wizard-4 custom-wizard" id="kt_wizard_v4" data-wizard-state="step-first"
                data-wizard-clickable="true" style="margin-top:-2rem !important">
                <div class="wizard-nav">
                    <div class="wizard-steps">
                        <div class="wizard-step mb-0 one" data-wizard-type="step">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Assigned</h6>
                                        @include('CountVar.countRectangle', ['count' => $assignedCount])
                                    </div>

                                </div>
                            </div>
                        </div>
                        @if (
                            $loginEmpId == 'Admin' ||
                                strpos($empDesignation, 'Manager') !== false ||
                                strpos($empDesignation, 'VP') !== false ||
                                strpos($empDesignation, 'Leader') !== false ||
                                strpos($empDesignation, 'Team Lead') !== false ||
                                strpos($empDesignation, 'CEO') !== false ||
                                strpos($empDesignation, 'Vice') !== false|| strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                            <div class="wizard-step mb-0 five" data-wizard-type="step">
                                <div class="wizard-wrapper py-2">
                                    <div class="wizard-label p-2 mt-2">
                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                            <h6 style="margin-right: 5px;">UnAssigned</h6>
                                            @include('CountVar.countRectangle', [
                                                'count' => $unAssignedCount,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="wizard-step mb-0 two" data-wizard-type="step">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Pending</h6>
                                        @include('CountVar.countRectangle', ['count' => $pendingCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 three" data-wizard-type="step">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Hold</h6>
                                        @include('CountVar.countRectangle', ['count' => $holdCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 four" data-wizard-type="step">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Completed</h6>
                                        @include('CountVar.countRectangle', ['count' => $completedCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-step mb-0 seven" data-wizard-type="step">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Auto Close</h6>
                                        @include('CountVar.countRectangle', ['count' => $autoCloseCount])
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if (
                            $loginEmpId == 'Admin' ||
                            strpos($empDesignation, 'Manager') !== false ||
                            strpos($empDesignation, 'VP') !== false ||
                            strpos($empDesignation, 'Leader') !== false ||
                            strpos($empDesignation, 'Team Lead') !== false ||
                            strpos($empDesignation, 'CEO') !== false ||
                            strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                            <div class="wizard-step mb-0 eight" data-wizard-type="step">
                                <div class="wizard-wrapper py-2">
                                    <div class="wizard-label p-2 mt-2">
                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                            <h6 style="margin-right: 5px;">Rebuttal</h6>
                                            @include('CountVar.countRectangle', [
                                                'count' => $rebuttalCount,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                      
                        {{-- <div class="wizard-step mb-0 five" data-wizard-type="step">
                            <div class="wizard-wrapper py-2">
                                <div class="wizard-label p-2 mt-2">
                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                        <h6 style="margin-right: 5px;">Rework</h6>
                                        @include('CountVar.countRectangle', ['count' => $reworkCount])
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        {{-- @if (
                            $loginEmpId == 'Admin' ||
                                strpos($empDesignation, 'Manager') !== false ||
                                strpos($empDesignation, 'VP') !== false ||
                                strpos($empDesignation, 'Leader') !== false ||
                                strpos($empDesignation, 'Team Lead') !== false ||
                                strpos($empDesignation, 'CEO') !== false ||
                                strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                            <div class="wizard-step mb-0 six" data-wizard-type="step">
                                <div class="wizard-wrapper py-2">
                                    <div class="wizard-label p-2 mt-2">
                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                            <h6 style="margin-right: 5px;">Duplicate</h6>
                                            @include('CountVar.countRectangle', [
                                                'count' => $duplicateCount,
                                            ])
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif --}}
                    </div>
                </div>
            </div>
            <div class="card card-custom custom-top-border">
                <div><span type="button" id="filterExpandButton" class="float-right mr-8 mt-5">
                    <i class="ki ki-arrow-down icon-nm"></i></span></div>
                   
                    <div class="card-body py-0 px-7" id="filter_section" style="display:none">
                       
                        @if (count($projectColSearchFields) > 0)
                            @php $count = 0; @endphp
                            @foreach ($projectColSearchFields as $key => $data)
                                @php
                                $decodedClientName = App\Http\Helper\Admin\Helpers::projectName($data->project_id)->project_name;
                                $decodedsubProjectName = $data->sub_project_i == NULL ? 'project' :App\Http\Helper\Admin\Helpers::subProjectName($data->project_id,$data->sub_project_id);
                                    $table_name= Str::slug((Str::lower($decodedClientName).'_'.Str::lower($decodedsubProjectName)),'_');
                                    $modelName = Str::studly($table_name);
                                    $modelClass = "App\\Models\\" .  $modelName;
                                    $labelName = ucwords(str_replace(['_else_', '_'], ['/', ' '], $data->column_name));
                                        $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data->column_name));
                                    $inputType = $data->column_type; $options = null;
                                if($inputType == 'select') {
                                    $options = $modelClass::select($columnName)
                                                ->distinct()
                                                ->get()
                                                ->pluck($columnName)
                                                ->toArray();
                                                $associativeOptions = [];
                                                if ($options !== null) {
                                                    foreach ($options as $option) {
                                                        $option=trim($option);
                                                        $associativeOptions[$option] = $option;
                                                    }
                                                }
                                }
                             $clientName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data->project_id, 'encode');
                             $subProjectName = $data->sub_project_id != null ? App\Http\Helper\Admin\Helpers::encodeAndDecodeID($data->sub_project_id, 'encode') : '--';
                                @endphp
                                 {!! Form::open([
                                    'url' =>
                                    url('qa_production/qa_projects_assigned/' . $clientName . '/' . $subProjectName) .
                                                    '?parent=' .
                                                    request()->parent .
                                                    '&child=' .
                                                    request()->child,
                                    'class' => 'form',
                                    'id' => 'formSearch',
                                    'enctype' => 'multipart/form-data',
                                ]) !!}
                                @csrf
                               
                            @if ($count % 4 == 0)
                                    <div class="row mr-0 ml-0 mt-5">
                                        @endif
                                    <div class="col-md-3">
                                        <div class="form-group row row_mar_bm">
                                            <label
                                                class="col-md-12">
                                              @if(str_contains($labelName, 'Coder '))
                                                {{ str_replace('Coder ', 'AR ', $labelName) }}
                                            @elseif ($data->column_name == 'CE_emp_id')
                                                AR Emp Id
                                              @else
                                                {{ $labelName }}
                                              @endif
                                            </label>
                                            <div class="col-md-10">
                                                @if ($options == null)
                                                    @if ($inputType != 'date_range')
                                                        {!! Form::$inputType($columnName,isset($searchData) && !empty($searchData) && isset($searchData[$columnName]) && $searchData[$columnName] ? $searchData[$columnName] : null, [
                                                            'class' => 'form-control white-smoke pop-non-edt-val',
                                                            'autocomplete' => 'none',
                                                            'style' => 'cursor:pointer',
                                                            'rows' => 3
                                                        ]) !!}
                                                    @else
                                                        {!! Form::text($columnName, null, [
                                                            'class' => 'form-control date_range white-smoke pop-non-edt-val',
                                                            'autocomplete' => 'none',
                                                            'style' => 'cursor:pointer'        
                                                        ]) !!}
                                                    @endif
                                                @else
                                                    @if ($inputType == 'select')
                                                        {!! Form::$inputType($columnName, ['' => '-- Select --'] + $associativeOptions, isset($searchData) && !empty($searchData) && isset($searchData[$columnName]) && $searchData[$columnName]  ? $searchData[$columnName] : null, [
                                                            'class' => 'form-control white-smoke pop-non-edt-val select2',
                                                            'autocomplete' => 'none'                                               
                                                        ]) !!}
                                                @endif
                                                @endif
                                            </div>
                                        
                                        
                                        </div>
                                    </div>
                                    @php $count++; @endphp
                                    @if ($count % 4 == 0 || $loop->last)
                                    </div>
                                @endif
                            
                            @endforeach
                            <div class="form-footer" style="justify-content: center !important">                                      
                                <button type="submit" class="btn  btn-white-black font-weight-bold"
                                    id="filter_search">Search</button> &nbsp;&nbsp; <button class="btn btn-light-danger" id="filter_clear" tabindex="10" type="button">
                                        <span>
                                            <span>Clear</span>
                                        </span>
                                    </button>                        
                            </div>
                        @endif
                    </div>
              
                    {!! Form::close() !!}
                <div class="card-body py-0 px-7">
                    <input type="hidden" value={{ $clientName }} id="clientName">
                    <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                    <div class="table-responsive pt-5">
                        <table class="table table-separate table-head-custom no-footer dtr-column clients_list_filter"
                            id="client_assigned_list" data-order='[[ 0, "desc" ]]'>
                            <thead>
                                @if (!empty($columnsHeader))
                                    <tr>
                                        @if (
                                            $loginEmpId == 'Admin' ||
                                                strpos($empDesignation, 'Manager') !== false ||
                                                strpos($empDesignation, 'VP') !== false ||
                                                strpos($empDesignation, 'Leader') !== false ||
                                                strpos($empDesignation, 'Team Lead') !== false ||
                                                strpos($empDesignation, 'CEO') !== false ||
                                                strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                                            <th class='notexport'><input type="checkbox" id="ckbCheckAll"
                                                    class="cursor_hand">
                                            </th>
                                        @endif
                                        <th class='notexport' style="color:white !important">Action</th>
                                        @foreach ($columnsHeader as $columnName => $columnValue)
                                            @if ($columnValue != 'id')
                                                <th><input type="hidden" value={{ $columnValue }}>
                                                   @if ($columnValue == 'chart_status')
                                                    Charge Status
                                                    @elseif ($columnValue == 'CE_emp_id')
                                                    AR Emp Id
                                                    @elseif ($columnValue == 'coder_work_date')
                                                    AR Work Date
                                                    @elseif ($columnValue == 'coder_rework_status')
                                                    AR Rework Status
                                                    @else
                                                    {{ ucwords(str_replace(['_else_', '_'], ['/', ' '], $columnValue)) }}
                                                    @endif
                                                    </th>
                                            @else
                                                <th style="display:none" class='notexport'><input type="hidden"
                                                        value={{ $columnValue }}>
                                                        @if ($columnValue == 'chart_status')
                                                        Charge Status
                                                        @elseif ($columnValue == 'CE_emp_id')
                                                        AR Emp Id
                                                        @elseif ($columnValue == 'coder_work_date')
                                                        AR Work Date
                                                        @elseif ($columnValue == 'coder_rework_status')
                                                        AR Rework Status
                                                       @else
                                                         {{ ucwords(str_replace(['_else_', '_'], ['/', ' '], $columnValue)) }}
                                                       @endif
                                                </th>
                                            @endif
                                        @endforeach
                                    </tr>
                                @endif

                            </thead>
                            <tbody>
                                @if (isset($assignedProjectDetails))
                                    @foreach ($assignedProjectDetails as $data)
                                        @php
                                        $arrayAttrributes = $data->getAttributes();
                                        $arrayAttrributes['aging']= null; 
                                        $arrayAttrributes['aging_range']= null;                                       
                                        @endphp
                                        <tr>
                                            @if (
                                                $loginEmpId == 'Admin' ||
                                                    strpos($empDesignation, 'Manager') !== false ||
                                                    strpos($empDesignation, 'VP') !== false ||
                                                    strpos($empDesignation, 'Leader') !== false ||
                                                    strpos($empDesignation, 'Team Lead') !== false ||
                                                    strpos($empDesignation, 'CEO') !== false ||
                                                    strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                                                <td><input type="checkbox" class="checkBoxClass cursor_hand" name='check[]'
                                                        value="{{ $data->id }}">
                                                </td>
                                            @endif
                                            <td>
                                                @if (
                                                    ($loginEmpId !== 'Admin' ||
                                                        strpos($empDesignation, 'Manager') !== true ||
                                                        strpos($empDesignation, 'VP') !== true ||
                                                        strpos($empDesignation, 'Leader') !== true ||
                                                        strpos($empDesignation, 'Team Lead') !== true ||
                                                        strpos($empDesignation, 'CEO') !== true ||
                                                        strpos($empDesignation, 'Vice') !== true || strpos($empDesignation, 'Group Coordinator - AR') !== true || strpos($empDesignation, 'Subject Matter Expert') !== true) &&
                                                        $loginEmpId != $data->QA_emp_id)
                                                @else
                                                
                                                    @if (empty($existingCallerChartsWorkLogs) && !in_array('QA_Inprocess', $assignedProjectDetailsStatus))
                                                    {{-- @if (empty($existingCallerChartsWorkLogs)) --}}
                                                         <button class="task-start clickable-row start" title="Start"><i
                                                                class="fa fa-play-circle icon-circle1 mt-0"
                                                                aria-hidden="true" style="color:#ffffff"></i></button>
                                                    @elseif($data->chart_status == 'QA_Inprocess')
                                                         <button class="task-start clickable-row start" title="Start"><i
                                                                class="fa fa-play-circle icon-circle1 mt-0"
                                                                aria-hidden="true" style="color:#ffffff"></i></button>
                                                    @elseif(in_array($data->id, $existingCallerChartsWorkLogs))
                                                         <button class="task-start clickable-row start" title="Start"><i
                                                                class="fa fa-play-circle icon-circle1 mt-0"
                                                                aria-hidden="true" style="color:#ffffff"></i></button>
                                                    @endif
                                                @endif
                                                <button class="task-start clickable-view" title="View"><i
                                                        class="fa far fa-eye text-eye icon-circle1 mt-0"></i></button>
                                            </td>
                                            @foreach ($arrayAttrributes as $columnName => $columnValue)
                                                @php
                                                    $columnsToExclude = [

                                                        'ce_hold_reason','qa_hold_reason','qa_work_status','QA_rework_comments','QA_required_sampling','QA_rework_comments','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                                                        'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                                                        'created_at',
                                                        'updated_at',
                                                        'deleted_at',
                                                    ];
                                                    $text = 'UnAssigned';
                                                    $backgroundColor = $text == 'UnAssigned' ? 'red' : 'transparent';
                                                    $textColor = $text == 'UnAssigned' ? 'white' : 'black';
                                                    if(isset($arrayAttrributes['dos'])) {          
                                                        $dosDate = Carbon::parse($arrayAttrributes['dos']);
                                                        $currentDate = Carbon::now();
                                                        $agingCount = $dosDate->diffInDays($currentDate);
                                                        if ($agingCount <= 30) {
                                                            $agingRange = '0-30';
                                                        } elseif ($agingCount <= 60) {
                                                            $agingRange ='31-60';
                                                        } elseif ($agingCount <= 90) {
                                                            $agingRange = '61-90';
                                                        } elseif ($agingCount <= 120) {
                                                            $agingRange = '91-120';
                                                        } elseif ($agingCount <= 180) {
                                                            $agingRange = '121-180';
                                                        } elseif ($agingCount <= 365) {
                                                            $agingRange = '181-365';
                                                        } else {
                                                            $agingRange = '365+';
                                                        }
                                                    } else {
                                                        $agingCount = '--';
                                                        $agingRange = '--';
                                                    }
                                                @endphp
                                                @if (!in_array($columnName, $columnsToExclude))
                                                    @if ($columnName != 'id')
                                                        <td
                                                            style="max-width: 300px;
                                                                        white-space: normal;">
                                                            @if ($columnName == 'chart_status' && is_null($data->QA_emp_id)  && is_null($data->qa_work_status))
                                                                <b>
                                                                    <p style="color: red;">UnAssigned</p>
                                                                </b>
                                                            @else
                                                                {{-- @if (str_contains($columnValue, '-') && strtotime($columnValue)) --}}
                                                                @if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $columnValue))
                                                                    {{ date('m/d/Y', strtotime($columnValue)) }}
                                                                @elseif ($columnName == 'chart_status' && (str_contains($columnValue, 'CE_') || str_contains($columnValue, 'Auto_'))&& $data->qa_work_status !== null)
                                                                    {{-- {{ str_replace('CE_', '', $columnValue) }} --}}
                                                                    {{ str_replace('_', ' ', $data->qa_work_status) }}
                                                                @elseif ($columnName == 'chart_status' && str_contains($columnValue, 'QA_'))
                                                                {{-- {{ str_replace('CE_', '', $columnValue) }} --}}
                                                                In process
                                                                @elseif ($columnName == 'QA_status_code')
                                                                @php
                                                                    if($columnValue != null) {
                                                                       $statusCode = App\Http\Helper\Admin\Helpers::qaStatusById($columnValue);
                                                                    } else {
                                                                        $statusCode = '';
                                                                    }
                                                                 @endphp
                                                                    {{ $columnValue == null ? $columnValue : $statusCode['status_code'] }}
                                                                @elseif ($columnName == 'QA_sub_status_code')
                                                                    @php
                                                                     if($columnValue != null) {
                                                                        $subStatusCode = App\Http\Helper\Admin\Helpers::qaSubStatusById($columnValue);
                                                                    } else {
                                                                        $subStatusCode = '';
                                                                    }

                                                                    @endphp
                                                                    {{ $columnValue == null ? $columnValue :  $subStatusCode['sub_status_code'] }}
                                                                    @elseif ($columnName == 'qa_classification')
                                                                        @php
                                                                            if($columnValue != null) {
                                                                            $qaClassification = App\Http\Helper\Admin\Helpers::qaClassificationById($columnValue);
                                                                        } else {
                                                                            $qaClassification = '';
                                                                        }
                                                                        @endphp
                                                                        {{ $columnValue == null ? $columnValue :  $qaClassification['qa_classification'] }} 
                                                                    @elseif ($columnName == 'qa_category')
                                                                        @php
                                                                            if($columnValue != null) {
                                                                            $qaCategory = App\Http\Helper\Admin\Helpers::qaCategoryById($columnValue);
                                                                        } else {
                                                                            $qaCategory = '';
                                                                        }
                                                                        @endphp
                                                                        {{ $columnValue == null ? $columnValue :  $qaCategory['qa_category'] }}
                                                                    @elseif ($columnName == 'qa_scope')
                                                                        @php
                                                                            if($columnValue != null) {
                                                                            $qaScope = App\Http\Helper\Admin\Helpers::qaScopeById($columnValue);
                                                                        } else {
                                                                            $qaScope = '';
                                                                        }
                                                                        @endphp
                                                                        {{ $columnValue == null ? $columnValue :  $qaScope['qa_scope'] }}
                                                                    @elseif ($columnName == 'aging')                                                                                  
                                                                      {{ $agingCount }}
                                                                    @elseif ($columnName == 'aging_range')
                                                                        {{ $agingRange }}
                                                                    @else
                                                                        {{ $columnValue }}
                                                                    @endif
                                                            @endif
                                                        </td>
                                                    @else
                                                        <td style="display:none;max-width: 300px;
                                                                        white-space: normal;"
                                                            id="table_id">
                                                            {{-- @if (str_contains($columnValue, '-') && strtotime($columnValue)) --}}
                                                            @if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $columnValue))
                                                                {{ date('m/d/Y', strtotime($columnValue)) }}
                                                            @elseif ($columnName == 'aging')                                                                                  
                                                                {{ $agingCount }}
                                                            @elseif ($columnName == 'aging_range')
                                                                {{ $agingRange }}
                                                            @else
                                                                {{ $columnValue }}
                                                            @endif
                                                        </td>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="ml-3">
                            Showing {{ $assignedProjectDetails->firstItem() != null ? $assignedProjectDetails->firstItem() : 0 }} to {{ $assignedProjectDetails->lastItem() != null ? $assignedProjectDetails->lastItem() : 0 }} of {{ $assignedProjectDetails->total() }} entries
                        </div>
                         <div>
                            {{ $assignedProjectDetails->appends(request()->all())->links() }}
                        </div>
                    </div>
                </div>

                <div class="modal fade modal-first" id="myModal_status" tabindex="-1" role="dialog"
                    aria-labelledby="myModalLabel" data-backdrop="static" aria-hidden="true">
                    @if ($popUpHeader != null)
                        <div class="modal-dialog">
                            @php
                                $clientName = App\Http\Helper\Admin\Helpers::projectName($popUpHeader->project_id);
                                if ($popUpHeader->sub_project_id != null) {
                                    $practiceName = App\Http\Helper\Admin\Helpers::subProjectName(
                                        $popUpHeader->project_id,
                                        $popUpHeader->sub_project_id,
                                    );
                                    $subProjectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                        $popUpHeader->sub_project_id,
                                        'encode',
                                    );
                                } else {
                                    $practiceName = '';
                                    $subProjectName = '--';
                                }
                                $projectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                                    $popUpHeader->project_id,
                                    'encode',
                                );
                            @endphp

                            <div class="modal-content" style="margin-top: 7rem">
                                <div class="modal-header" style="background-color: #139AB3;height: 84px">
                                    <div class="row" style="height: auto;width:100%">
                                        <div class="col-md-4">
                                            <div class="align-items-center" style="display: -webkit-box !important;">
                                                <div class="rounded-circle bg-white text-black mr-2"
                                                    style="width: 50px; height: 50px; display: flex; justify-content: center; align-items: center;font-weight;bold">
                                                    <span>{{ strtoupper(substr($clientName->project_name, 0, 1)) }}</span>
                                                </div>&nbsp;&nbsp;
                                                <div>
                                                    <h6 class="modal-title mb-0" id="myModalLabel"
                                                        style="color: #ffffff;">
                                                        {{ ucfirst($clientName->aims_project_name) }}
                                                    </h6>
                                                    @if ($practiceName != '')
                                                        <h6 style="color: #ffffff;font-size:1rem;">
                                                            {{ ucfirst($practiceName->sub_project_name) }}</h6>
                                                    @endif
                                                </div>&nbsp;&nbsp;
                                                <div class="bg-white rounded-pill px-2 text-black"
                                                    style="margin-bottom: 2rem;margin-left:2.2px;font-size:10px;font-weight:500;background-color:#E9F3FF;color:#139AB3;">
                                                    <span id="title_status"></span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="col-md-8  justify-content-end"
                                            style="display: -webkit-box !important;">
                                            <button type="button" class="btn btn-black-white mr-3 sop_click"
                                                id="sop_click" style="padding: 0.35rem 1rem;">SOP</button>
                                        </div> --}}
                                    </div>
                                </div>
                                {!! Form::open([
                                    'url' =>
                                        url('qa_production/qa_project_store/' . $projectName . '/' . $subProjectName) .
                                        '?parent=' .
                                        request()->parent .
                                        '&child=' .
                                        request()->child,
                                    'class' => 'form',
                                    'id' => 'formConfiguration',
                                    'enctype' => 'multipart/form-data',
                                ]) !!}
                                @csrf
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-3" data-scroll="true" data-height="400">
                                            <h6 class="title-h6">Basic Information</h6>&nbsp;&nbsp;
                                            <input type="hidden" name="idValue">
                                            <input type="hidden" name="parentId">
                                            <input type="hidden" name="record_old_status">
                                            @if (count($popupNonEditableFields) > 0)
                                                @php $count = 0; @endphp
                                                @foreach ($popupNonEditableFields as $data)
                                                    @php
                                                        $columnName = Str::lower(
                                                            str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                                        );
                                                    @endphp

                                                    <label class="col-md-12">{{ $data->label_name }}
                                                    </label>
                                                    <input type="hidden" name="{{ $columnName }}">

                                                    <label class="col-md-12 pop-non-edt-val" id={{ $columnName }}>
                                                    </label>
                                                    <hr style="margin-left:1rem">
                                                @endforeach
                                            @endif
                                        </div>
                                        <div class="col-md-9" style="border-left: 1px solid #ccc;" data-scroll="true"
                                            data-height="400">
                                                <h6 class="title-h6">AR
                                                    {{-- <span type = "button" id="expandButton" class="float-right">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                                            fill="currentColor" class="bi bi-arrow-counterclockwise"
                                                            viewBox="0 0 16 16">
                                                            <path fill-rule="evenodd"
                                                                d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z" />
                                                            <path
                                                                d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466" />
                                                        </svg></span> --}}

                                                </h6>&nbsp;&nbsp;
                                                @if (count($popupEditableFields) > 0)
                                                        @php $count = 0; @endphp
                                                        @foreach ($popupEditableFields as $key => $data)
                                                            @php
                                                                $labelName = $data->label_name;
                                                                $columnName = Str::lower(
                                                                    str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                                                );
                                                                $inputType = $data->input_type;
                                                                $options =
                                                                    $data->options_name != null
                                                                        ? explode(',', $data->options_name)
                                                                        : null;
                                                                $associativeOptions = [];
                                                                if ($options !== null) {
                                                                    foreach ($options as $option) {
                                                                        $associativeOptions[$option] = $option;
                                                                    }
                                                                }
                                                            @endphp
                                                            @if ($count % 2 == 0)
                                                                <div class="row" id={{ $columnName }}>
                                                            @endif
                                                                <div class="col-md-6 dynamic-field">
                                                                    <div class="form-group row row_mar_bm">
                                                                        <label
                                                                            class="col-md-12 {{ $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? 'required' : '' }}">
                                                                            {{ $labelName }}
                                                                        </label>
                                                                        <div class="col-md-10">
                                                                            @if ($options == null)
                                                                                @if ($inputType != 'date_range')
                                                                                    {!! Form::$inputType($columnName . '[]', null, [
                                                                                        'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                        'autocomplete' => 'none',
                                                                                        'style' => 'cursor:pointer',
                                                                                        'rows' => 3,
                                                                                        'id' => $columnName,
                                                                                        $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                        ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                                    ]) !!}
                                                                                    {{-- @if($columnName == "am_cpt" || $columnName == "am_icd") 
                                                                                     {!! Form::$inputType($columnName.'_hidden' . '[]', null, [
                                                                                        'class' => 'form-control ' . $columnName.'_hidden' . ' white-smoke pop-non-edt-val',
                                                                                        'autocomplete' => 'none',
                                                                                        'style' => 'cursor:pointer; display:none;',
                                                                                        'rows' => 3,
                                                                                        'id' => $columnName.'_hidden',
                                                                                        $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                        ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                                    ]) !!}
                                                                                    @endif --}}
                                                                                @else
                                                                                    {!! Form::text($columnName . '[]', null, [
                                                                                        'class' => 'form-control date_range daterange_' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                        'autocomplete' => 'none',
                                                                                        'style' => 'cursor:pointer',
                                                                                        'id' => 'date_range',
                                                                                        $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                        ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'readonly'
                                                                                    ]) !!}
                                                                                @endif
                                                                            @else
                                                                                @if ($inputType == 'select')
                                                                                    {!! Form::$inputType($columnName . '[]', ['' => '-- Select --'] + $associativeOptions, null, [
                                                                                        'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val',
                                                                                        'autocomplete' => 'none',
                                                                                        'style' => 'cursor:pointer;' . (($data->input_type_editable == 2 || $data->input_type_editable == 3) ? '' : 'pointer-events: none;'),
                                                                                        'id' => $columnName,
                                                                                        $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                    ]) !!}
                                                                                @elseif ($inputType == 'checkbox')
                                                                                    <p id="check_p1"
                                                                                        style="display:none;color:red; margin-left: 3px;">
                                                                                        Checkbox
                                                                                        is not checked</p>
                                                                                    <div class="form-group row">
                                                                                        @for ($i = 0; $i < count($options); $i++)
                                                                                            <div class="col-md-6">
                                                                                                <div class="checkbox-inline mt-2">
                                                                                                    <label
                                                                                                        class="checkbox pop-non-edt-val"
                                                                                                        style="word-break: break-all;">
                                                                                                        {!! Form::$inputType($columnName . '[]', $options[$i], false, [
                                                                                                            'class' => $columnName,
                                                                                                            'id' => $columnName,
                                                                                                            $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                                            'onclick' => $data->input_type_editable != 2 && $data->input_type_editable != 3 ? 'return false;' : '',
                                                                                                        ]) !!}{{ $options[$i] }}
                                                                                                        <span></span>
                                                                                                    </label>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endfor
                                                                                    </div>
                                                                                @elseif ($inputType == 'radio')
                                                                                    <p id="radio_p1"
                                                                                        style="display: none; color: red; margin-left: 3px;">
                                                                                        Radio
                                                                                        is not selected</p>
                                                                                    <div class="form-group row">
                                                                                        @for ($i = 0; $i < count($options); $i++)
                                                                                            <div class="col-md-6">
                                                                                                <div class="radio-inline mt-2">
                                                                                                    <label
                                                                                                        class="radio pop-non-edt-val"
                                                                                                        style="word-break: break-all;">
                                                                                                        {!! Form::$inputType($columnName, $options[$i], false, [
                                                                                                            'class' => $columnName,
                                                                                                            'id' => $columnName,
                                                                                                            $data->field_type_2 == 'mandatory' && ($data->input_type_editable == 2 || $data->input_type_editable == 3) ? 'required' : '',
                                                                                                            'disabled' => $data->input_type_editable != 2 && $data->input_type_editable != 3
                                                                                                        ]) !!}{{ $options[$i] }}
                                                                                                        <span></span>
                                                                                                    </label>
                                                                                                </div>

                                                                                            </div>
                                                                                        @endfor
                                                                                    </div>
                                                                                @endif
                                                                            @endif

                                                                        </div>
                                                                        <div class="col-md-1 col-form-label pt-0 pb-4"
                                                                            style="margin-left: -1.3rem;">
                                                                            <input type="hidden"
                                                                                value="{{ $associativeOptions != null ? json_encode($associativeOptions) : null }}"
                                                                                class="add_options">

                                                                            @if ($data->field_type_1 == 'multiple')
                                                                                <i class="fa fa-plus add_more"
                                                                                    id="add_more_{{ $columnName }}"
                                                                                    style="{{ $data->field_type_1 == 'multiple' ? 'visibility: visible;' : 'visibility: hidden;' }}"></i>
                                                                                <input type="hidden"
                                                                                    value="{{ $data->field_type_1 == 'multiple' ? $labelName : '' }}"
                                                                                    class="add_labelName">
                                                                                <input type="hidden"
                                                                                    value="{{ $data->field_type_1 == 'multiple' ? $columnName : '' }}"
                                                                                    class="add_columnName">
                                                                                <input type="hidden"
                                                                                    value="{{ $data->field_type_1 == 'multiple' ? $inputType : '' }}"
                                                                                    class="add_inputtype">
                                                                                <input type="hidden"
                                                                                    value="{{ $data->field_type_1 == 'multiple' ? ($data->field_type_2 == 'mandatory' ? 'required' : '') : '' }}"
                                                                                    class="add_mandatory">
                                                                            @endif
                                                                        </div>
                                                                        <div></div>
                                                                    </div>
                                                                </div>
                                                            @php $count++; @endphp
                                                            @if ($count % 2 == 0 || $loop->last)
                                                            </div>
                                                        @endif
                                                        @endforeach
                                                @endif
                                                {{-- <div class="row mt-4 trends_div">
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-md-12">
                                                                Coder Trends
                                                            </label>
                                                            <div class="col-md-11">
                                                                {!!Form::textarea('annex_coder_trends',  null, ['class' => 'text-black form-control white-smoke annex_coder_trends','rows' => 6,'readonly']) !!}
    
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group row">
                                                            <label class="col-md-12">
                                                                QA Trends
                                                            </label>
                                                            <div class="col-md-11">
                                                                {!!Form::textarea('annex_qa_trends',  null, ['class' => 'text-black form-control white-smoke annex_qa_trends','rows' => 6,'readonly']) !!}
    
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div> --}}
                                                @php
                                                    if($popUpHeader->sub_project_id != null && $popUpHeader->sub_project_id != "") {
                                                        $statusActionShow = App\Models\projectInputSetting::where('sub_project_id',$popUpHeader->sub_project_id)->first();                                                                                                                              
                                                    } else {
                                                        $statusActionShow = null;
                                                    }
                                                @endphp
                                              @if($statusActionShow != null)
                                                <div class="row mt-4">
                                                    @if($statusActionShow->status_input == 1)
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <label class="col-md-12">
                                                                    Status Code
                                                                </label>
                                                                @php $arStatusList = $popUpHeader->sub_project_id != null && $popUpHeader->sub_project_id != "" ? App\Http\Helper\Admin\Helpers::arStatusListBySubPrjId( $popUpHeader->sub_project_id) : []; @endphp
            
                                                                <div class="col-md-10">
                                                                    <input type="hidden" id="ar_status_val">
                                                                    {!! Form::Select(
                                                                        'ar_status_code',
                                                                        $arStatusList,
                                                                        null,
                                                                        [
                                                                            'class' => 'form-control white-smoke  kt_select2_qa_status pop-non-edt-val ',
                                                                            'autocomplete' => 'none',
                                                                            'id' => 'ar_status_code',
                                                                            'style' => 'cursor:pointer',
                                                                            'disabled'
                                                                        ],
                                                                    ) !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif    
                                                    @if($statusActionShow->action_input == 1)
                                                        <div class="col-md-6">
                                                            <div class="form-group row">
                                                                <label class="col-md-12">
                                                                    Action Code
                                                                </label>
                                                                @php $arActionList = []; @endphp
                                                                <div class="col-md-10">
                                                                    {!! Form::Select(
                                                                        'ar_action_code',
                                                                        $arActionList,
                                                                        null,
                                                                        [
                                                                            'class' => 'form-control white-smoke  kt_select2_ar_action_code pop-non-edt-val ',
                                                                            'autocomplete' => 'none',
                                                                            'id' => 'ar_action_code',
                                                                            'style' => 'cursor:pointer',
                                                                            'disabled'
                                                                        ],
                                                                    ) !!}
            
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                              @endif
                                                <hr>
                                                <h6 class="title-h6">QA</h6>&nbsp;&nbsp;
                                            @if (count($popupQAEditableFields) > 0)
                                                @php $count = 0; @endphp
                                                @foreach ($popupQAEditableFields as $key => $data)
                                                        @php
                                                            $labelName = $data->label_name;
                                                            $columnName = Str::lower(str_replace([' ', '/'], ['_', '_else_'], $data->label_name));
                                                            $inputType = $data->input_type;
                                                            $options = $data->options_name != null ? explode(',', $data->options_name) : null;
                                                            $associativeOptions = [];
                                                            if ($options !== null) {
                                                                foreach ($options as $option) {
                                                                    $associativeOptions[$option] = $option;
                                                                }
                                                            }
                                                        @endphp
                                                        @if ($count % 2 == 0)
                                                            <div class="row" id={{ $columnName }}>
                                                        @endif
                                                        <div class="col-md-6 dynamic-field">
                                                            <div class="form-group row row_mar_bm">
                                                                <label class="col-md-12 {{ $data->field_type_2 == 'mandatory' ? 'required' : '' }}">
                                                                    {{ $labelName }}
                                                                </label>
                                                                <div class="col-md-10">
                                                                    @if ($options == null)
                                                                        @if ($inputType != 'date_range')
                                                                            {!! Form::$inputType($columnName . '[]', null, [
                                                                                'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val exclude',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer',
                                                                                'rows' => 3,
                                                                                'id' => $columnName,
                                                                                $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                            ]) !!}
                                                                        @else
                                                                            {!! Form::text($columnName . '[]', null, [
                                                                                'class' => 'form-control date_range daterange_' . $columnName . ' white-smoke pop-non-edt-val exclude',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer',
                                                                                'id' => 'date_range',
                                                                                $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                            ]) !!}
                                                                        @endif
                                                                    @else
                                                                        @if ($inputType == 'select')
                                                                            {!! Form::$inputType($columnName . '[]', ['' => '-- Select --'] + $associativeOptions, null, [
                                                                                'class' => 'form-control ' . $columnName . ' white-smoke pop-non-edt-val exclude',
                                                                                'autocomplete' => 'none',
                                                                                'style' => 'cursor:pointer',
                                                                                'id' => $columnName,
                                                                                $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                            ]) !!}
                                                                        @elseif ($inputType == 'checkbox')
                                                                            <p id="check_p1" style="display:none;color:red; margin-left: 3px;">
                                                                                Checkbox
                                                                                is not checked</p>
                                                                            <div class="form-group row">
                                                                                @for ($i = 0; $i < count($options); $i++)
                                                                                    <div class="col-md-6">
                                                                                        <div class="checkbox-inline mt-2">
                                                                                            <label class="checkbox pop-non-edt-val"
                                                                                                style="word-break: break-all;">
                                                                                                {!! Form::$inputType($columnName . '[]', $options[$i], false, [
                                                                                                    'class' => 'exclude '.$columnName,
                                                                                                    'id' => $columnName,
                                                                                                    $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                                ]) !!}{{ $options[$i] }}
                                                                                                <span></span>
                                                                                            </label>
                                                                                        </div>
                                                                                    </div>
                                                                                @endfor
                                                                            </div>
                                                                        @elseif ($inputType == 'radio')
                                                                            <p id="radio_p1" style="display: none; color: red; margin-left: 3px;">
                                                                                Radio
                                                                                is not selcted</p>
                                                                            <div class="form-group row">
                                                                                @for ($i = 0; $i < count($options); $i++)
                                                                                    <div class="col-md-6">
                                                                                        <div class="radio-inline mt-2">
                                                                                            <label class="radio pop-non-edt-val"
                                                                                                style="word-break: break-all;">
                                                                                                {!! Form::$inputType($columnName, $options[$i], false, [
                                                                                                    'class' => $columnName.' exclude',
                                                                                                    'id' => $columnName,
                                                                                                    $data->field_type_2 == 'mandatory' ? 'required' : '',
                                                                                                ]) !!}{{ $options[$i] }}
                                                                                                <span></span>
                                                                                            </label>
                                                                                        </div>

                                                                                    </div>
                                                                                @endfor
                                                                            </div>
                                                                        @endif
                                                                    @endif

                                                                </div>
                                                                <div class="col-md-1 col-form-label pt-0 pb-4" style="margin-left: -1.3rem;">
                                                                    <input type="hidden"
                                                                        value="{{ $associativeOptions != null ? json_encode($associativeOptions) : null }}"
                                                                        class="add_options">

                                                                    @if ($data->field_type_1 == 'multiple')
                                                                        <i class="fa fa-plus add_more exclude" id="add_more_{{ $columnName }}"
                                                                            style="{{ $data->field_type_1 == 'multiple' ? 'visibility: visible;' : 'visibility: hidden;' }}"></i>
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? $labelName : '' }}"
                                                                            class="add_labelName">
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? $columnName : '' }}"
                                                                            class="add_columnName">
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? $inputType : '' }}"
                                                                            class="add_inputtype">
                                                                        <input type="hidden"
                                                                            value="{{ $data->field_type_1 == 'multiple' ? ($data->field_type_2 == 'mandatory' ? 'required' : '') : '' }}"
                                                                            class="add_mandatory">
                                                                    @endif
                                                                </div>
                                                                <div></div>
                                                            </div>
                                                        </div>
                                                            @php $count++; @endphp
                                                            @if ($count % 2 == 0 || $loop->last)
                                                            </div>
                                                            @endif
                                                @endforeach
                                            @endif
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <input type="hidden" name="invoke_date">
                                                <input type="hidden" name="QA_emp_id">
                                                <div class="form-group row">
                                                    <label class="col-md-12 required">
                                                        Charge Status
                                                    </label>
                                                    <div class="col-md-10">
                                                        {!! Form::Select(
                                                            'chart_status',
                                                            [
                                                                '' => '--Select--',
                                                                'QA_Inprocess' => 'Inprocess',
                                                                'QA_Pending' => 'Pending',
                                                                'QA_Completed' => 'Completed',
                                                                'QA_Hold' => 'Hold',
                                                                'Revoke' =>'Revoke'
                                                            ],
                                                            null,
                                                            [
                                                                'class' => 'form-control white-smoke  pop-non-edt-val ',
                                                                'autocomplete' => 'none',
                                                                'id' => 'chart_status_start',
                                                                'style' => 'cursor:pointer',
                                                            ],
                                                        ) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12 required" id="qa_hold_reason_label" style ="display:none">
                                                        Hold Reason
                                                    </label>
                                                    <div class="col-md-10">
                                                        {!! Form::textarea('qa_hold_reason', null, [
                                                            'class' => 'text-black form-control',
                                                            'rows' => 3,
                                                            'id' => 'qa_hold_reason',
                                                            'style' => 'display:none',
                                                        ]) !!}

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <input type="hidden" name="invoke_date">
                                                <input type="hidden" name="QA_emp_id">
                                                <div class="form-group row">
                                                    <label class="col-md-12 required">
                                                        Error Category
                                                    </label>
                                                    @php $qaStatusList = App\Http\Helper\Admin\Helpers::qaStatusList(); @endphp

                                                    <div class="col-md-10">
                                                        <input type="hidden" id="status_val">
                                                           {!! Form::Select(
                                                            'QA_status_code',
                                                            $qaStatusList,
                                                            null,
                                                            [
                                                                'class' => 'form-control white-smoke  kt_select2_qa_status pop-non-edt-val ',
                                                                'autocomplete' => 'none',
                                                                'id' => 'qa_status',
                                                                'style' => 'cursor:pointer',
                                                            ],
                                                        ) !!}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12 required">
                                                        Sub Category
                                                    </label>
                                                    @php $qaSubStatusList = []; @endphp
                                                    <div class="col-md-10">
                                                        {!! Form::Select(
                                                            'QA_sub_status_code',
                                                            $qaSubStatusList,
                                                            null,
                                                            [
                                                                'class' => 'form-control white-smoke  kt_select2_qa_sub_status pop-non-edt-val ',
                                                                'autocomplete' => 'none',
                                                                'id' => 'qa_sub_status',
                                                                'style' => 'cursor:pointer',
                                                            ],
                                                        ) !!}

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row" id="qa_class_cat_scope" ></div>
                                        <hr style="display:none">
                                        <div class="row mt-4">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="QA_rework_comments_label" style="display:none">
                                                      Notes
                                                    </label>
                                                    <div class="col-md-10">
                                                        {!! Form::textarea('QA_rework_comments',  null, ['class' => 'text-black form-control QA_rework_comments','rows' => 6,'id' => 'QA_rework_comments','style'=> 'display:none']) !!}

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" style="display:none" id="coder_rework_status_label">
                                                       Coder Status
                                                    </label>
                                                    <div class="col-md-10">
                                                        <label class="col-md-12 pop-non-edt-val coder_rework_status" id="coder_rework_status" style="display:none">
                                                        </label>
                                                        {{-- {!! Form::Select(
                                                            'coder_rework_status',
                                                            [
                                                                '' => 'Select',
                                                                'Accept' => 'Accept',
                                                                'Rebuttal' => 'Rebuttal',
                                                              ],
                                                            null,
                                                            [
                                                                'class' => 'form-control white-smoke coder_rework_status pop-non-edt-val ',
                                                                'autocomplete' => 'none',
                                                                'id' => 'coder_rework_status',
                                                                'style' => 'cursor:pointer',
                                                            ],
                                                        ) !!} --}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="coder_rework_reason_label" style="display:none">
                                                       Coder Comments
                                                    </label>
                                                    <div class="col-md-10">
                                                        <label class="col-md-12 pop-non-edt-val coder_rework_reason" id="coder_rework_reason" style="display:none">
                                                        </label>
                                                        {{-- {!! Form::textarea('coder_rework_reason',  null, ['class' => 'text-black form-control coder_rework_reason','rows' => 3,'id' => 'rework_reason']) !!} --}}

                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                          </div>
                    </div>
        <div class="modal-footer" style="justify-content: space-between;">


            <p class="timer_1" aria-haspopup="true" aria-expanded="false" data-toggle="modal"
                data-target="#exampleModalCustomScrollable" style="margin-left: -2rem">

                <span title="Total hours">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="22" fill="currentColor"
                        class="bi bi-stopwatch" viewBox="0 0 16 16">
                        <path d="M8.5 5.6a.5.5 0 1 0-1 0v2.9h-3a.5.5 0 0 0 0 1H8a.5.5 0 0 0 .5-.5z" />
                        <path
                            d="M6.5 1A.5.5 0 0 1 7 .5h2a.5.5 0 0 1 0 1v.57c1.36.196 2.594.78 3.584 1.64l.012-.013.354-.354-.354-.353a.5.5 0 0 1 .707-.708l1.414 1.415a.5.5 0 1 1-.707.707l-.353-.354-.354.354-.013.012A7 7 0 1 1 7 2.071V1.5a.5.5 0 0 1-.5-.5M8 3a6 6 0 1 0 .001 12A6 6 0 0 0 8 3" />
                    </svg>
                </span><span id="elapsedTime" class="timer_2"></span>
            </p>

            <button type="submit" class="btn1" id="project_assign_save" style="margin-right: -2rem">Submit</button>
        </div>
    </div>
    {!! Form::close() !!}
    </div>

    </div>
    @endif
    </div>
    <div class="modal fade modal-first" id="myModal_view" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"
        data-backdrop="static" aria-hidden="true">
        @if ($popUpHeader != null)
            <div class="modal-dialog">
                @php
                    $clientName = App\Http\Helper\Admin\Helpers::projectName($popUpHeader->project_id);
                    $projectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID($popUpHeader->project_id, 'encode');
                    if ($popUpHeader->sub_project_id != null) {
                        $practiceName = App\Http\Helper\Admin\Helpers::subProjectName(
                            $popUpHeader->project_id,
                            $popUpHeader->sub_project_id,
                        );
                        $subProjectName = App\Http\Helper\Admin\Helpers::encodeAndDecodeID(
                            $popUpHeader->sub_project_id,
                            'encode',
                        );
                    } else {
                        $practiceName = '';
                        $subProjectName = '';
                    }

                @endphp


                <div class="modal-content" style="margin-top: 7rem">
                    <div class="modal-header" style="background-color: #139AB3;height: 84px">
                        <div class="row" style="height: auto;width:100%">
                            <div class="col-md-4">

                                <div class="align-items-center" style="display: -webkit-box !important;">
                                    <div class="rounded-circle bg-white text-black mr-2"
                                        style="width: 50px; height: 50px; display: flex; justify-content: center; align-items: center;font-weight;bold">
                                        <span>{{ strtoupper(substr($clientName->project_name, 0, 1)) }}</span>
                                    </div>&nbsp;&nbsp;
                                    <div>
                                        <h6 class="modal-title mb-0" id="myModalLabel" style="color: #ffffff;">
                                            {{ ucfirst($clientName->aims_project_name) }}
                                        </h6>
                                        @if ($practiceName != '')
                                            <h6 style="color: #ffffff;font-size:1rem;">
                                                {{ ucfirst($practiceName->sub_project_name) }}</h6>
                                        @endif
                                    </div>&nbsp;&nbsp;
                                    <div class="bg-white rounded-pill px-2 text-black"
                                        style="margin-bottom: 2rem;margin-left:2.2px;font-size:10px;font-weight:500;background-color:#E9F3FF;color:#139AB3;">
                                        <span id="title_status_view"></span>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-md-8 justify-content-end" style="display: -webkit-box !important;">
                                <button type="button" class="btn btn-black-white mr-3 sop_click" id="sop_click"
                                    style="padding: 0.35rem 1rem;">SOP</button>
                            </div> --}}
                        </div>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3" data-scroll="true" data-height="400">
                                <h6 class="title-h6">Basic Information</h6>&nbsp;&nbsp;
                                <input type="hidden" name="idValue">
                                @if (count($popupNonEditableFields) > 0)
                                    @php $count = 0; @endphp
                                    @foreach ($popupNonEditableFields as $data)
                                        @php
                                            $columnName = Str::lower(
                                                str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                            );
                                        @endphp

                                        <label class="col-md-12">{{ $data->label_name }}
                                        </label>
                                        <input type="hidden" name="{{ $columnName }}[]">

                                        <label class="col-md-12 pop-non-edt-val" id={{ $columnName }}>
                                        </label>
                                        <hr style="margin-left:1rem">
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-md-9" style="border-left: 1px solid #ccc;" data-scroll="true"
                                data-height="400">
                                <h6 class="title-h6">AR</h6>&nbsp;&nbsp;
                                @if (count($popupEditableFields) > 0)
                                    @php $count = 0; @endphp
                                    @foreach ($popupEditableFields as $key => $data)
                                        @php
                                            $labelName = $data->label_name;
                                            $columnName = Str::lower(
                                                str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                            );

                                        @endphp
                                        @if ($count % 2 == 0)
                                            <div class="row" id={{ $columnName }}>
                                        @endif
                                        <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12">
                                                        {{ $labelName }}
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id={{ $columnName }}>
                                                    </label>

                                                    <div></div>
                                                </div>
                                            </div>
                                            @php $count++; @endphp
                                            @if ($count % 2 == 0 || $loop->last)
                                              </div>
                                        @endif
                                        @endforeach
                                @endif
                                @php
                                    if($popUpHeader->sub_project_id != null && $popUpHeader->sub_project_id != "") {
                                        $statusActionShow = App\Models\projectInputSetting::where('sub_project_id',$popUpHeader->sub_project_id)->first();                                                                                                                              
                                    } else {
                                        $statusActionShow = null;
                                    }
                                @endphp
                                @if($statusActionShow != null)
                                    <div class="row mt-4">
                                        @if($statusActionShow->status_input == 1)
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="ar_status_label">
                                                        Status Code
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="ar_status_view">
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                        @if($statusActionShow->action_input == 1)
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="ar_action_label">
                                                        Action Code
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="ar_action_view">
                                                    </label>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                    <hr>
                                    <h6 class="title-h6">QA</h6>&nbsp;&nbsp;
                                    @if (count($popupQAEditableFields) > 0)
                                        @php $count = 0; @endphp
                                        @foreach ($popupQAEditableFields as $key => $data)
                                            @php
                                                $labelName = $data->label_name;
                                                $columnName = Str::lower(
                                                    str_replace([' ', '/'], ['_', '_else_'], $data->label_name),
                                                );

                                            @endphp
                                            @if ($count % 2 == 0)
                                                <div class="row" id={{ $columnName }}>
                                            @endif
                                            <div class="col-md-6">
                                                    <div class="form-group row">
                                                        <label class="col-md-12">
                                                            {{ $labelName }}
                                                        </label>
                                                        <label class="col-md-12 pop-non-edt-val" id={{ $columnName }}>
                                                        </label>

                                                        <div></div>
                                                    </div>
                                                </div>
                                                @php $count++; @endphp
                                                @if ($count % 2 == 0 || $loop->last)
                                                </div>
                                            @endif
                                            @endforeach
                                    @endif
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12 required">
                                                        Charge Status
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="chart_status">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_status_label">
                                                        Error Category
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_status_view">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_sub_status_label">
                                                        Sub Category
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_sub_status_view">
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_classification_label">
                                                        Classification
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_classification_view"></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_category_label">
                                                        Category
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_category_view"></label>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="qa_scope_label">
                                                        Scope
                                                    </label>
                                                    <label class="col-md-12 pop-non-edt-val" id="qa_scope_view"></label>
                                                </div>
                                            </div>
                                        </div>
                                        <hr style="display:none" id="hr_view">
                                        <div class="row mt-4">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="QA_rework_comments_label_view" style="display:none">
                                                      Notes
                                                    </label>
                                                    <div class="col-md-10">
                                                         <label class="col-md-12 pop-non-edt-val coder_rework_status" id="QA_rework_comments_view" style="display:none">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-4">
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" style="display:none" id="coder_rework_status_label_view">
                                                       Coder Status
                                                    </label>
                                                    <div class="col-md-10">
                                                        <label class="col-md-12 pop-non-edt-val coder_rework_status" id="coder_rework_status_view" style="display:none">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group row">
                                                    <label class="col-md-12" id="coder_rework_reason_label_view" style="display:none">
                                                       Coder Comments
                                                    </label>
                                                    <div class="col-md-10">
                                                        <label class="col-md-12 pop-non-edt-val coder_rework_reason" id="coder_rework_reason_view" style="display:none">
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                            </div>
                        </div>
    <div class="modal-footer">

        <button class="btn btn-light-danger float-right" id="close_assign" tabindex="10" type="button"
            data-dismiss="modal">
            <span>
                <span>Close</span>
            </span>
        </button>
    </div>
    </div>

    </div>

    </div>
    @endif
    </div>
    <div class="modal fade modal-second modal-left" id="myModal_sop" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                @if ($popUpHeader != null)
                    @php
                        $clientName = App\Http\Helper\Admin\Helpers::projectName($popUpHeader->project_id);
                        $sopDetails = App\Models\SopDoc::where('project_id',$popUpHeader->project_id)->where('sub_project_id',$popUpHeader->sub_project_id)->latest()->first('sop_path');
                        // $pdfName = preg_replace('/[^A-Za-z0-9]/', '_', $clientName->project_name);
                    @endphp
                @else
                    @php
                        $sopDetails = '';
                        // $pdfName = '';
                    @endphp
                @endif
                <div class="modal-header" style="background-color: #139AB3;height: 84px">
                    <h5 class="modal-title" id="exampleModalLabel" style="color: #ffffff;">SOP</h5>
                    <a href={{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }} target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor"
                            class="bi bi-arrow-up-right-square" viewBox="0 0 16 16"
                            style="color: #ffffff; margin-left: 365px;">
                            <path fill-rule="evenodd"
                                d="M15 2a1 1 0 0 0-1-1H2a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1zM0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm5.854 8.803a.5.5 0 1 1-.708-.707L9.243 6H6.475a.5.5 0 1 1 0-1h3.975a.5.5 0 0 1 .5.5v3.975a.5.5 0 1 1-1 0V6.707z" />
                        </svg>
                    </a>
                    <button type="button" class="close comment_close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body">
                    <iframe src={{ isset($sopDetails) && isset($sopDetails->sop_path) ? asset($sopDetails->sop_path) : '#' }} style="width: 100%; height: 418px;"
                        frameborder="0" type="application/pdf"></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-danger" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>


@endsection

<style>
    .dropdown-item.active {
        color: #ffffff;
        text-decoration: none;
        background-color: #888a91;
    }

    .modal-left .modal-dialog {
        margin-top: 90px;
        margin-left: 320px;
        margin-right: auto;
    }

    .modal-left .modal-content {
        border-radius: 5px;
    }

    .modal-right .modal-dialog {
        margin-left: auto;
        margin-right: 220px;
        transition: margin 5s ease-in-out;
    }

    .modal-right .modal-content {
        border-radius: 5px;
    }
    nav{
    float: right !important;
}
</style>

@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        var start = moment().startOf('month')
        var end = moment().endOf('month');
        $('.date_range').attr("autocomplete", "off");
        $('.date_range').daterangepicker({
            showOn: 'both',
            startDate: start,
            endDate: end,
            showDropdowns: true,
            ranges: {}
        });
        $('.date_range').val('');
        var startTime_db;
        $(document).ready(function() {
            $('.cpt').attr('readonly', true);
            $('.icd').attr('readonly', true);
          //  $('#myModal_status').on('shown.bs.modal', function () {
                //$('#qa_status').val(7).change(); 
               // $('#qa_sub_status').val(12).change();
           // });
            var qaSubStatusList = @json($qaSubStatusListVal);
            var qaStatusList = @json( $qaStatusList);
            var arStatusList = @json( $arStatusList);
            var arActionList = @json($arActionListVal);
            var qaClassification = @json($qaClassificationVal);
            var qaCategory = @json($qaCategoryVal);
            var qaScope = @json($qaScopeVal);
            var prevValues;
            $("#expandButton").click(function() {
                var modalContent = $(".modal-content");
                if (modalContent.width() === 800) {
                    modalContent.css("width", "120%");
                } else {
                    modalContent.css("width", "100%");
                }
            });
            $("#filterExpandButton").click(function() {
                var div = document.getElementById('filter_section');
                if (div.style.display !== 'none') {
                    div.style.display = 'none';
                }
                else {
                    div.style.display = 'block';
                }
            });
            var indvidualSearchFieldsCount = Object.keys(@json($projectColSearchFields)).length;
            // $(".close").click(function() {
            //     $("#myModal_status").hide();
            // });
            var countDigits = {{ strlen($assignedCount) }};
            var newWidth = 30 + (countDigits - 1) * 6;
            var newHeight = 30 + (countDigits - 1) * 6;
            $('.code-badge-tab-selected').css({
                'width': newWidth + 'px',
                'height': newHeight + 'px'
            });
            $('.code-badge-tab').css({
                'width': newWidth + 'px',
                'height': newHeight + 'px'
            });

            function getUrlParam(param) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(param);
            }
            const url = window.location.href;
            const startIndex = url.indexOf('projects_') + 'projects_'.length;
            const endIndex = url.indexOf('/', startIndex);
            const urlDynamicValue = url.substring(startIndex, endIndex);

            var uniqueId = 0;
            $('.modal-body').on('click', '.add_more', function() {
                var addBtnClasses = $(this).attr('class');
                var btnLastClass = '';
                if (addBtnClasses) {
                    var classArray = addBtnClasses.split(' ');
                    var btnLastClass = classArray[classArray.length - 1];
                }
                btnLastClass = btnLastClass == 'exclude' ? btnLastClass : 'include';
                var ids = [];
                clumnClassName = $(this).attr('id').replace(/^add_more_/, '');
                $('.' + clumnClassName).each(function() {
                    ids.push($(this).attr('id'));
                });
                var lastElement = ids[ids.length - 1];
                var lastId = lastElement.replace(new RegExp('^' + clumnClassName), '');
                if (lastId) {
                    uniqueId=lastId;
                }
                uniqueId++;
                var labelName =$('.'+clumnClassName).closest('.row_mar_bm').find('.add_labelName').val();
                var columnName = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_columnName').val();
                var inputType = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_inputtype').val();
                var addMandatory = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_mandatory').val();
                var optionsJson = $('.'+clumnClassName).closest('.row_mar_bm').find('.add_options').val();
                var optionsObject = optionsJson ? JSON.parse(optionsJson) : null;
                var optionsArray = optionsObject ? Object.values(optionsObject) : null;

                var newElementId = 'dynamicElement_' + clumnClassName + uniqueId;
                var newElement;
                if (optionsArray == null) {
                    if (inputType !== 'date_range') {
                        if (inputType == 'textarea') {
                            newElement = '<textarea name="' + columnName +
                                '[]"  class="form-control ' + columnName + ' '+ btnLastClass +' white-smoke pop-non-edt-val mt-0" rows="3" id="' +
                                columnName +
                                uniqueId +
                                '" '+ addMandatory +'></textarea>';

                        } else {
                            newElement = '<input type="' + inputType + '" name="' + columnName +
                                '[]"  class="form-control ' + columnName +' '+ btnLastClass + ' white-smoke pop-non-edt-val "  id="' +
                                columnName +
                                uniqueId +
                                '" '+ addMandatory +'>';
                        }
                    } else {
                        newElement = '<input type="text" name="' + columnName +
                            '[]" class="form-control date_range ' + columnName +' '+ btnLastClass +
                            ' white-smoke pop-non-edt-val"  style="cursor:pointer" autocomplete="none" id="' +
                            columnName +
                            uniqueId +
                            '" '+ addMandatory +'>';
                    }
                } else if (inputType === 'select') {

                    newElement = '<select name="' + columnName + '[]"  class="form-control ' +
                        columnName +' '+ btnLastClass + ' white-smoke pop-non-edt-val" id="' +
                        columnName +
                        uniqueId +
                        '" '+ addMandatory +'>';

                    optionsArray.unshift('-- Select --');
                    optionsArray.forEach(function(option) {
                        newElement += option != '-- Select --' ? '<option value="' + option + '">' +
                            option + '</option>' : '<option value="">' + option + '</option>';
                    });
                    newElement += '</select>';
                } else if (inputType === 'checkbox' && Array.isArray(optionsArray)) {
                    newElement = '<div class="form-group row">';

                    optionsArray.forEach(function(option) {
                        newElement +=
                            '<div class="col-md-6">' +
                            '<div class="checkbox-inline mt-2">' +
                            '<label class="checkbox pop-non-edt-val" style="word-break: break-all;" ' +
                            addMandatory + '>' +
                            '<input type="checkbox" name="' + columnName + '[]" value="' + option +
                            '" id="' +
                            columnName +
                            uniqueId +
                            '" class="' +
                            columnName +' '+ btnLastClass +
                            '" '+ addMandatory +'>' + option +
                            '<span></span>' +
                            '</label>' +
                            '</div>' +
                            '</div>';
                    });

                    newElement += '</div>';
                } else if (inputType === 'radio' && Array.isArray(optionsArray)) {
                    newElement = '<div class="form-group row">';
                    optionsArray.forEach(function(option) {
                        newElement +=
                            '<div class="col-md-6">' +
                            '<div class="radio-inline mt-2">' +
                            '<label class="radio pop-non-edt-val" style="word-break: break-all;" ' + addMandatory +
                            '>' +
                            '<input type="radio" name="' + columnName + '_' + uniqueId +
                            '" value="' + option + '" class="' + columnName +' '+ btnLastClass +'" id="' +
                            columnName +
                            uniqueId +
                            '" '+ addMandatory +'>' + option +
                            '<span></span>' +
                            '</label>' +
                            '</div>' +
                            '</div>';
                    });

                    newElement += '</div>';
                }

                var plusButton = '<i class="fa fa-plus add_more" id="' +'add_more_'+columnName +'"></i>';
                 var newRow = '<div class="row mt-6" id="' + newElementId + '">' +
                    '<div class="col-md-10">' + newElement + '</div>' +
                    '<div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                        plusButton +
                    '</div><div></div>' +
                    '</div>';
                var modalBody = $('.'+clumnClassName).closest('.modal-content').find('.modal-body');


                $(this).closest('.col-md-6').append(newRow);
                     elementToRemove = 'add_more_'+clumnClassName;
                                $('#'+elementToRemove).remove();
                                uniqueId = uniqueId-1;
                                removeId = uniqueId == 0 ? clumnClassName : clumnClassName+ uniqueId;
                                if(uniqueId > 0) {
                                  $('#'+lastElement).closest('.col-md-10').next('.col-md-1').append('<i class="fa fa-minus minus_button remove_more" id="'+removeId +'"></i>');
                                }


                if (inputType === 'date_range') {
                    var newDateRangePicker = modalBody.find('#' + newElementId).find('.date_range');
                    newDateRangePicker.daterangepicker({
                        showOn: 'both',
                        startDate: start,
                        endDate: end,
                        showDropdowns: true,
                        ranges: {}
                    }).attr("autocomplete", "off");
                    newDateRangePicker.val('');
                }
            });

            $(document).on('click', '.remove_more', function() {
                var uniqueId = $(this).attr('id');
                var elementId = 'dynamicElement_' + uniqueId;
                $('#' + elementId).remove();
            });
            var d = new Date();
            var month = d.getMonth() + 1;
            var day = d.getDate();
            var date = (month < 10 ? '0' : '') + month + '-' +
                (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#client_assigned_list").DataTable({
                processing: true,
                ordering: true,
                clientSide: true,
                lengthChange: false,
                searching: indvidualSearchFieldsCount > 0 ? false : true,
                paging: false,
                info: false,
                // pageLength: 20,
                scrollCollapse: true,
                scrollX: true,
                "initComplete": function(settings, json) {
                    $('body').find('.dataTables_scrollBody').addClass("scrollbar");
                },
                language: {
                    "search": '',
                    "searchPlaceholder": "   Search",
                },
                // buttons: [{
                //     "extend": 'excel',
                //     "text": `<span data-dismiss="modal" data-toggle="tooltip" data-placement="left" data-original-title="Export" style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                //              </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></span>`,
                //     "className": 'btn btn-primary-export text-white',
                //     "title": 'PROCODE',
                //     "filename": 'procode_qa_assigned_' + date,
                //     "exportOptions": {
                //         "columns": ':not(.notexport)'
                //     }
                // }],
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            // table.buttons().container()
            //     .appendTo('.outside');
            $('.dataTables_filter').addClass('pull-left');
            var clientName = $('#clientName').val();
            var subProjectName = $('#subProjectName').val();
            $(document).on('click', '.clickable-row', function(e) {
                var classes = $(this).attr('class');
                var lastClass = '';
                if (classes) {
                    var classArray = classes.split(' ');
                    var lastClass = classArray[classArray.length - 1];
                }
                var record_id = $(this).closest('tr').find('#table_id').text();
                var $row = $(this).closest('tr');
                var tdCount = $row.find('td').length;
                var thCount = tdCount - 1;

                var headers = [];
                $row.closest('table').find('thead th input').each(function() {
                    if ($(this).val() != undefined) {
                        headers.push($(this).val());
                    }
                });
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    }
                });

                $.ajax({
                    url: "{{ url('qa_production/qa_client_completed_datas_details') }}",
                    method: 'POST',
                    data: {
                        record_id: record_id,
                        clientName: clientName,
                        subProjectName: subProjectName,
                        urlDynamicValue: urlDynamicValue
                    },
                    success: function(response) {
                        if (lastClass == 'start') {
                            if (response.success == true) {
                                $('#myModal_status').modal('show');
                                startTime_db = response.startTimeVal;
                                headers.push('QA_rework_comments');
                                headers.push('coder_rework_status');
                                headers.push('coder_rework_reason');
                                prevValues = response.clientData;
                                handleClientPendData(response.clientData, headers);
                            } else {
                                $('#myModal_status').modal('hide');
                                js_notification('error', 'Something went wrong');
                            }
                        }
                    },
                });

                function handleClientPendData(clientData, headers) {

                    $.each(headers, function(index, header) {
                        value = clientData[header];
                        if (header == 'annex_coder_trends') {
                                if (/_el_/.test(value)) {
                                    var commentsValues = value.split('_el_');
                                    var commentsText = commentsValues.join('\n');
                                    $('textarea[name="annex_coder_trends"]').val(commentsText);
                                } else {
                                    $('textarea[name="annex_coder_trends"]').val(value);
                                }
                           }
                          if (header == 'QA_rework_comments') {
                                if (/_el_/.test(value)) {
                                    var commentsValues = value.split('_el_');
                                    var commentsText = commentsValues.join('\n');
                                    $('textarea[name="QA_rework_comments"]').val(commentsText);
                                } else {
                                    $('textarea[name="QA_rework_comments"]').val(value);
                                }
                           }
                        $('label[id="' + header + '"]').html("");
                        $('input[name="' + header + '[]"]').html("");
                        if (/_el_/.test(value)) {
                            elementToRemove = 'add_more_' + header;
                            $('#' + elementToRemove).remove();
                            var values = value.split('_el_');
                            var optionsJson = $('.' + header).closest('.dynamic-field').find(
                                '.add_options').val();
                            var optionsObject = optionsJson ? JSON.parse(optionsJson) : null;
                            var optionsArray = optionsObject ? Object.values(optionsObject) : null;
                            var addMandatory =  $('.'+header).closest('.dynamic-field').find('.add_mandatory').val();
                            var inputType;
                            $('select[name="' + header + '[]"]').val(values[0]).trigger('change');
                            $('textarea[name="' + header + '[]"]').val(values[0]);
                            if ($('input[name="' + header + '[]"][type="checkbox"]').length > 0) {
                                var checkboxValues = values[0].split(',');
                                $('input[name="' + header + '[]"]').each(function() {
                                    var checkboxValue = $(this).val();
                                    var isChecked = checkboxValues.includes(checkboxValue);
                                    $(this).prop('checked', isChecked);
                                });
                            } else if ($('input[name="' + header + '"][type="radio"]').length > 0) {

                                $('input[name="' + header + '"]').filter('[value="' + values[0] +
                                    '"]').prop(
                                    'checked', true);
                            } else {
                                $('input[name="' + header + '[]"]').val(values[0]);
                            }


                            for (var i = 1; i < values.length; i++) {
                                var selectType;
                                var isLastValue = i === values.length - 1;
                                var newElementId = 'dynamicElement_' + header + i;
                                if ($('select[name="' + header + '[]"]').prop('tagName') !=
                                    undefined) {
                                    selectType = $('<select>', {
                                        name: header + '[]',
                                        class: 'form-control ' + header +
                                            ' white-smoke pop-non-edt-val',
                                        id: header + i,
                                        addMandatory
                                    });
                                    selectType.append($('<option>', {
                                        value: '',
                                        text: '-- Select --'
                                    }));
                                    optionsArray.forEach(function(option) {
                                        selectType.append($('<option>', {
                                            value: option,
                                            text: option,
                                            selected: option == values[i]
                                        }));
                                    });
                                    var selectWrapper = $('<div>', {
                                        class: 'col-md-10'
                                    }).append(selectType);
                                    if (i === values.length - 1) {
                                        var minusButton = $('<i>', {
                                            class: 'fa fa-plus add_more',
                                            id: 'add_more_' + header
                                        });
                                    } else {
                                        var minusButton = $('<i>', {
                                            class: 'fa fa-minus minus_button remove_more',
                                            id: header + i
                                        });
                                    }
                                    var colLabel = $('<div>', {
                                        class: 'col-md-1 col-form-label text-lg-right pt-0 pb-4',
                                        style: 'margin-left: -1.3rem;'
                                    }).append(minusButton);
                                    var rowDiv = $('<div>', {
                                        class: 'row mt-4',
                                        id: newElementId
                                    }).append(selectWrapper, colLabel);
                                    $('select[name="' + header + '[]"]').closest('.dynamic-field')
                                        .append(rowDiv);

                                } else if ($('textarea[name="' + header + '[]"]').prop(
                                    'nodeName') != undefined) {
                                    inputType = '<textarea name="' + header +
                                        '[]" '+addMandatory+' class="form-control ' + header +
                                        ' white-smoke pop-non-edt-val mt-0" rows="3" id="' +
                                        header + i + '">' + values[i] + '</textarea>';
                                    if (i === values.length - 1) {
                                        var minusButton = '<i class="fa fa-plus add_more" id="' +
                                            'add_more_' + header + '"></i>';
                                    } else {
                                        var minusButton =
                                            '<i class="fa fa-minus minus_button remove_more" id="' +
                                            header + i + '"></i>';
                                    }
                                    var span = '<div class="row mt-4" id="' + newElementId + '">' +
                                        '<div class="col-md-10">' + inputType +
                                        '</div><div class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                        minusButton + '</div><div></div></div>';
                                    $('textarea[name="' + header + '[]"]').closest('.dynamic-field')
                                        .append(span);
                                } else if ($('input[name="' + header + '[]"][type="checkbox"]')
                                    .length > 0 && Array.isArray(optionsArray)) {
                                    inputType = '<div class="form-group row">';
                                    optionsArray.forEach(function(option) {
                                        var checked = (values[i] && values[i].split(',')
                                                .includes(option.toString())) ? 'checked' :
                                            '';
                                        inputType +=
                                            '<div class="col-md-6">' +
                                            '<div class="checkbox-inline mt-2">' +
                                            '<label class="checkbox pop-non-edt-val" style="word-break: break-all;" >' +
                                            '<input type="checkbox" name="' + header +
                                            '[]" value="' + option + '" '+addMandatory+' class="' + header +
                                            '" id="' + header + i + '" ' + checked + '>' +
                                            option +
                                            '<span></span>' +
                                            '</label>' +
                                            '</div>' +
                                            '</div>';
                                    });

                                    inputType += '</div>';
                                    if (i === values.length - 1) {
                                        var minusButton = '<i class="fa fa-plus add_more" id="' +
                                            'add_more_' + header + '"></i>';
                                    } else {
                                        var minusButton =
                                            '<i class="fa fa-minus minus_button remove_more" id="' +
                                            header + i + '"></i>';
                                    }
                                    var span = '<div class="row mt-4" id="' + newElementId + '">' +
                                        '<div class="col-md-10">' + inputType +
                                        '</div><div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                        minusButton + '</div><div></div></div>';

                                    $('input[name="' + header + '[]"]').closest('.dynamic-field')
                                        .append(span);
                                } else if ($('input[name="' + header + '"][type="radio"]').length >
                                    0 && Array.isArray(optionsArray)) {
                                    inputType = '<div class="form-group row">';
                                    optionsArray.forEach(function(option) {
                                        var checked = (values[i] && values[i].split(',')
                                                .includes(option.toString())) ? 'checked' :
                                            '';
                                        inputType +=
                                            '<div class="col-md-6">' +
                                            '<div class="radio-inline mt-2">' +
                                            '<label class="radio pop-non-edt-val" style="word-break: break-all;" >' +
                                            '<input type="radio" name="' + header + '_' +
                                            i + '" '+addMandatory+' class="' + header + '" value="' +
                                            option + '" id="' +
                                            header + i + '" ' + checked + '>' + option +
                                            '<span></span>' +
                                            '</label>' +
                                            '</div>' +
                                            '</div>';
                                    });

                                    inputType += '</div>';
                                    if (i === values.length - 1) {
                                        var minusButton = '<i class="fa fa-plus add_more" id="' +
                                            'add_more_' + header + '"></i>';
                                    } else {
                                        var minusButton =
                                            '<i class="fa fa-minus minus_button remove_more" id="' +
                                            header + i + '"></i>';
                                    }
                                    var span = '<div class="row mt-4" id="' + newElementId + '">' +
                                        '<div class="col-md-10">' + inputType +
                                        '</div><div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                        minusButton + '</div><div></div></div>';
                                    $('input[name="' + header + '"]').closest('.dynamic-field')
                                        .append(span);
                                } else {
                                    var fieldType = $('.' + header).attr('type');
                                    var classes = $('.' + header).attr('class');
                                    if(classes != undefined) {
                                       var classArray = classes.split(' ');
                                    } else {
                                        var classArray = [];
                                    }
                                    var dateRangeClass = '';
                                    for (var j = 0; j < classArray.length; j++) {
                                        if (classArray[j] === 'date_range') {
                                            dateRangeClass = classArray[j];
                                            break;
                                        }
                                    }
                                    if (dateRangeClass == 'date_range') {

                                        inputType = '<input type="' + fieldType + '" name="' +
                                            header + '[]"  '+addMandatory+' class="form-control date_range ' +
                                            header +
                                            ' white-smoke pop-non-edt-val" autocomplete="none" style="cursor:pointer" value="' +
                                            values[i] + '" id="' + header + i + '">';
                                        if (i === values.length - 1) {
                                            var minusButton =
                                                '<i class="fa fa-plus add_more" id="' +
                                                'add_more_' + header + '"></i>';
                                        } else {
                                            var minusButton =
                                                '<i class="fa fa-minus minus_button remove_more" id="' +
                                                header + i + '"></i>';
                                        }
                                    } else {
                                        inputType = '<input type="' + fieldType + '" name="' +
                                            header + '[]"  '+addMandatory+' class="form-control ' + header +
                                            ' white-smoke pop-non-edt-val"  value="' + values[i] +
                                            '" id="' + header + i + '">';
                                        if (i === values.length - 1) {
                                            var minusButton =
                                                '<i class="fa fa-plus add_more" id="' +
                                                'add_more_' + header + '"></i>';
                                        } else {
                                            var minusButton =
                                                '<i class="fa fa-minus minus_button remove_more" id="' +
                                                header + i + '"></i>';
                                        }
                                    }

                                    var span = '<div class="row mt-4"  id="' + newElementId + '">' +
                                        '<div class="col-md-10">' + inputType +
                                        '</div><div  class="col-md-1 col-form-label text-lg-right pt-0 pb-4" style="margin-left: -1.3rem;">' +
                                        minusButton + '</div><div></div></div>';
                                    $('input[name="' + header + '[]"]').closest('.dynamic-field')
                                        .append(span);
                                }
                            }
                            $('.date_range').daterangepicker({
                                autoUpdateInput: false,
                            }).on('apply.daterangepicker', function(ev, picker) {
                                $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' +
                                    picker.endDate.format('MM/DD/YYYY'));
                            }).attr("autocomplete", "off");


                        } else if ($('input[name="' + header + '[]"]').is(':checkbox') && value !== null) {
                            var checkboxValues = value.split(',');
                            $('input[name="' + header + '[]"]').each(function() {
                                $(this).prop('checked', checkboxValues.includes($(this)
                                .val()));
                            });
                        } else if ($('input[name="' + header + '"]').is(':radio') && value !== '' && value !== null) {
                          if(value.length > 0) {
                                $('input[name="' + header + '"]').filter('[value="' + value + '"]')
                                    .prop(
                                        'checked', true);
                          }
                        } else if ($('select[name="' + header + '[]"]').length) {
                            $('select[name="' + header + '[]"]').val(value).trigger('change');
                        } else {
                             $('input[name="idValue"]').val(clientData['parent_id']);
                             $('input[name="parentId"]').val(clientData['parent_id']);
                             $('input[name="record_old_status"]').val('QA_Assigned');
                            if (header === 'chart_status' && value.includes('CE_')) {
                                claimStatus = value;
                                value = value.replace('CE_', '');
                                $('select[name="chart_status"]').val('QA_Inprocess').trigger(
                                    'change');
                                $('#title_status').text("In Process");
                                $('input[name="record_old_status"]').val('QA_Assigned');
                            } else if(header === 'chart_status' && value.includes('QA_')) {
                                claimStatus = value;
                                value = value.replace('QA_', '');
                                $('select[name="chart_status"]').val('QA_Inprocess').trigger(
                                    'change');
                                $('#title_status').text("In Process");
                                $('input[name="record_old_status"]').val('QA_Inprocess');
                            }
                            // if (header == 'id') {
                            //     $('input[name="idValue"]').val(value);
                            // }
                            if (header == 'invoke_date') {
                                $('input[name="invoke_date"]').val(value);
                            }
                            if (header == 'QA_emp_id') {
                                $('input[name="QA_emp_id"]').val(value);
                            }

                            if (header == 'QA_status_code') {
                                $('select[name="QA_status_code"]').val(value).trigger('change');
                                $('#status_val').val(value);
                            }
                            if (header == 'QA_sub_status_code') {
                                statusVal = $('#status_val').val();
                                subStatus(statusVal,value);
                                classCatScope(statusVal,value);
                            }
                            if (header == 'coder_rework_status') {
                                $('label[id="coder_rework_status"]').text(value);
                                if (value !== null) {
                                    $('#coder_rework_status_label').css('display','block');
                                    $('#coder_rework_status').css('display','block');
                                } else {
                                    $('#coder_rework_status_label').css('display','none');
                                    $('#coder_rework_status').css('display','none');
                                }
                            }
                            if (header == 'coder_rework_reason') {

                                $('label[id="coder_rework_reason"]').text(value);
                                if (value !== null) {
                                    $('#coder_rework_reason_label').css('display','block');
                                    $('#coder_rework_reason').css('display','block');
                                } else {
                                    $('#coder_rework_reason_label').css('display','none');
                                    $('#coder_rework_reason').css('display','none');
                                }
                            }
                            if (header == 'ar_status_code') {
                                $('select[name="ar_status_code"]').val(value).trigger('change');
                                $('#ar_status_val').val(value);
                            }
                            if (header == 'ar_action_code') {
                                statusVal = $('#ar_status_val').val();
                                actionCode(statusVal,value);
                            }
                            $('textarea[name="' + header + '[]"]').val(value);
                            $('label[id="' + header + '"]').text(value);
                            if(value != null) {
                                $('input[name="' + header + '[]"]').val(value);
                                $('input[name="' + header + '"]').val(value);
                            }
                            if (header == 'am_cpt' || header == 'am_icd') {
                                $('textarea[name="' + header + '_hidden[]"]').val(value);
                            }
                        }
                    });

                }
            });
                function subStatus(statusVal,value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "GET",
                        url: "{{ url('qa_production/qa_sub_status_list') }}",
                        data: {
                            status_code_id: statusVal
                        },
                        success: function(res) {
                            subStatusCount = Object.keys(res.subStatus).length;
                            var sla_options = '<option value="">-- Select --</option>';
                            $.each(res.subStatus, function(key, value) {
                                sla_options += '<option value="' + key + '" ' + '>' + value +
                                    '</option>';
                            });
                            $('select[name="QA_sub_status_code"]').html(sla_options);
                            $('select[name="QA_sub_status_code"]').val(12).change();
                            if (value) {
                                $('select[name="QA_sub_status_code"]').val(value);
                            }
                        },
                        error: function(jqXHR, exception) {}
                    });
                }
                $(document).on('change', '#qa_status', function() {
                    var status_code_id = $(this).val();
                        KTApp.block('#myModal_status', {
                            overlayColor: '#000000',
                            state: 'danger',
                            opacity: 0.1,
                            message: 'Fetching...',
                        });
                        $('#qa_class_cat_scope').html('');
                    subStatus(status_code_id,'');
                    KTApp.unblock('#myModal_status');
                });
                function classCatScope(status_code_id,sub_status_code_id){
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "GET",
                        url: "{{ url('qa_production/qa_class_cat_scope') }}",
                        data: {
                            status_code_id: status_code_id,
                            sub_status_code_id: sub_status_code_id
                        },
                        success: function(res) {
                            if(res.success == true){
                              
                               $('#qa_class_cat_scope').html(res.html);
                            }                   
                        }
                    })
                };
                $(document).on('change', '#qa_sub_status', function() {
                    var status_code_id = $('#qa_status').val();
                    var sub_status_code_id = $(this).val();
                    classCatScope(status_code_id,sub_status_code_id);                  
                });
            $(document).on('click', '.clickable-view', function(e) {
                $('#myModal_status').modal('hide');
                var record_id = $(this).closest('tr').find('#table_id').text();
                var $row = $(this).closest('tr');
                var tdCount = $row.find('td').length;
                var thCount = tdCount - 1;

                var headers = [];
                $row.closest('table').find('thead th input').each(function() {
                    if ($(this).val() != undefined) {
                        headers.push($(this).val());
                    }
                });
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    }
                });

                $.ajax({
                    url: "{{ url('qa_production/qa_client_view_details') }}",
                    method: 'POST',
                    data: {
                        record_id: record_id,
                        clientName: clientName,
                        subProjectName: subProjectName
                    },
                    success: function(response) {
                        if (response.success == true) {
                            headers.push('QA_rework_comments');
                                headers.push('coder_rework_status');
                                headers.push('coder_rework_reason');
                            $('#myModal_view').modal('show');
                            handleClientData(response.clientData, headers);
                        } else {
                            $('#myModal_view').modal('hide');
                            js_notification('error', 'Something went wrong');
                        }
                    },
                });

                function handleClientData(clientData, headers) {
                    $.each(headers, function(index, header) {
                        value = clientData[header];
                        $('label[id="' + header + '"]').html("");
                        if (/_el_/.test(value)) {
                            var values = value.split('_el_');
                            var formattedDatas = [];
                            values.forEach(function(data, index) {
                                if (data !== '') {
                                    var circle = $('<span>').addClass('circle');
                                    var span = $('<span>').addClass('date-label').text(
                                    data);
                                    span.prepend(circle);
                                    formattedDatas.push(span);
                                }
                            });
                            formattedDatas.forEach(function(span, index) {
                                if (header == 'QA_rework_comments') {
                                        $('label[id="QA_rework_comments_view"]').append(span);
                                        if (span !== null) {
                                            $('#QA_rework_comments_label_view').css('display','block');
                                            $('#QA_rework_comments_view').css('display','block');
                                            $('#hr_view').css('display','block');
                                        } else {
                                            $('#QA_rework_comments_label_view').css('display','none');
                                            $('#QA_rework_comments_view').css('display','none');
                                            $('#hr_view').css('display','none');
                                        }
                                    }
                                $('label[id="' + header + '"]').append(span);
                            });
                        } else {

                            if (header === 'chart_status' && value.includes('CE_')) {
                                // value = value.replace('CE_', '');
                                value = "Assigned";
                                $('#title_status_view').text("Assigned");
                            } else if (header === 'chart_status' && value.includes('QA_')) {
                                value = "In Process";
                                $('#title_status_view').text("In Process");
                            }
                             $('#chart_status').text() == "Assigned" ? $('#qa_status_label').css('display','none') : $('#qa_status_label').css('display','block');
                            $('#chart_status').text() == "Assigned" ? $('#qa_sub_status_label').css('display','none') : $('#qa_sub_status_label').css('display','block');
                            $('#chart_status').text() == "Assigned" ? $('#qa_status_view').css('display','none') : $('#qa_status_view').css('display','block');
                            $('#chart_status').text() == "Assigned" ? $('#qa_sub_status_view').css('display','none') : $('#qa_sub_status_view').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#qa_classification_label').css('display','none') : $('#qa_classification_label').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#qa_classification_view').css('display','none') : $('#qa_classification_view').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#qa_category_label').css('display','none') : $('#qa_category_label').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#qa_category_view').css('display','none') : $('#qa_category_view').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#qa_scope_label').css('display','none') : $('#qa_scope_label').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#qa_scope_view').css('display','none') : $('#qa_scope_view').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#ar_status_label').css('display','none') : $('#ar_status_label').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#ar_action_label').css('display','none') : $('#ar_action_label').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#ar_status_view').css('display','none') : $('#ar_status_view').css('display','block');
                            // $('#chart_status').text() == "Assigned" ? $('#ar_action_view').css('display','none') : $('#ar_action_view').css('display','block');
                            if (header == 'QA_status_code') {
                                var statusName = '';
                                    $.each(qaStatusList, function(key, val) {
                                        if (value == key) {
                                            statusName = val;
                                        }
                                    });
                                    $('label[id="qa_status_view"]').text(statusName);
                               }
                                if (header == 'QA_sub_status_code') {
                                    var subStatusName = '';
                                    $.each(qaSubStatusList, function(key, val) {
                                        if (value == key) {
                                            subStatusName = val;
                                        }
                                    });
                                    $('label[id="qa_sub_status_view"]').text(subStatusName);

                                }
                                if (header == 'qa_classification') {
                                var qa_classification = '';
                                $.each(qaClassification, function(key, val) {      
                                    if (value == key) {
                                        qa_classification = val;
                                    }
                                });
                                
                                if(qa_classification == '') {
                                    $('label[id="qa_classification_label"]').css('display','none');
                                } else {
                                    $('label[id="qa_classification_label"]').css('display','block');
                                }
                                $('label[id="qa_classification_view"]').text(qa_classification);
                            }
                            if (header == 'qa_category') {
                                var qa_category = '';
                                $.each(qaCategory, function(key, val) {      
                                    if (value == key) {
                                        qa_category = val;
                                    }
                                });
                                if(qa_category == '') {
                                    $('label[id="qa_category_label"]').css('display','none');
                                } else {
                                    $('label[id="qa_category_label"]').css('display','block');
                                }
                                $('label[id="qa_category_view"]').text(qa_category);
                            }
                            if (header == 'qa_scope') {
                                var qa_scope = '';
                                $.each(qaScope, function(key, val) {      
                                    if (value == key) {
                                        qa_scope = val;
                                    }
                                });
                                if(qa_scope == '') {
                                    $('label[id="qa_scope_label"]').css('display','none');
                                } else {
                                    $('label[id="qa_scope_label"]').css('display','block');
                                }
                                $('label[id="qa_scope_view"]').text(qa_scope);
                            }
                                if (header == 'ar_status_code') {
                                   var statusName = '';
                                    $.each(arStatusList, function(key, val) {
                                        if (value == key) {
                                            statusName = val;
                                        }
                                    });
                                    if(statusName == '') {
                                        $('label[id="ar_status_label"]').css('display','none');
                                    } else {
                                        $('label[id="ar_status_label"]').css('display','block');
                                    }
                                    $('label[id="ar_status_view"]').text(statusName);
                               }
                                if (header == 'ar_action_code') {
                                    var subStatusName = '';
                                    $.each(arActionList, function(key, val) {
                                        if (value == key) {
                                            subStatusName = val;
                                        }
                                    });
                                    if(subStatusName == '') {
                                        $('label[id="ar_action_label"]').css('display','none');
                                    } else {
                                        $('label[id="ar_action_label"]').css('display','block');
                                    }
                                    $('label[id="ar_action_view"]').text(subStatusName);

                                }
                                if (header == 'coder_rework_status') {
                                   $('label[id="coder_rework_status_view"]').text(value);
                                   if (value !== null && value !== '') {
                                        $('#coder_rework_status_label_view').css('display','block');
                                        $('#coder_rework_status_view').css('display','block');
                                    } else {
                                        $('#coder_rework_status_label_view').css('display','none');
                                        $('#coder_rework_status_view').css('display','none');
                                    }
                                }
                                if (header == 'coder_rework_reason') {

                                    $('label[id="coder_rework_reason_view"]').text(value);
                                    if (value !== null && value !== '') {
                                        $('#coder_rework_reason_label_view').css('display','block');
                                        $('#coder_rework_reason_view').css('display','block');
                                        $('#hr_view').css('display','block');
                                    } else {
                                        $('#coder_rework_reason_label_view').css('display','none');
                                        $('#coder_rework_reason_view').css('display','none');
                                        $('#hr_view').css('display','none');
                                    }
                                }
                                if (header == 'QA_rework_comments') {
                                        $('label[id="QA_rework_comments_view"]').text(value);
                                        if (value !== null && value !== '') {
                                            $('#QA_rework_comments_label_view').css('display','block');
                                            $('#QA_rework_comments_view').css('display','block');
                                            $('#hr_view').css('display','block');
                                        } else {
                                            $('#QA_rework_comments_label_view').css('display','none');
                                            $('#QA_rework_comments_view').css('display','none');
                                            $('#hr_view').css('display','none');
                                        }
                                    }
                            $('label[id="' + header + '"]').text(value);
                        }

                        function formatDate(dateString) {
                            var parts = dateString.split('-');
                            var formattedDatas = parts[1] + '/' + parts[2] + '/' + parts[0];
                            return formattedDatas;
                        }
                    });

                }
            });
            $(document).on('click', '.sop_click', function(e) {
                $('#myModal_sop').modal('show');
            });
            function actionCode(statusVal,value) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        type: "GET",
                        url: "{{ url('production/ar_action_code_list') }}",
                        data: {
                            status_code_id: statusVal
                        },
                        success: function(res) {
                            subStatusCount = Object.keys(res.subStatus).length;
                            var sla_options = '<option value="">-- Select --</option>';
                            $.each(res.subStatus, function(key, value) {
                                sla_options += '<option value="' + key + '" ' + '>' + value +
                                    '</option>';
                            });
                            $('select[name="ar_action_code"]').html(sla_options);
                            // $('select[name="QA_sub_status_code"]').val(12).change();
                            if (value) {
                                $('select[name="ar_action_code"]').val(value);
                            }
                        },
                        error: function(jqXHR, exception) {}
                    });
                }
                $(document).on('change', '#ar_status_code', function() {
                    var status_code_id = $(this).val();
                        KTApp.block('#myModal_status', {
                            overlayColor: '#000000',
                            state: 'danger',
                            opacity: 0.1,
                            message: 'Fetching...',
                        });
                        actionCode(status_code_id,'');
                    KTApp.unblock('#myModal_status');
                });
            $(document).ready(function() {
                $('#myModal_sop').on('shown.bs.modal', function() {
                    $('#myModal_status').addClass('modal-right');
                    $('#myModal_view').addClass('modal-right');
                });

                $('#myModal_sop').on('hidden.bs.modal', function() {
                    $('#myModal_status').removeClass('modal-right');
                    $('#myModal_view').removeClass('modal-right');
                });
            });

            $(document).on('click', '#project_assign_save', function(e) {
                e.preventDefault();
                var inputTypeValue = 0;var inputTypeRadioValue = 0;
                var claimStatus = $('#chart_status_start').val();
                if (claimStatus == "QA_Hold") {
                    var ceHoldReason = $('#qa_hold_reason');
                    if (ceHoldReason.val() == '') {
                        ceHoldReason.css('border-color', 'red', 'important');
                        inputTypeValue = 1;
                    } else {
                        ceHoldReason.css('border-color', '');
                        inputTypeValue = 0;
                    }
                }
                    var qaStatus = $('#qa_status');
                    var qaSubStatus =  $('#qa_sub_status');
                    if (qaStatus.val() == '' || qaStatus.val() == null) {
                        qaStatus.next('.select2').find(".select2-selection").css('border-color', 'red','important');
                        inputTypeValue = 1;
                        return false;
                    }

                    if (qaSubStatus.val() == '' || qaSubStatus.val() == null) {
                        qaSubStatus.next('.select2').find(".select2-selection").css('border-color', 'red','important');
                        inputTypeValue = 1;
                        return false;
                    }
                // $('#formConfiguration').serializeArray().map(function(input) {
                //     labelName = input.name;
                //         if(labelName.substring(0, 3).toLowerCase() == "cpt") {
                //             var textValue = input.value;
                //             if(textValue.length < 5) {
                //                 inputTypeValue = 1;
                //                 js_notification('error',"The CPT value must be at least 5 characters long" );
                //             } else {
                //                 inputTypeValue = 0;
                //             }
                //         }
                //         if(labelName.substring(0, 3).toLowerCase() == "icd") {
                //             var textValue = input.value;
                //             if(textValue.length < 3) {
                //                 inputTypeValue = 1;
                //                 js_notification('error', "The ICD value must be at least 3 characters long" );
                //             } else {
                //                 inputTypeValue = 0;
                //             }
                //         }
                //         return inputTypeValue;
                // });
                var fieldNames = $('#formConfiguration').serializeArray().map(function(input) {
                    return input.name;
                });
                var requiredFields = {};
                var requiredFieldsType = {};
                var inputclass = [];
                $('#formConfiguration').find(':input[required], select[required], textarea[required]',
                    ':input[type="checkbox"][required], input[type="radio"][required]').each(
                    function() {
                        var fieldName = $(this).attr('name');
                        var fieldType = $(this).attr('type') || $(this).prop('tagName').toLowerCase();

                        if (!requiredFields[fieldType]) {
                            requiredFields[fieldType] = [];
                        }

                        requiredFields[fieldType].push(fieldName);
                    });


                $('input[type="radio"]').each(function() {
                    var groupName = $(this).attr("name");
                    var mandatory = $(this).prop('required');
                    if ($('input[type="radio"][name="' + groupName + '"]:checked').length === 0 && mandatory === true) {
                        $('#radio_p1').css('display', 'block');
                        inputTypeRadioValue = 1;
                        return false;
                    } else {
                        $('#radio_p1').css('display', 'none');
                        inputTypeRadioValue = 0;
                    }
                });


                $('input[type="checkbox"]').each(function() {
                    var groupName = $(this).attr("id");
                    var mandatory = $(this).prop('required');
                    if ($(this).attr("name") !== 'check[]' && $(this).attr("name") !== undefined) {
                        if ($('input[type="checkbox"][id="' + groupName + '"]:checked').length ===
                            0) {
                            if ($('input[type="checkbox"][id="' + groupName + '"]:checked')
                                .length ===
                                0 && mandatory === true) {
                                $('#check_p1').css('display', 'block');
                                inputTypeValue = 1;
                            } else {
                                $('#check_p1').css('display', 'none');
                                inputTypeValue = 0;
                            }
                            return false;
                        }
                    }
                });

                for (var fieldType in requiredFields) {
                    if (requiredFields.hasOwnProperty(
                            fieldType)) {
                        var fieldNames = requiredFields[fieldType];
                        fieldNames.forEach(function(fieldNameVal) {
                            var label_id = $('' + fieldType + '[name="' + fieldNameVal + '"]').attr(
                                'class');
                            var classValue = (fieldType == 'text' || fieldType == 'date') ? $(
                                    'input' + '[name="' + fieldNameVal + '"]').attr(
                                    'class') : $('' + fieldType + '[name="' + fieldNameVal + '"]')
                                .attr(
                                    'class');
                            if (classValue !== undefined) {
                                var classes = classValue.split(' ');
                                inputclass.push($('.' + classes[1]));
                                inclass = $('.' + classes[1]);
                                inclass.each(function(element) {
                                    var label_id = $(this).attr('id');
                                    if ($(this).val() == '') {
                                        if ($(this).val() == '') {
                                            e.preventDefault();
                                            $(this).css('border-color', 'red', 'important');
                                            inputTypeValue = 1;
                                        } else {
                                            $(this).css('border-color', '');
                                            inputTypeValue =
                                                0;
                                        }
                                        return false;
                                    }

                                });
                            }
                        });

                    }
                }

                var fieldValuesByFieldName = {};

                $('input[type="radio"]:checked').each(function() {
                    var fieldName = $(this).attr('class').split(' ')[0];
                    var fieldValue = $(this).val();
                    if (!fieldValuesByFieldName[fieldName]) {
                        fieldValuesByFieldName[fieldName] = [];
                    }

                    fieldValuesByFieldName[fieldName].push(fieldValue);
                });
                var groupedData = {};
                Object.keys(fieldValuesByFieldName).forEach(function(key) {
                    var columnName = key;
                    if (!groupedData[columnName]) {
                        groupedData[columnName] = [];
                    }
                    groupedData[columnName] = groupedData[columnName].concat(fieldValuesByFieldName[
                        key]);
                });
                $.each(fieldValuesByFieldName, function(fieldName, fieldValues) {
                    $.each(fieldValues, function(index, value) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: fieldName + '[]',
                            value: value
                        }).appendTo('form#formConfiguration');
                    });
                });


                if (inputTypeValue == 0 && inputTypeRadioValue == 0) {

                    swal.fire({
                        text: "Do you want to update?",
                        icon: "success",
                        buttonsStyling: false,
                        showCancelButton: true,
                        confirmButtonText: "Yes",
                        cancelButtonText: "No",
                        reverseButtons: true,
                        customClass: {
                            confirmButton: "btn font-weight-bold btn-white-black",
                            cancelButton: "btn font-weight-bold btn-light-danger",
                        }

                    }).then(function(result) {
                        if (result.value == true) {
                            KTApp.block('#myModal_status', {
                                overlayColor: '#000000',
                                state: 'danger',
                                opacity: 0.1,
                                message: 'Fetching...',
                            });
                            document.querySelector('#formConfiguration').submit();
                            KTApp.unblock('#myModal_status');

                        } else {
                            //   location.reload();
                        }
                    });

                } else {
                    return false;
                }
            });
            $("#ckbCheckAll").click(function() {
                var isChecked = $(this).prop('checked');
                $(".checkBoxClass").prop('checked', isChecked);
                var table = $('#client_assigned_list').DataTable();
                for (var i = 0; i < table.page.info().pages; i++) {
                    table.page(i).draw(false); // Switch to page i
                    $(".checkBoxClass").prop('checked', isChecked); // Select checkboxes on the current page
                }
                if ($(this).prop('checked') == true && $('.checkBoxClass:checked').length > 0) {
                    $('#assigneeDropdown').prop('disabled', false);
                    assigneeDropdown();
                } else {
                    $('#assigneeDropdown').prop('disabled', true);

                }
            });

            function handleCheckboxChange() {
                // $('.checkBoxClass').change(function() {
                    var anyCheckboxChecked = $('.checkBoxClass:checked').length > 0;
                    var allCheckboxesChecked = $('.checkBoxClass:checked').length === $('.checkBoxClass')
                        .length;
                    if (allCheckboxesChecked) {
                        $("#ckbCheckAll").prop('checked', $(this).prop('checked'));
                    } else {
                        $("#ckbCheckAll").prop('checked', false);
                    }
                    $('#assigneeDropdown').prop('disabled', !(anyCheckboxChecked || allCheckboxesChecked));
                    if ($(this).prop('checked') == true) {
                    assigneeDropdown();
                    }
                // });
            }

            function attachCheckboxHandlers() {
                $('.checkBoxClass').off('change').on('change', handleCheckboxChange);
            }
               attachCheckboxHandlers();
                table.on('draw', function() {
                    attachCheckboxHandlers();
                });

            function assigneeDropdown() {
               KTApp.block('#assign_div', {
                    overlayColor: '#000000',
                    state: 'danger',
                    opacity: 0.1,
                    message: 'Fetching...',
                });
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    }
                });
                  
                $.ajax({
                    url: "{{ url('qa_production/assignee_drop_down') }}",
                    method: 'POST',
                    data: {
                        clientName: clientName,
                    },
                    success: function(response) {
                       var sla_options = '<option value="">-- Select --</option>';
                        $.each(response.assignedDropDown, function(key, value) {
                            sla_options += '<option value="' + key + '">' + value +
                                '</option>';
                        });
                        $('select[name="assignee_name"]').html(sla_options);
                        KTApp.unblock('#assign_div');
                    },
                });
               
            }

            $('#assigneeDropdown').change(function() {
                assigneeId = $(this).val();
                var checkedRowValues = [];
                $('#client_assigned_list').DataTable().$('input[name="check[]"]:checked').each(function() {
                    var rowData = {
                        name: 'check[]',
                        value: $(this).val()
                    };
                    checkedRowValues.push(rowData);
                });
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    }
                });
                swal.fire({
                    text: "Do you want to assign?",
                    icon: "success",
                    buttonsStyling: false,
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                    customClass: {
                        confirmButton: "btn font-weight-bold btn-white-black",
                        cancelButton: "btn font-weight-bold  btn-light-danger",
                    }

                }).then(function(result) {
                    if (result.value == true) {
                        $.ajax({
                            url: "{{ url('qa_production/sampling_assignee') }}",
                            method: 'POST',
                            data: {
                                assigneeId: assigneeId,
                                checkedRowValues: checkedRowValues,
                                clientName: clientName,
                                subProjectName: subProjectName
                            },
                            success: function(response) {
                                if (response.success == true) {
                                    js_notification('success',
                                        'Assignee Updated Successfully');
                                } else {
                                    js_notification('error', 'Something went wrong');
                                }
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                            },
                        });

                    } else {
                        location.reload();
                    }
                });
            })

            $(document).on('click', '.one', function() {
                window.location.href = "{{ url('#') }}";
            })

            $(document).on('click', '.two', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_pending/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.three', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_hold/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()["parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.four', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_completed/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.five', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_unAssigned/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.six', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_duplicate/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.seven', function() {
                window.location.href = baseUrl + 'qa_production/qa_projects_auto_close/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.eight', function() {
                window.location.href = baseUrl + 'qa_production/qa_rebuttal/' + clientName + '/' +
                    subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })

            $(document).on('change', '#chart_status_start', function() {
                var claimStatus = $(this).val();
                if (claimStatus == "QA_Hold") {
                    $('#qa_hold_reason').css('display', 'block');
                    $('#qa_hold_reason_label').css('display', 'block');
                } else {
                    $('#qa_hold_reason').css('display', 'none');
                    $('#qa_hold_reason_label').css('display', 'none');
                    $('#qa_hold_reason').css('border-color', '');
                    $('#qa_hold_reason').val('');
                }
            })



                    // Exclude fields you don't want to track
                    var excludedFields = ['QA_rework_comments', 'chart_status','coder_rework_status','coder_rework_reason','QA_status_code','QA_sub_status_code','qa_hold_reason','	ce_hold_reason'];
                    var previousValue;
                    $('#formConfiguration').on('focus', 'input:not(.exclude), select:not(.exclude), textarea:not(.exclude)', function() {
                        currentClass = $(this).attr('name').replace(/\[\]$/, '');
                        if (currentClass == 'am_cpt'|| currentClass =='am_icd'){
                            previousValue = $('.'+currentClass+'_hidden').val().trim();
                        } else {
                            previousValue = $(this).val().trim();
                        }
                    }).on('focusout', 'input:not(.exclude), select:not(.exclude), textarea:not(.exclude)', function() {
                        //   var currentValue = $(this).val();
                            var fieldName = $(this).attr('name');
                            var trimmedFiled = $(this).attr('id') !== undefined ? $(this).attr('id') : $(this).attr('class');
                            var trimmedFiled1 = $(this).attr('name').replace(/\[\]$/, '');
                            var formattedValue = trimmedFiled.toUpperCase().replace(/_else_/g, '/').replace(/_/g, ' ');
                            var formattedValue1 = trimmedFiled1.toUpperCase().replace(/_else_/g, '/').replace(/_/g, ' ');
                            if (excludedFields.indexOf(fieldName) === -1) {
                                var currentValue = '';
                                if ($(this).is('input[type="checkbox"]')) {
                                    currentValue = $(this).is(':checked') ? ' Checked '+$(this).closest('label').text().trim() : ' Unchecked '+$(this).closest('label').text().trim();
                                } else if ($(this).is('input[type="radio"]')) {
                                    currentValue = $(this).is(':checked') ? ' Checked '+$(this).closest('label').text().trim() : ' Unchecked '+$(this).closest('label').text().trim();
                                } else if ($(this).is('input[type="date"]')) {
                                    currentValue = $(this).val().trim();
                                } else {
                                    currentValue = $(this).val().trim();
                                }
                                var newLine = '';
                                if ($(this).is('input[type="checkbox"]') || $(this).is('input[type="radio"]')) {
                                    if(previousValue !== currentValue) {
                                        newLine =  formattedValue1 + currentValue;
                                    }
                                } else {
                                    var textAreaValue = $('#QA_rework_comments').val();
                                    var processedText = fieldName.replace('am_', '').toUpperCase();
                                    processedText = processedText.replace('[]', '').toUpperCase();
                                    var errorPreviousValue = [];
                                    if(currentValue != '') {                                  
                                        if (fieldName == 'am_cpt[]'|| fieldName =='am_icd[]') {
                                            var notes = $('.QA_rework_comments').val().trim();
                                            var annexPrevious = previousValue.split(',').map(value => value.trim()); 
                                            annexPrevious = annexPrevious.filter(function(item) {
                                                    return item && item.trim();
                                                });
                                            var annexcurrent = currentValue.split(',').map(value => value.trim());
                                            annexcurrent = annexcurrent.filter(function(item) {
                                                    return item && item.trim();
                                                });
                                        
                                            let notesMap = {};
                                            var annexInfMap = {};
                                        
                                                annexcurrent.forEach(function (value, index) {
                                                        annexInfMap[value] = (annexInfMap[value] || 0)+1 ;
                                                    });
                                            for (var i = 0; i < annexPrevious.length; i++) {
                                                if (annexcurrent[i] !== undefined && annexcurrent[i] !== '') {
                                                    if (annexPrevious[0] !== '' && annexPrevious[i] !== annexcurrent[i]) {
                                                        if (annexPrevious[i].includes('-') && annexcurrent[i].includes('-')) {
                                                            var clientParts = annexPrevious[i].split('-');
                                                            var annexParts = annexcurrent[i].split('-');
                                                            const clientPart0 = clientParts[0].trim(); 
                                                            const annexPart0 = annexParts[0].trim(); 
                                                            const part1 = clientParts[1].trim(); 
                                                            const part2 = annexParts[1].trim(); 
                                                            if(part1 != part2) {
                                                                notesMap[part1] = processedText + ' - modifier ' +  part1 + ' changed to ' +  part2 + ' belongs to ' +  clientPart0;
                                                                errorPreviousValue[part1] = processedText + ' - modifier ' + part1;
                                                            }
                                                                var noteLines =  notes.split('\n');
                                                                for (var j = 0; j < noteLines.length; j++) {
                                                                    if(noteLines[j].includes(processedText + ' - modifier ' +  part1)) {
                                                                        // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                                                                        // notes = noteLines; 
                                                                        noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                        notes = noteLines.join('\n');  

                                                                        var lines = notes.split('\n');  
                                                                        var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier ' +  part1));
                                                                        if (matchedLine) {
                                                                            notes = lines.filter(lines => lines !== matchedLine).join('\n');
                                                                        }                                    
                                                                                                        
                                                                    }
                                                                }
                                                        
                                                            if(clientPart0 != annexPart0) {
                                                                notesMap[clientPart0] = processedText + ' - ' + clientPart0 + ' changed to ' + annexPart0;
                                                                errorPreviousValue[clientPart0] = processedText + ' - ' + clientPart0;
                                                            
                                                            }
                                                            var lines1 = notes.split('\n');
                                                            var matchedLine = lines1.find(lines => lines.includes(processedText + ' - ' + clientPart0));
                                                            if (matchedLine) {
                                                                notes = lines1.filter(lines => lines !== matchedLine).join('\n');
                                                            }
                                                            var noteLines =  notes.split('\n');
                                                            for (var j = 0; j < noteLines.length; j++) {
                                                                if(noteLines[j].includes(processedText + ' - ' + clientPart0)){
                                                                    // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                                                                    // notes = noteLines;
                                                                    noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                    notes = noteLines.join('\n');                              
                                                                }
                                                            }
                                                        } else if (annexPrevious[i].includes('-') && !annexcurrent[i].includes('-')) {
                                                            var clientParts = annexPrevious[i].split('-');
                                                            const client1 = clientParts[0].trim(); 
                                                            const annex1 =annexcurrent[i].trim(); 
                                                            const cpart1 = clientParts[1].trim(); 
                                                            
                                                            notesMap[cpart1] = processedText + ' - modifier ' +  cpart1 + ' removed belongs to ' + client1;
                                                            errorPreviousValue[cpart1] = processedText + ' - modifier ' + cpart1; 
                                                            var lines = notes.split('\n');
                                                            var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier ' +  cpart1));
                                                            if (matchedLine) {
                                                                notes = lines.filter(lines => lines !== matchedLine).join('\n');
                                                            }
                                                            if(client1 != annex1) {
                                                                notesMap[i] = processedText + ' - ' + client1 + ' changed to ' + annex1;
                                                                errorPreviousValue[client1] = processedText + ' - ' + client1;
                                                            }
                                                            var noteLines =  notes.split('\n');
                                                            for (var j = 0; j < noteLines.length; j++) {
                                                                if(noteLines[j].includes(processedText + ' - ' + client1)){
                                                                    // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                                                                    // notes = noteLines;
                                                                    noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                    notes = noteLines.join('\n');                                                                                          
                                                                }
                                                            }
                                                        } else if (!annexPrevious[i].includes('-') && annexcurrent[i].includes('-')) {
                                                            var parts = annexcurrent[i].split('-');
                                                            const client2 = annexPrevious[i].trim(); 
                                                            const annex2 = parts[0].trim();
                                                            const apart1 = parts[0].trim(); 
                                                            const apart2 = parts[1].trim(); 
                                                            notesMap[apart1] = processedText + ' - modifier ' +  parts[1] + ' added to ' +  client2;
                                                            errorPreviousValue[client2] = ' added to ' + client2;
                                                            var noteLines =  notes.split('\n');
                                                            for (var j = 0; j < noteLines.length; j++) {
                                                                if(noteLines[j].includes(processedText + ' - modifier ')){
                                                                    // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                                                                    // notes = noteLines;   
                                                                    noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                    notes = noteLines.join('\n');                              
                                                                }
                                                            }
                                                            var lines = notes.split('\n');
                                                            var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier '));
                                                            if (matchedLine) {
                                                                notes = lines.filter(lines => lines !== matchedLine).join('\n');
                                                            }
                                                            if(client2 != annex2) {
                                                                notesMap[i] = processedText + ' - ' + client2 + ' changed to ' + annex2;
                                                                errorPreviousValue[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i];
                                                            }
                                                            var noteLines =  notes.split('\n');
                                                            for (var j = 0; j < noteLines.length; j++) {
                                                                    if(noteLines[j].includes(processedText + ' - ' + client2)){
                                                                        // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                                                                        // notes = noteLines;
                                                                        noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                        notes = noteLines.join('\n');                                
                                                                    }
                                                            }
                                                            // var lines = notes.split('\n');
                                                            // var matchedLine = lines.find(lines => lines.includes(processedText + ' - ' + client2));
                                                            // if (matchedLine) {
                                                            //     notes = lines.filter(lines => lines !== matchedLine).join('\n');
                                                            // }
                                                        } else {
                                                            notesMap[i] = processedText + ' - ' + annexPrevious[i] + ' changed to ' + annexcurrent[i];
                                                            errorPreviousValue[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i];
                                                            var lines =  notes.split('\n');
                                                            var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));
                                                            var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(annexPrevious[i]) );
                                                        
                                                            if (matchedLine || matchedLine1) {
                                                                lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                                                notes = lines.join('\n');
                                                            }
                                                            var noteLines =  notes.split('\n');
                                                            for (var j = 0; j < noteLines.length; j++) {
                                                                if(noteLines[j].includes(processedText + ' - ') && noteLines[j].includes(annexPrevious[i])){
                                                                    noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                    notes = noteLines.join('\n');                                               
                                                                }
                                                            }
                                                        }
                                                    
                                                        var lines =  notes.split('\n');
                                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));
                                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(annexPrevious[i]) );
                                                    
                                                        if (matchedLine || matchedLine1) {
                                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                                            notes = lines.join('\n');
                                                        }
                                                        var noteLines =  notes.split('\n');
                                                        for (var j = 0; j < noteLines.length; j++) {
                                                            if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])){
                                                                noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                                                                notes = noteLines;                                          
                                                            }
                                                        }
                                                    } else {
                                                        var lines = notes.split('\n');
                                                        
                                                        if (annexPrevious[i].includes('-')) {
                                                            var clientParts = annexPrevious[i].split('-');
                                                            var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0]));
                                                            var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1]));
                                                            var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to '));
                                                            var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                                                            var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                                                            if (matchedLine || matchedLine1 || matchedLine2 || matchedLine2 || matchedLine3 || matchedLine4) {
                                                                lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                                                                notes = lines.join('\n');
                                                            }
                                                        } else {
                                                            var clientParts = annexcurrent[i].split('-');
                                                            var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));
                                                            var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1]));
                                                            var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to '));
                                                            var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                                                            var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                                                            if (matchedLine || matchedLine1 || matchedLine2 || matchedLine2 || matchedLine3 || matchedLine4) {
                                                                lines = lines.filter(line => line !== matchedLine && line !== matchedLine1  && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                                                                notes = lines.join('\n');
                                                            }
                                                            var noteLines =  notes.split('\n');
                                                            for (var j = 0; j < noteLines.length; j++) {
                                                                if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])) {
                                                                    // noteLines = noteLines.filter((item, index) => index !== j).join('\n');
                                                                    // notes = noteLines;
                                                                    noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                    notes = noteLines.join('\n');                                                          
                                                                }    
                                                            }
                                                        }
                                                        
                                                    }
                                                    if (annexInfMap[annexcurrent[i]] > 0) {
                                                        annexInfMap[annexcurrent[i]]--;
                                                        if (annexInfMap[annexcurrent[i]] === 0) {
                                                            delete annexInfMap[annexcurrent[i]];
                                                        }
                                                    }
                                                } else {
                                                    var lines = notes.split('\n');
                                                    if (annexPrevious[i].includes('-')) {
                                                        var clientParts = annexPrevious[i].split('-');
                                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0])); 
                                                        var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to ')); 
                                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); 
                                                        var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                                                        var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                                                        if (matchedLine || matchedLine1 || matchedLine2 || matchedLine3 || matchedLine4) {
                                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                                                            notes = lines.join('\n');
                                                        }
                                                    } else {
                                                        var lines =  notes.split('\n');
                                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));
                                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(annexPrevious[i]) );
                                                    
                                                        if (matchedLine || matchedLine1) {
                                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                                            notes = lines.join('\n');
                                                        }
                                                        var noteLines =  notes.split('\n');
                                                        for (var j = 0; j < noteLines.length; j++) {
                                                            if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])){
                                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                notes = noteLines.join('\n');                                               
                                                            }
                                                        }    
                                                    }   
                                                    if(annexcurrent.length > 1 && annexcurrent[0] == ''){
                                                        notesMap[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i] + ' removed';
                                                    } else if(annexcurrent[0] !== '') {
                                                        notesMap[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i] + ' removed';
                                                    } else {
                                                        // var lines = notes.split('\n');
                                                        // for (var j = 0; j < lines.length; j++) {
                                                        //     var matchedLine = lines.find(line => line.includes(processedText )); 
                                                        //         notes = lines.filter(line => line !== matchedLine).join('\n');
                                                        // }
                                                            var lines = notes.split('\n');
                                                            if (annexPrevious[i].includes('-')) {
                                                                var clientParts = annexPrevious[i].split('-');
                                                                var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0]));
                                                                var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to ')); 
                                                                var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); 
                                                                var matchedLine3 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'removed '));
                                                                var matchedLine4 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1] + 'added '));
                                                                if (matchedLine || matchedLine1 || matchedLine2 || matchedLine3) {
                                                                    lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2 && line !== matchedLine3 && line !== matchedLine4);
                                                                    notes = lines.join('\n');
                                                                }
                                                            } else { 
                                                                var lines =  notes.split('\n');
                                                                var matchedLine = lines.find(line => line.includes(processedText + ' - ' + annexPrevious[i]));
                                                                var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(annexPrevious[i]) );
                                                            
                                                                if (matchedLine || matchedLine1) {
                                                                    lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                                                    notes = lines.join('\n');
                                                                }
                                                                var noteLines =  notes.split('\n');
                                                                for (var j = 0; j < noteLines.length; j++) {
                                                                    if(noteLines[j].includes(processedText + ' - ' + annexPrevious[i])){
                                                                        noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                                        notes = noteLines.join('\n');                                               
                                                                    }
                                                                }       
                                                            }          
                                                    }
                                                    errorPreviousValue[annexPrevious[i]] = processedText + ' - ' + annexPrevious[i];
                                                }
                                            }

                                            for (var key in annexInfMap) {
                                                if (annexInfMap.hasOwnProperty(key) && annexInfMap[key] > 0) {
                                                    if(key && (annexPrevious[0] !== '')) {
                                                        notesMap[key] = processedText + ' - ' + key + ' added';
                                                        var lines = notes.split('\n');
                                                        var matchedLine = lines.find(line => line.includes(notesMap[key]));
                                                        if (matchedLine) {
                                                            notes = lines.filter(line => line !== matchedLine).join('\n');
                                                        }
                                                    }
                                                } 
                                            }
                                            annexPrevious.forEach(function (value) {
                                                let combinedArray = Object.values(errorPreviousValue);
                                                let filteredArray = combinedArray.filter(item => item !== null && item !== '');
                                                if (value.includes('-')) {
                                                    var clientParts = value.split('-');
                                                    clientParts.forEach(function(innerValue){
                                                        if (notesMap[innerValue]) { 
                                                            var lines = notes.split('\n');
                                                            if (lines.includes(filteredArray[innerValue])) {
                                                            var matchedLine = lines.find(line => line.includes(filteredArray[innerValue]));
                                                                if (matchedLine !== undefined) {
                                                                    notes = notes.replace(matchedLine, notesMap[innerValue]);
                                                                } else {
                                                                    notes += '\n' + notesMap[innerValue];
                                                                }
                                                            } else {
                                                                if (notes === "") {
                                                                    notes += notesMap[innerValue];
                                                                } else {
                                                                    notes += '\n' + notesMap[innerValue];
                                                                }
                                                            }
                                                            delete notesMap[innerValue];
                                                    }
                                                    })
                                                
                                                } else {
                                                    if (notesMap[value]) { 
                                                        var lines = notes.split('\n');
                                                        if (lines.includes(filteredArray[value])) {
                                                        var matchedLine = lines.find(line => line.includes(filteredArray[value]));
                                                            if (matchedLine !== undefined) {
                                                                notes = notes.replace(matchedLine, notesMap[value]);
                                                            } else {
                                                                notes += '\n' + notesMap[value];
                                                            }
                                                        } else {
                                                            if (notes === "") {
                                                                notes += notesMap[value];
                                                            } else {
                                                                notes += '\n' + notesMap[value];
                                                            }
                                                        }
                                                        delete notesMap[value];
                                                    }
                                                }
                                            });
                                            for (var key in notesMap) {
                                                if (notesMap.hasOwnProperty(key)) {
                                                    notes += '\n' + notesMap[key];
                                                }
                                            }
                                            
                                            var notes1 = notes.split('\n').filter(line => line.trim() !== '');
                                            var matchedLine = notes1.find(line => line.includes(processedText + ' - ') && line.includes(' added') );
                                            if (matchedLine !== undefined && !matchedLine.includes(' added to')) {
                                                let modifiedString = matchedLine.replace(processedText + ' - ', '').replace(' added', '');
                                                var notes2 = textAreaValue.split('\n').filter(line => line.includes(processedText + ' - '));
                                                if (!annexcurrent.includes(modifiedString) || notes2.length > annexcurrent.length) {
                                                    notes1 = notes1.filter(line => line !== matchedLine);
                                                    notes = notes1.join('\n');
                                                }                                
                                            }

                                            var noteLines11 =  notes.split('\n').filter(line => line.trim() !== '');
                                            var filteredNoteLines = [];
                                            var filteredNoteLines1 = [];
                                            for (var q = 0; q < noteLines11.length; q++) {                                         
                                                if(noteLines11[q].includes(processedText + ' - ') && noteLines11[q].includes(' added') && !noteLines11[q].includes(' added to')){                                  
                                                    let modifiedString = noteLines11[q].replace(processedText + ' - ', '').replace(' added', '');
                                                    if (!annexcurrent.includes(modifiedString)) {
                                                        noteLines11 = noteLines11.filter(line => line !== noteLines11[q]);
                                                        notes = noteLines11.join('\n');  
                                                    }                                           
                                                }                                        
                                                if (annexcurrent.length == 0 && noteLines11[q].includes(processedText + ' - ')) {
                                                    annexPrevious.forEach(function (item,value) {                                        
                                                        filteredNoteLines.push(processedText + ' - ' + item + ' removed');
                                                    });
                                                } else {
                                                
                                                    if(noteLines11[q].includes(processedText + ' - ')) {
                                                    
                                                        filteredNoteLines1.push(noteLines11[q]);
                                                    } else {
                                                    filteredNoteLines.push(noteLines11[q]);
                                                    }
                                                }
                                            }      
                                        
                                            // noteLines11 = noteLines11.filter(function(item) {
                                            //     return filteredNoteLines1.indexOf(item) === -1;
                                            // }); 
                                            //  noteLines11 = filteredNoteLines;
                                            //  notes = noteLines11.join('\n');
                                            
                                            let noteLines1 = notes.trim().split('\n');
                                            let uniqueNotes = Array.from(new Set(noteLines1));
                                            let finalNotes = uniqueNotes.join('\n');
                                            newLine = finalNotes;
                                        } else {
                                            newLine = previousValue != '' ? formattedValue1 + ' '+previousValue + ' Changed to ' + currentValue : formattedValue1 +' ' + currentValue + ' added';
                                        }
                                        
                                    } else if(previousValue !== currentValue && currentValue == '') {
                                        if(currentClass == 'am_cpt'|| currentClass =='am_icd') {
                                            newLine = previousValue != '' ? processedText + ' - '+previousValue+ ' removed' : processedText +' ' + currentValue + ' added';
                                        } else {
                                            newLine = previousValue != '' ? formattedValue1 + ' '+previousValue+ ' removed' : formattedValue1 +' ' + currentValue + ' added';
                                        }
                                    }
                                }
                                if(currentClass == 'am_cpt'|| currentClass =='am_icd') {
                                        var annexPrevious = previousValue.split(',').map(value => value.trim()); 
                                            annexPrevious = annexPrevious.filter(function(item) {
                                                return item && item.trim();
                                            });
                                        var annexcurrent = currentValue.split(',').map(value => value.trim());
                                            annexcurrent = annexcurrent.filter(function(item) {
                                                return item && item.trim();
                                            });
                                        var noteLines11 =  textAreaValue.split('\n').filter(line => line.trim() !== '');                           
                                        var filteredNoteLines = [];
                                        var filteredNoteLines1 = [];
                                        for (var q = 0; q < noteLines11.length; q++) {                                     
                                            if(noteLines11[q].includes(processedText + ' - ') && noteLines11[q].includes(' added') && !noteLines11[q].includes(' added to')){                                  
                                                let modifiedString = noteLines11[q].replace(processedText + ' - ', '').replace(' added', '');
                                                if (!annexcurrent.includes(modifiedString)) {
                                                    noteLines11 = noteLines11.filter(line => line !== noteLines11[q]);
                                                    notes = noteLines11.join('\n');  
                                                }                                           
                                            }
                                        }      
                                
                                        noteLines11 = noteLines11.filter(function(item) {
                                            return filteredNoteLines1.indexOf(item) === -1;
                                        });
                                        
                                        noteLines11 = filteredNoteLines;
                                        textAreaValue = noteLines11.join('\n');

                                        var notes1 = textAreaValue.split('\n');
                                        let modifiedString;
                                        var matchedLine = notes1.find(line => line.includes(processedText + ' - ') && line.includes(' added') && !line.includes(' added to'));
                                        if (matchedLine !== undefined && !matchedLine.includes(' added to')) {
                                            modifiedString = matchedLine.replace(processedText + ' - ', '').replace(' added', '');                         
                                        }
                                        
                                        if (modifiedString !== undefined && textAreaValue.includes(modifiedString) && !annexcurrent.includes(modifiedString)) {
                                                var lines = textAreaValue.split('\n');
                                                notes1 = lines.filter(line => line !== matchedLine);
                                                textAreaValue = notes1.join('\n');                                                 
                                        } else {
                                            if(textAreaValue == "") {                                        
                                                textAreaValue += newLine;
                                            } else {
                                                var textAreaValueLines = textAreaValue.split('\n');
                                                
                                                let combinedArray = Object.values(errorPreviousValue);
                                                let filteredArray = combinedArray.filter(item => item !== null && item !== '');
                                                if( filteredArray.length >= 1) {
                                                    for (var j = 0; j < filteredArray.length; j++) {
                                                        if (jQuery.inArray(filteredArray[j], textAreaValueLines)) {
                                                            var matchedLine = textAreaValueLines.find(line => line.includes(processedText) && line.includes(filteredArray[j]));
                                                            if (matchedLine) {                         
                                                                textAreaValue = textAreaValue.replace(matchedLine, newLine);
                                                            } else {
                                                                    newLine = '\n'+newLine;
                                                                    textAreaValue += newLine;
                                                            }
                                                        } else {
                                                                newLine = '\n'+newLine;
                                                            textAreaValue += newLine;
                                                        }
                                                    }   
                                                } else {
                                                    var textAreaValueLines = textAreaValue.split('\n');
                                                    for(var a=0;a < textAreaValueLines.length; a++) {
                                                        var matchedLine = textAreaValueLines[a].includes(processedText);
                                                        // var matchedLine = textAreaValueLines.find(line => line.includes(processedText));
                                                        if (matchedLine) {
                                                            textAreaValue = textAreaValue.replace(textAreaValueLines[a], newLine);
                                                        } else {
                                                            newLine = '\n'+newLine;
                                                            textAreaValue += newLine;
                                                        }
                                                    }
                                                
                                                }    
                                        }
                                    }
                                    // textAreaValue += newLine;
                                } else {
                                    var textAreaValue = $('#QA_rework_comments').val();
                                    if (textAreaValue.includes(previousValue) && previousValue !== currentValue) {
                                        var lines = textAreaValue.split('\n');
                                        var matchedLine = lines.find(line => line.includes(previousValue) && line.includes(formattedValue1));  
                                        textAreaValue = textAreaValue.replace(matchedLine, newLine);
                                    } else {
                                        if(textAreaValue == "" && previousValue !== currentValue) {
                                            textAreaValue += newLine;
                                        } else {
                                            if(previousValue !== currentValue) {
                                                newLine = '\n'+newLine;
                                                textAreaValue += newLine;
                                            }
                                        }
                                    }
                                }
                                let textAreaValue1 = textAreaValue.trim().split('\n');
                                let uniqueNotes1 = Array.from(new Set(textAreaValue1));
                                let finalNotes1 = uniqueNotes1.join('\n');
                                $('#QA_rework_comments').val(finalNotes1);
                            }

                    });
                    
                    
                   
                    function handleBlurEvent(clientClass, annexClass) {
                        var clientInf = $(clientClass).val().split(',').map(value => value.trim()); 
                           clientInf = clientInf.filter(function(item) {
                                return item && item.trim();
                            });
                        var annexInf = $(annexClass).val().split(',').map(value => value.trim()); 
                        annexInf = annexInf.filter(function(item) {
                                return item && item.trim();
                            });
                        let notesMap = {};
                        var previousValue = [];
                        var processedText = clientClass.replace('.', '').toUpperCase();
                        var annexInfMap = {};
                        var notes = $('.annex_qa_trends').val().trim();

                        annexInf.forEach(function (value, index) {
                            annexInfMap[value] = (annexInfMap[value] || 0)+1 ;
                        });
                                 
                        for (var i = 0; i < clientInf.length; i++) {
                            if (annexInf[i] !== undefined && annexInf[i] !== '') {
                                if (clientInf[0] !== '' && clientInf[i] !== annexInf[i]) {                                    
                                    if (clientInf[i].includes('-') && annexInf[i].includes('-')) {
                                        var clientParts = clientInf[i].split('-');
                                        var annexParts = annexInf[i].split('-');
                                        const clientPart0 = clientParts[0].trim(); 
                                        const annexPart0 = annexParts[0].trim(); 
                                        const part1 = clientParts[1].trim(); 
                                        const part2 = annexParts[1].trim(); 
                                        if(part1 != part2) {
                                            notesMap[part1] = processedText + ' - modifier ' +  part1 + ' changed to ' +  part2 + ' belongs to ' +  clientPart0;
                                            previousValue[part1] = processedText + ' - ' + part1;
                                        }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - modifier ' +  part1)) {
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');      
                                                var lines = notes.split('\n');
                                                var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier ' +  part1));
                                                if (matchedLine) {
                                                    notes = lines.filter(lines => lines !== matchedLine).join('\n');
                                                }             
                                            }
                                        }
                                        if(clientPart0 != annexPart0) {
                                            notesMap[clientPart0] = processedText + ' - ' + clientPart0 + ' changed to ' + annexPart0;
                                            previousValue[clientPart0] = processedText + ' - ' + clientPart0;                                          
                                        }
                                        var lines1 = notes.split('\n');
                                         var matchedLine = lines1.find(lines => lines.includes(processedText + ' - ' + clientPart0));
                                         if (matchedLine) {
                                            notes = lines1.filter(lines => lines !== matchedLine).join('\n');
                                         }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                                if(noteLines[j].includes(processedText + ' - ' + clientPart0)) {
                                                    noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                    notes = noteLines.join('\n');                                
                                                }
                                        }
                                    } else if (clientInf[i].includes('-') && !annexInf[i].includes('-')) {
                                        var clientParts = clientInf[i].split('-');
                                        const client1 = clientParts[0].trim(); 
                                        const annex1 =annexInf[i].trim(); 
                                        const cpart1 = clientParts[1].trim();
                                        notesMap[cpart1] = processedText + ' - modifier ' +  cpart1 + ' removed belongs to ' + client1;
                                        previousValue[cpart1] = processedText + ' - ' + cpart1;

                                        var lines = notes.split('\n');
                                        var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier ' +  cpart1));
                                        if (matchedLine) {
                                            notes = lines.filter(lines => lines !== matchedLine).join('\n');
                                        } 
                                        if(client1 !== annex1) {
                                            notesMap[i] = processedText + ' - ' + client1 + ' changed to ' + annex1;
                                            previousValue[client1] = processedText + ' - ' + client1;
                                        }

                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - ' + client1)){
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');                                                                                       
                                            }
                                        }
                                    } else if (!clientInf[i].includes('-') && annexInf[i].includes('-')) {
                                        var parts = annexInf[i].split('-');
                                        const client2 = clientInf[i].trim(); 
                                        const annex2 = parts[0].trim();
                                        const apart1 = parts[0].trim(); 
                                        const apart2 = parts[1].trim(); 
                                        notesMap[apart1] = processedText + ' - modifier ' +  parts[1] + ' added to ' +  client2;
                                        previousValue[client2] = processedText + ' - ' + client2;
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - modifier ')){
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');                                      
                                            }
                                        }
                                        var lines = notes.split('\n');
                                        var matchedLine = lines.find(lines => lines.includes(processedText + ' - modifier '));
                                        if (matchedLine) {
                                            notes = lines.filter(lines => lines !== matchedLine).join('\n');
                                        }
                                        if(client2 != annex2) {
                                            notesMap[i] = processedText + ' - ' + client2 + ' changed to ' + annex2;
                                            previousValue[clientInf[i]] = processedText + ' - ' + clientInf[i];
                                        }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                                if(noteLines[j].includes(processedText + ' - ' + client2)){
                                                    noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                    notes = noteLines.join('\n');                                    
                                                }
                                        }
                                    } else {
                                        notesMap[i] = processedText + ' - ' + clientInf[i] + ' changed to ' + annexInf[i];
                                        previousValue[clientInf[i]] = processedText + ' - ' + clientInf[i];
                                        var lines =  notes.split('\n');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientInf[i]));
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(clientInf[i]) );
                                    
                                        if (matchedLine || matchedLine1) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                            notes = lines.join('\n');
                                        }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - ' + clientInf[i])){
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');                                               
                                            }
                                        }
                                    }
                                        var lines =  notes.split('\n');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientInf[i]));
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(clientInf[i]) );
                                    
                                        if (matchedLine || matchedLine1) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                            notes = lines.join('\n');
                                        }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - ' + clientInf[i])){
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');                                               
                                            }
                                        }                                  
                                } else {
                                    var lines = notes.split('\n');
                                    if (clientInf[i].includes('-')) {
                                        var clientParts = clientInf[i].split('-');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0])); 
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); 
                                        var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to '));
                                   
                                        if (matchedLine || matchedLine1 || matchedLine2) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1  && line !== matchedLine2);
                                            notes = lines.join('\n');
                                        }
                                    } else {
                                        var clientParts = annexInf[i].split('-');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientInf[i]));
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(clientInf[i]) );
                                        var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to ')); 
                                        if (matchedLine || matchedLine1 || matchedLine2) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1  && line !== matchedLine2);
                                            notes = lines.join('\n');
                                        }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - ' + clientInf[i])){
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');                                               
                                            }
                                        }      
                                   }                                                                
                                }
                                if (annexInfMap[annexInf[i]] > 0) {
                                    annexInfMap[annexInf[i]]--;
                                    if (annexInfMap[annexInf[i]] === 0) {
                                        delete annexInfMap[annexInf[i]];
                                    }
                                }
                            
                            } else {
                                       var lines = notes.split('\n');
                                    if (clientInf[i].includes('-')) {
                                        var clientParts = clientInf[i].split('-');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0]));
                                        var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to '));
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); 
                                        if (matchedLine || matchedLine1 || matchedLine2) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2);
                                            notes = lines.join('\n');
                                        }
                                    } else {
                                        var lines =  notes.split('\n');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientInf[i]));
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(clientInf[i]) );
                                    
                                        if (matchedLine || matchedLine1) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                            notes = lines.join('\n');
                                        }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - ' + clientInf[i])){
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');                                               
                                            }
                                        }     
                                   }        
                                if(annexInf.length > 1 && annexInf[0] == ''){
                                    notesMap[clientInf[i]] = processedText + ' - ' + clientInf[i] + ' removed';
                                } else if(annexInf[0] !== '') {
                                    notesMap[clientInf[i]] = processedText + ' - ' + clientInf[i] + ' removed';
                                } 
                                else {
                                      var lines = notes.split('\n');
                                       if (clientInf[i].includes('-')) {
                                        var clientParts = clientInf[i].split('-');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientParts[0]));
                                        var matchedLine2 = lines.find(line => line.includes(processedText + ' - ' + clientParts[0] + ' changed to '));
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ' + clientParts[1])); 
                                        if (matchedLine || matchedLine1 || matchedLine2) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1 && line !== matchedLine2);
                                            notes = lines.join('\n');
                                        }
                                    } else {
                                        var lines =  notes.split('\n');
                                        var matchedLine = lines.find(line => line.includes(processedText + ' - ' + clientInf[i]));
                                        var matchedLine1 = lines.find(line => line.includes(processedText + ' - modifier ') && line.includes(clientInf[i]) );
                                    
                                        if (matchedLine || matchedLine1) {
                                            lines = lines.filter(line => line !== matchedLine && line !== matchedLine1);
                                            notes = lines.join('\n');
                                        }
                                        var noteLines =  notes.split('\n');
                                        for (var j = 0; j < noteLines.length; j++) {
                                            if(noteLines[j].includes(processedText + ' - ' + clientInf[i])){
                                                noteLines = noteLines.filter(line => line !== noteLines[j]);
                                                notes = noteLines.join('\n');                                               
                                            }
                                        }   
                                   }               
                                }
                                previousValue[clientInf[i]] = processedText + ' - ' + clientInf[i];                            
                            }
                        }
                      
                        for (var key in annexInfMap) {
                            if (annexInfMap.hasOwnProperty(key) && annexInfMap[key] > 0) {
                                if(key && (clientInf[0] !== '')) {
                                    notesMap[key] = processedText + ' - ' + key + ' added';
                                    var lines = notes.split('\n');
                                    var matchedLine = lines.find(line => line.includes(notesMap[key]));
                                    if (matchedLine) {
                                        notes = lines.filter(line => line !== matchedLine).join('\n');
                                    }
                                }
                            } 
                        }
                         clientInf.forEach(function (value) {
                            if (notesMap[value]) { 
                                var lines = notes.split('\n');
                                if (lines.includes(previousValue[value])) {
                                     var matchedLine = lines.find(line => line.includes(previousValue[value]));
                                    if (matchedLine !== undefined) {
                                        notes = notes.replace(matchedLine, notesMap[value]);
                                    } else {
                                        notes += '\n' + notesMap[value];
                                    }
                                } else {                                    
                                    if (notes === "") {
                                        notes += notesMap[value];
                                    } else {
                                        notes += '\n' + notesMap[value];
                                    }
                                }
                                delete notesMap[value];
                            }
                        });

                        // Add remaining notes for new additions
                        for (var key in notesMap) {
                            var lines = notes.split('\n');
                            if (notesMap.hasOwnProperty(key)) {
                                notes += '\n' + notesMap[key];
                            }
                        }
                            var notes1 = notes.split('\n').filter(line => line.trim() !== '');
                            var matchedLine = notes1.find(line => line.includes(processedText + ' - ') && line.includes(' added') );
                            if (matchedLine !== undefined && !matchedLine.includes(' added to')) {
                                let modifiedString = matchedLine.replace(processedText + ' - ', '').replace(' added', '');
                                if (!annexInf.includes(modifiedString)) {
                                    notes1 = notes1.filter(line => line !== matchedLine);
                                    notes = notes1.join('\n');
                                }                        
                            }

                            var noteLines11 =  notes.split('\n').filter(line => line.trim() !== '');
                            var filteredNoteLines = [];
                            for (var q = 0; q < noteLines11.length; q++) {                               
                                if(noteLines11[q].includes(processedText + ' - ') && noteLines11[q].includes(' added') && !noteLines11[q].includes(' added to')){                                  
                                    let modifiedString = noteLines11[q].replace(processedText + ' - ', '').replace(' added', '');
                                    if (!annexInf.includes(modifiedString)) {
                                        noteLines11 = noteLines11.filter(line => line !== noteLines11[q]);
                                        notes = noteLines11.join('\n');  
                                    }                                           
                                }  
                                
                                if (annexInf.length == 0 && noteLines11[q].includes(processedText + ' - ')) {
                                    clientInf.forEach(function (item,value) {                                        
                                      filteredNoteLines.push(processedText + ' - ' + item + ' removed');
                                    });
                                } else {
                                    filteredNoteLines.push(noteLines11[q]);
                                }
                            }      
                            noteLines11 = filteredNoteLines;
                            notes = noteLines11.join('\n');

                        let noteLines1 = notes.trim().split('\n');
                        let uniqueNotes = Array.from(new Set(noteLines1));
                        let finalNotes = uniqueNotes.join('\n');
                        $('.annex_qa_trends').val(finalNotes);
                    }
                    $('.am_cpt').on('blur', function () {
                         handleBlurEvent('.cpt', '.am_cpt');
                    });

                    $('.am_icd').on('blur', function () {
                         handleBlurEvent('.icd', '.am_icd');
                    });
                    function toggleCoderTrends() {
                        var hasAMFields = $('.am_cpt').length > 0 && $('.am_icd').length > 0;
                        if (hasAMFields) {
                            $('.trends_div').show();
                        } else {
                            $('.trends_div').hide();
                        }
                    }
                    // toggleCoderTrends();
                    $(document).on('click', '#filter_clear', function(e) {
                        window.location.href = baseUrl + 'qa_production/qa_projects_assigned/' + clientName + '/' + subProjectName +
                            "?parent=" +
                            getUrlVars()[
                                "parent"] +
                            "&child=" + getUrlVars()["child"];
                    })
                        $(document).on('click', '#assign_export', function(e) {
                            var formData = $('#formSearch').serialize();
                            var chartStatus = "CE_Completed";
                            var recordStatusVal = "Assigned";
                            formData += '&chart_status=' + chartStatus;
                            formData += '&clientName=' + clientName;
                            formData += '&subProjectName=' + subProjectName;
                            formData += '&recordStatusVal=' + recordStatusVal;
                            $.ajaxSetup({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                        'content')
                                }
                            });
                            KTApp.block('#export_div', {
                                overlayColor: '#000000',
                                state: 'danger',
                                opacity: 0.1,
                                message: 'Fetching...',
                            });
                            $.ajax({
                                    url: "{{ url('qa_production/quality_export') }}",
                                    method: 'POST',
                                    data: formData,
                                    xhrFields: {
                                        responseType: 'blob'  // This is crucial for downloading Excel
                                    },
                                    success: function(response, status, xhr) {  // Correct order of parameters
                                        var filename = "";
                                        var disposition = xhr.getResponseHeader('Content-Disposition');
                                        if (disposition && disposition.indexOf('attachment') !== -1) {
                                            var matches = /filename[^;=\n]*=([^;\n]*)/.exec(disposition);                            
                                            if (matches != null && matches[1]) {
                                                // Trim any extra spaces or quotes around the filename
                                                filename = matches[1].trim().replace(/^"|"$/g, '');
                                            }
                                        }

                                        var blob = new Blob([response], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                                        var link = document.createElement('a');
                                        link.href = window.URL.createObjectURL(blob);
                                        link.download = filename || 'export.xlsx';
                                        document.body.appendChild(link);
                                        link.click();
                                        document.body.removeChild(link);
                                        KTApp.unblock('#export_div');
                                    },
                                    error: function(response) {
                                        console.log('Error generating Excel file', response);
                                    }
                            });

                        });
        })

        function updateTime() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            var startTime = new Date(startTime_db).getTime();
            var elapsedTimeMs = new Date().getTime() - startTime;
            var elapsedHours = Math.floor(elapsedTimeMs / (1000 * 60 * 60));
            var remainingMinutes = Math.floor((elapsedTimeMs % (1000 * 60 * 60)) / (1000 * 60));
            elapsedHours = (elapsedHours < 10 ? "0" : "") + elapsedHours;
            remainingMinutes = (remainingMinutes < 10 ? "0" : "") + remainingMinutes;
            document.getElementById("elapsedTime").innerHTML = elapsedHours + " : " + remainingMinutes;
            setTimeout(updateTime, 1000);
        }
        updateTime();
    </script>
@endpush
