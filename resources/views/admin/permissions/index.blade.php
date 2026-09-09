@extends('admin.layouts.master')
@section('title', 'Permissions')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-permissions.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-permissions-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Permissions</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('permissions_create'))
                            <a href="javascript:void(0);" onclick="createPermission('{{ route('admin.permissions.create') }}');" class="btn btn-primary" data-toggle="modal" data-target="#modal_add_permission">
                                <i class="la la-plus"></i>
                                Add New
                            </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="mt-2 mb-7 js-filter-bar">
                            <div class="row mb-3 mobile-filter-toggle">
                                <div class="col-12">
                                    <button class="btn btn-primary btn-block" onclick="toggleAllFilters();">
                                        <i class="fa fa-filter mr-2"></i>
                                        <span>Filters</span>
                                        <i class="filter-toggle-arrow fa fa-chevron-down"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="all-filters-wrapper">
                                <div class="sneat-filter-row">
                                    <div class="filterouterdiv mb-0">
                                        <label>Parent Group:</label>
                                        <select class="form-control filter-field" id="search_parent_id">
                                            <option value="">All Parent Groups</option>
                                        </select>
                                    </div>
                                    <div class="filterouterdiv mb-0 search-wider">
                                        <label>Search:</label>
                                        <input type="text" value="{{$filters['search'] ?? ''}}" class="form-control filter-field" placeholder="Search..." id="search_search" />
                                    </div>
                                    <div class="sneat-filter-actions">
                                        @include('admin.partials.filter-buttons')
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="datatable datatable-bordered datatable-head-custom" id="kt_datatable"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_add_permission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="permission-create">
            @include('admin.permissions.create')
        </div>
    </div>

    <div class="modal fade" id="modal_edit_permission" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered form-popup" id="permission-create">
            @include('admin.permissions.edit')
        </div>
    </div>

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/users/permission.js')}}"></script>
    @endpush

    @push('js')
        <script src="{{asset('assets/js/pages/crud/forms/validation/permission/validate.js')}}"></script>
    @endpush

@endsection
