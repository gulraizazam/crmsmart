@extends('admin.layouts.master')
@section('title', 'Roles Duplicate')
@section('content')
    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-roles.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-roles-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <form class="form fv-plugins-bootstrap" method="post" id="permissions-form" action="{{route('admin.roles.duplicate.store')}}">
                    @csrf
                    @include('admin.roles.fields', ['rolesFormTitle' => 'Duplicate Role'])

                    <div class="card card-custom sneat-page-card sneat-role-perm-card">
                        <div class="card-header">
                            <div class="card-title sneat-page-title-wrap">
                                <h3 class="card-label">Dashboard Permissions</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="accordion accordion-light accordion-light-borderless accordion-svg-toggle" id="dashboard-collapse">
                                <div id="dashboard-permissions" class="collapse show" data-parent="#dashboard-collapse">
                                    @if(count($dashboard_permissions))
                                        @foreach($dashboard_permissions as $permission)
                                            <div class="form-group row">
                                                <label class="col-2 col-form-label"><strong>{{ $permission['title'] }}</strong></label>
                                                <input id="allow_{{ $permission['name'] }}" type="checkbox" name="permission[]"
                                                       class="allow_all allow {{ $permission['name'] }} allow_{{ $permission['name'] }}"
                                                       value="{{ $permission['name'] }}"
                                                       @if(isset($allowed_permissions[$permission['id']])) checked="true" @endif
                                                       style="visibility: hidden;"
                                                       onclick="FormValidation.checkMyModule(this,'allow_{{ $permission['name'] }}');">
                                                <div class="col-9 col-form-label">
                                                    <div class="checkbox-inline">
                                                        @foreach($permission['children'] as $child)
                                                            <label class="checkbox permission_checkbox">
                                                                <input id="sub-allow_{{ $child['name'] }}"
                                                                       type="checkbox" name="permission[]"
                                                                       class="allow_all allow {{ $permission['name'] }}  sub-allow_{{ $permission['name'] }}"
                                                                       value="{{ $child['name'] }}"
                                                                       @if(isset($allowed_permissions[$child['id']])) checked="true" @endif
                                                                       onclick="FormValidation.checkMyParent(this,'allow_{{ $permission['name'] }}' , 'sub-allow_{{ $permission['name'] }}', '{{ $child['name'] }}' );">
                                                                <span></span>{{ $child['title'] }}</label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-custom sneat-page-card sneat-role-perm-card">
                        <div class="card-header">
                            <div class="card-title sneat-page-title-wrap">
                                <h3 class="card-label">General Permissions</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="accordion accordion-light accordion-light-borderless accordion-svg-toggle" id="general-collapse">
                                <div id="general-permissions" class="collapse show" data-parent="#general-collapse">
                                    @if(count($permissions))
                                        @foreach($permissions as $permission)
                                            <div class="form-group row">
                                                <label class="col-2 col-form-label"><strong>{{ $permission['title'] }}</strong></label>
                                                <div class="col-9 col-form-label">
                                                    <div class="checkbox-inline">
                                                        <label class="checkbox permission_checkbox">
                                                        <input id="allow_{{ $permission['name'] }}" type="checkbox" name="permission[]"
                                                               class="allow_all allow {{ $permission['name'] }} allow_{{ $permission['name'] }}"
                                                               value="{{ $permission['name'] }}"
                                                               @if(isset($allowed_permissions[$permission['id']])) checked="true" @endif
                                                               onclick="FormValidation.checkMyModule(this,'allow_{{ $permission['name'] }}');">
                                                        <span></span>Display</label>
                                                        @foreach($permission['children'] as $child)
                                                            <label class="checkbox permission_checkbox">
                                                                <input id="sub-allow_{{ $child['name'] }}"
                                                                       type="checkbox" name="permission[]"
                                                                       class="allow_all allow {{ $permission['name'] }}  sub-allow_{{ $permission['name'] }}"
                                                                       value="{{ $child['name'] }}"
                                                                       @if(isset($allowed_permissions[$child['id']])) checked="true" @endif
                                                                       onclick="FormValidation.checkMyParent(this,'allow_{{ $permission['name'] }}' , 'sub-allow_{{ $permission['name'] }}', '{{ $child['name'] }}' );">
                                                                <span></span>{{ $child['title'] }}</label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card card-custom sneat-page-card sneat-role-perm-card">
                        <div class="card-header">
                            <div class="card-title sneat-page-title-wrap">
                                <h3 class="card-label">Reports Permissions</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="accordion accordion-light accordion-light-borderless accordion-svg-toggle" id="report-collapse">
                                <div id="report-permissions" class="collapse show" data-parent="#report-collapse">
                                    @if(count($reports_permissions))
                                        @foreach($reports_permissions as $permission)
                                            <div class="form-group row">
                                                <label class="col-2 col-form-label"><strong>{{ $permission['title'] }}</strong></label>
                                                <div class="col-9 col-form-label">
                                                    <div class="checkbox-inline">
                                                        <label class="checkbox permission_checkbox">
                                                            <input id="allow_{{ $permission['name'] }}" type="checkbox" name="permission[]"
                                                                class="allow_all allow {{ $permission['name'] }} allow_{{ $permission['name'] }}"
                                                                value="{{ $permission['name'] }}"
                                                                @if(isset($allowed_permissions[$permission['id']])) checked="true" @endif
                                                                onclick="FormValidation.checkMyModule(this,'allow_{{ $permission['name'] }}');">
                                                            <span></span>Display
                                                        </label>
                                                        @foreach($permission['children'] as $child)
                                                            <label class="checkbox permission_checkbox">
                                                                <input id="sub-allow_{{ $child['name'] }}"
                                                                    type="checkbox" name="permission[]"
                                                                    class="allow_all allow {{ $permission['name'] }}  sub-allow_{{ $permission['name'] }}"
                                                                    value="{{ $child['name'] }}"
                                                                    @if(isset($allowed_permissions[$child['id']])) checked="true" @endif
                                                                    onclick="FormValidation.checkMyParent(this,'allow_{{ $permission['name'] }}' , 'sub-allow_{{ $permission['name'] }}', '{{ $child['name'] }}' );">
                                                                <span></span>{{ $child['title'] }}</label>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                            <div class="sneat-role-save">
                                <button type="submit" class="btn btn-primary spinner-button">
                                    <span class="indicator-label">Save</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_permission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="permission-create">
        </div>
    </div>

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/users/role.js')}}"></script>
    @endpush
    @push('js')
        <script src="{{asset('assets/js/pages/crud/forms/validation/permission/permission-validate.js')}}"></script>
    @endpush
@endsection
