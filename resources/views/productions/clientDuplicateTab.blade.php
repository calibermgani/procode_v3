@extends('layouts.app3')
@php
use Carbon\Carbon;
@endphp
@section('content')
    <div class="content d-flex flex-column flex-column-fluid">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid p-0">
                <div class="card card-custom custom-card">
                    <div class="card-body p-0">
                        @php
                             $empDesignation = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail']['user_hrdetails'] &&  Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']  !=null ? Session::get('loginDetails')['userDetail']['user_hrdetails']['current_designation']: "";
                             $loginEmpId = Session::get('loginDetails') &&  Session::get('loginDetails')['userDetail'] && Session::get('loginDetails')['userDetail']['emp_id'] !=null ? Session::get('loginDetails')['userDetail']['emp_id']:"";
                        @endphp
                          <div class="card-header border-0 px-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- <span class="svg-icon svg-icon-primary svg-icon-lg ">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="16" fill="currentColor"
                                                class="bi bi-arrow-left project_header_row" viewBox="0 0 16 16"
                                                style="width: 1.05rem !important;color: #000000 !important;margin-left: 4px !important;">
                                                <path fill-rule="evenodd"
                                                    d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8" />
                                            </svg>
                                        </span> --}}
                                        <span class="project_header" style="margin-left: 4px !important;">Practice List</span>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="row" style="justify-content: flex-end;margin-right:1.4rem">
                                            <select id="statusDropdown" class="form-control col-md-2" disabled>
                                            <option value="">--select--</option>
                                            <option value="agree">Agree</option>
                                            <option value="dis_agree">Dis Agree</option>
                                            </select> &nbsp;&nbsp;
                                            <div class="d-flex align-items-center" id="export_div">
                                                <a class="btn btn-primary-export text-white ml-2" href="javascript:void(0);" id='assign_export'  style="font-size:13px"> <svg xmlns="http://www.w3.org/2000/svg" width="18" height="16" fill="currentColor" class="bi bi-box-arrow-up" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M3.5 6a.5.5 0 0 0-.5.5v8a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5v-8a.5.5 0 0 0-.5-.5h-2a.5.5 0 0 1 0-1h2A1.5 1.5 0 0 1 14 6.5v8a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 14.5v-8A1.5 1.5 0 0 1 3.5 5h2a.5.5 0 0 1 0 1z"/><path fill-rule="evenodd" d="M7.646.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 1.707V10.5a.5.5 0 0 1-1 0V1.707L5.354 3.854a.5.5 0 1 1-.708-.708z"/>
                                                </svg>&nbsp;&nbsp;&nbsp;<span>Export</span></a>
                                            </div>
                                        </div>
                                    </div>
                              </div>
                            </div>
                            <div class="wizard wizard-4 custom-wizard" id="kt_wizard_v4" data-wizard-state="step-first"
                                data-wizard-clickable="true" style="margin-top:-2rem !important">
                                <div class="wizard-nav">
                                    <div class="wizard-steps">
                                        <!--begin:: Tab Menu View -->
                                        <div class="wizard-step mb-0 one" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Assigned</h6>
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $assignedCount }}
                                                        </div> --}}
                                                        @include('CountVar.countRectangle', ['count' => $assignedCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($loginEmpId  == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                                            <div class="wizard-step mb-0 seven" data-wizard-type="done">
                                                <div class="wizard-wrapper py-2">
                                                    <div class="wizard-label p-2 mt-2">
                                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                                            <h6 style="margin-right: 5px;">UnAssigned</h6>
                                                            @include('CountVar.countRectangle', ['count' => $unAssignedCount])
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                       @endif
                                        <div class="wizard-step mb-0 two" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Pending</h6>
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $pendingCount }}
                                                        </div> --}}
                                                        @include('CountVar.countRectangle', ['count' => $pendingCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wizard-step mb-0 three" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Hold</h6>
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $holdCount }}
                                                        </div> --}}
                                                        @include('CountVar.countRectangle', ['count' => $holdCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wizard-step mb-0 four" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Completed</h6>
                                                        @include('CountVar.countRectangle', ['count' => $completedCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wizard-step mb-0 five" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Audit Rework</h6>
                                                        {{-- <div class="rounded-circle code-badge-tab">
                                                            {{ $reworkCount }}
                                                        </div> --}}
                                                        @include('CountVar.countRectangle', ['count' => $reworkCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @if ($loginEmpId  == "Admin" || strpos($empDesignation, 'Manager') !== false || strpos($empDesignation, 'VP') !== false || strpos($empDesignation, 'Leader') !== false || strpos($empDesignation, 'Team Lead') !== false || strpos($empDesignation, 'CEO') !== false || strpos($empDesignation, 'Vice') !== false || strpos($empDesignation, 'Group Coordinator - AR') !== false || strpos($empDesignation, 'Subject Matter Expert') !== false)
                                            <div class="wizard-step mb-0 six" data-wizard-type="step">
                                                <div class="wizard-wrapper py-2">
                                                    <div class="wizard-label p-2 mt-2">
                                                        <div class="wizard-title" style="display: flex; align-items: center;">
                                                            <h6 style="margin-right: 5px;">Duplicate</h6>
                                                            {{-- <div class="rounded-circle code-badge-tab-selected">
                                                                {{ $duplicateCount }}
                                                            </div> --}}
                                                            @include('CountVar.countRectangle', ['count' => $duplicateCount])
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="wizard-step mb-0 eight" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title"
                                                    style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Non Workable</h6>
                                                        @include('CountVar.countRectangle', ['count' => $arNonWorkableCount])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wizard-step mb-0 nine" data-wizard-type="done">
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
                                        <div class="wizard-step mb-0 ten" data-wizard-type="done">
                                            <div class="wizard-wrapper py-2">
                                                <div class="wizard-label p-2 mt-2">
                                                    <div class="wizard-title" style="display: flex; align-items: center;">
                                                        <h6 style="margin-right: 5px;">Auto Close</h6>
                                                        @include('CountVar.countRectangle', [
                                                            'count' => $arAutoCloseCount,
                                                        ])
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
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
                                                url('projects_duplicate/' . $clientName . '/' . $subProjectName) .
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
                                                                    {!! Form::$inputType($columnName,isset($searchData) && !empty($searchData) && isset($searchData[$columnName]) && $searchData[$columnName]  ? $searchData[$columnName] : null, [
                                                                        'class' => 'form-control white-smoke pop-non-edt-val',
                                                                        'autocomplete' => 'none',
                                                                        'style' => 'cursor:pointer',
                                                                        'rows' => 3
                                                                    ]) !!}
                                                                @else
                                                                    {!! Form::text($columnName, null, [
                                                                        'class' => 'form-control date_range  white-smoke pop-non-edt-val',
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
                                {{-- <input type="hidden" value={{ $databaseConnection }} id="dbConnection">
                                <input type="hidden" value={{ $encodedId }} id="encodeddbConnection"> --}}
                                <input type="hidden" value={{ $clientName }} id="clientName">
                                <input type="hidden" value={{ $subProjectName }} id="subProjectName">
                                {{-- <div class="d-flex justify-content-between align-items-center">
                                    <select class="form-control col-md-1"
                                        disabled>
                                        <option value="">--select--</option>
                                        <option value="agree">Agree</option>
                                        <option value="dis_agree">Dis Agree</option>
                                    </select>
                                </div> --}}
                                {{-- <div class="form-group row" style="margin-left: 25rem;margin-bottom: -5rem;">
                                    <select id="statusDropdown" class="form-control col-md-1" style="margin-bottom: 1rem;"
                                    disabled>
                                    <option value="">--select--</option>
                                    <option value="agree">Agree</option>
                                    <option value="dis_agree">Dis Agree</option>
                                </select>
                                </div> --}}
                                <div class="table-responsive pt-5 pb-5 clietnts_table">
                                    <table class="table table-separate table-head-custom no-footer dtr-column "
                                        id="client_duplicate_list" data-order='[[ 0, "desc" ]]'>
                                        <thead>

                                            <tr>
                                                @if ($duplicateProjectDetails->contains('key', 'value'))
                                                    @foreach ($duplicateProjectDetails[0]->getAttributes() as $columnName => $columnValue)
                                                        @php
                                                            $columnsToExclude =  ['id','QA_emp_id','duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                                                            'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                                                            'updated_at','created_at', 'deleted_at'];
                                                        @endphp
                                                          <th class='notexport' style="color:white !important"><input type="checkbox" id="ckbCheckAll"></th>
                                                        @if (!in_array($columnName, $columnsToExclude))
                                                            <th><input type="hideen"
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
                                                @else
                                                <th class='notexport'><input type="checkbox" id="ckbCheckAll"></th>
                                                    @foreach ($columnsHeader as $columnName => $columnValue)
                                                        <th><input type="hidden"
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
                                                    @endforeach
                                                @endif
                                            </tr>


                                        </thead>

                                        <tbody>
                                            @if (isset($duplicateProjectDetails))
                                                @foreach ($duplicateProjectDetails as $data)
                                                    @php
                                                    $arrayAttrributes = $data->getAttributes();
                                                    $arrayAttrributes['aging']= null; 
                                                    $arrayAttrributes['aging_range']= null;                                       
                                                    @endphp
                                                    <tr>
                                                        <td><input type="checkbox" class="checkBoxClass" name='check[]' value="{{$data->id}}">
                                                        </td>
                                                        @foreach ($arrayAttrributes as $columnName => $columnValue)
                                                            @php
                                                                  $columnsToExclude =   ['id','QA_emp_id','duplicate_status','ce_hold_reason','qa_hold_reason','qa_work_status','QA_required_sampling','QA_rework_comments','coder_rework_status','coder_rework_reason','coder_error_count','qa_error_count','tl_error_count','tl_comments','QA_status_code','QA_sub_status_code','qa_classification','qa_category','qa_scope','QA_followup_date','CE_status_code','CE_sub_status_code','CE_followup_date',
                                                            'cpt_trends','icd_trends','modifiers','annex_coder_trends','annex_qa_trends','qa_cpt_trends','qa_icd_trends','qa_modifiers',
                                                            'updated_at','created_at', 'deleted_at'];
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

                                                                <td  style="max-width: 300px;white-space: normal;">
                                                                    {{-- @if (str_contains($columnValue, '-') && strtotime($columnValue)) --}}
                                                                    @if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $columnValue))
                                                                         {{ date('m/d/Y', strtotime($columnValue)) }}
                                                                    @else
                                                                        @if ($columnName == 'chart_status' && str_contains($columnValue, 'CE_'))
                                                                            {{ str_replace('CE_', '', $columnValue) }}
                                                                        @elseif ($columnName == 'aging')                                                                                  
                                                                            {{ $agingCount }}
                                                                        @elseif ($columnName == 'aging_range')
                                                                            {{ $agingRange }}
                                                                        @else
                                                                            {{ $columnValue }}
                                                                        @endif
                                                                    @endif
                                                                </td>
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
                                        Showing {{ $duplicateProjectDetails->firstItem() != null ? $duplicateProjectDetails->firstItem() : 0 }} to {{ $duplicateProjectDetails->lastItem() != null ? $duplicateProjectDetails->lastItem() : 0 }} of {{ $duplicateProjectDetails->total() }} entries
                                    </div>
                                     <div>
                                        {{ $duplicateProjectDetails->appends(request()->all())->links() }}
                                    </div>
                                </div>        
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    /* Increase modal width */
    #myModal_status .modal-dialog {
        max-width: 800px;
        /* Adjust the width as needed */
    }

    /* Style for labels */
    #myModal_status .modal-body label {
        margin-bottom: 5px;
    }

    /* Style for textboxes */
    #myModal_status .modal-body input[type="text"] {
        width: 100%;
        padding: 8px;
        box-sizing: border-box;
    }

    /* .dt-buttons {
    position: absolute;
    top: 0;
    right: 0;
    z-index: 1000;
  } */


    .dropdown-item.active {
        color: #ffffff;
        text-decoration: none;
        background-color: #888a91;
    }
</style>
@push('view.scripts')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function() {
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
                var d = new Date();
                var month = d.getMonth() + 1;
                var day = d.getDate();
                var date = (month < 10 ? '0' : '') + month + '-' +
                    (day < 10 ? '0' : '') + day + '-' + d.getFullYear();
            var table = $("#client_duplicate_list").DataTable({
                processing: true,
                ordering: true,
                lengthChange: false,
                searching: indvidualSearchFieldsCount > 0 ? false : true,
                paging: false,
                info: false,
                scrollCollapse: true,
                scrollX: true,
                "initComplete": function(settings, json) {
                    $('body').find('.dataTables_scrollBody').addClass("scrollbar");
                },
                columnDefs: [{

                    targets: [0],
                    orderable: false,
                }, ],
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
                //     "filename": 'procode_duplicate_'+date,
                //     "exportOptions": {
                //         "columns": ':not(.notexport)'// Exclude first two columns
                //     }
                // }],
                dom: "<'row'<'col-md-12'f><'col-md-12't>><'row'<'col-md-5 pt-2'i><'col-md-7 pt-2'p>>"
            })
            table.buttons().container()
                .appendTo('.outside');
                $('.dataTables_filter').addClass('pull-left');
                $(document).on('click', '#filter_clear', function(e) {
                    window.location.href = baseUrl + 'projects_duplicate/' + clientName + '/' + subProjectName +
                        "?parent=" +
                        getUrlVars()[
                            "parent"] +
                        "&child=" + getUrlVars()["child"];
                })
            // var encodedProjectId = $('#encodeddbConnection').val();
            var clientName = $('#clientName').val();
            var subProjectName = $('#subProjectName').val();

            $(document).on('click', '.one', function() {
                window.location.href = baseUrl + 'projects_assigned/' + clientName + '/' + subProjectName +
                    "?parent=" + getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.two', function() {
                window.location.href = baseUrl + 'projects_pending/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.three', function() {
                window.location.href = baseUrl + 'projects_hold/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] +
                    "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.four', function() {
                window.location.href = baseUrl + 'projects_completed/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.five', function() {
                window.location.href = baseUrl + 'projects_Revoke/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.six', function() {
                window.location.href = "{{ url('#') }}";
            })
            $(document).on('click', '.seven', function() {
                window.location.href = baseUrl + 'projects_unassigned/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.eight', function() {
                window.location.href = baseUrl + 'projects_non_workable/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.nine', function() {
                window.location.href = baseUrl + 'ar_rebuttal/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $(document).on('click', '.ten', function() {
                window.location.href = baseUrl + 'projects_auto_close/' + clientName + '/' + subProjectName +
                    "?parent=" +
                    getUrlVars()[
                        "parent"] + "&child=" + getUrlVars()["child"];
            })
            $('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
                localStorage.setItem('activeTab', $(e.target).attr('href'));
            });
           

            var activeTab = localStorage.getItem('activeTab');
            if (activeTab) {
                $('#myTab a[href="' + activeTab + '"]').tab('show');
            }

            $("#ckbCheckAll").click(function() {
                $(".checkBoxClass").prop('checked', $(this).prop('checked'));
                console.log($(this).prop('checked'), $(".checkBoxClass").length, 'log');
                if ($(this).prop('checked') == true && $('.checkBoxClass:checked').length > 0) {
                    $('#statusDropdown').prop('disabled', false);
                } else {
                    $('#statusDropdown').prop('disabled', true);

                }
            });
            $('.checkBoxClass').change(function() {
                var anyCheckboxChecked = $('.checkBoxClass:checked').length > 0;
                var allCheckboxesChecked = $('.checkBoxClass:checked').length === $('.checkBoxClass')
                    .length;
                if (allCheckboxesChecked) {
                    $("#ckbCheckAll").prop('checked', $(this).prop('checked'));
                } else {
                    $("#ckbCheckAll").prop('checked', false);
                }
                $('#statusDropdown').prop('disabled', !(anyCheckboxChecked || allCheckboxesChecked));
            });
            $('#statusDropdown').change(function() {
                dropdownStatus = $(this).val();
                checkedRowValues = $("input[name='check[]']").serializeArray();
                dbConn = $('#dbConnection').val();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content')
                    }
                });
                swal.fire({
                    text: "Do you want to change status?",
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
                            url: "{{ url('clients_duplicate_status') }}",
                            method: 'POST',
                            data: {
                                // dbConn: dbConn,
                                clientName: clientName,
                                subProjectName: subProjectName,
                                dropdownStatus: dropdownStatus,
                                checkedRowValues: checkedRowValues
                            },
                            success: function(response) {
                                location.reload();
                            },

                        });
                    } else {
                        location.reload();
                    }
                });
            })

            $('#clients_list tbody').on('click', 'tr', function() {
                window.location.href = $(this).data('href');
            });
            $(document).on('click', '#assign_export', function(e) {   
                    var resourceName = null; 
                    var formData = $('#formSearch').serialize();
                    var chartStatus = "CE_Assigned";
                    formData += '&chart_status=' + chartStatus;
                    formData += '&clientName=' + clientName;
                    formData += '&subProjectName=' + subProjectName;
                    formData += '&resourceName=' + resourceName;
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
                        url: "{{ url('client_duplicate_xport') }}",
                        method: 'POST',
                        data: formData,
                        xhrFields: {
                            responseType: 'blob'  
                        },
                        success: function(response, status, xhr) {  
                            var filename = "";
                            var disposition = xhr.getResponseHeader('Content-Disposition');
                            if (disposition && disposition.indexOf('attachment') !== -1) {
                                var matches = /filename[^;=\n]*=([^;\n]*)/.exec(disposition);                            
                                if (matches != null && matches[1]) {
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
        });
    </script>
@endpush
