@extends('admin.layouts.master')
@section('title', 'Roles')
@section('content')

    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-roles.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-roles-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Roles</h3>
                        </div>
                        <div class="card-toolbar">
                            @if(Gate::allows('roles_create'))
                                <a href="{{ route('admin.roles.create') }}" class="btn btn-primary">
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
                                        <label>Name:</label>
                                        <input type="text" value="{{$filters['name'] ?? ''}}" class="form-control filter-field" placeholder="Name" id="search_name" />
                                    </div>
                                    <div class="filterouterdiv mb-0">
                                        <label>Commission:</label>
                                        <input type="text" oninput="phoneField(this);" value="{{$filters['commission'] ?? ''}}" class="form-control filter-field" placeholder="Commission" id="search_commission" />
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

    @push('datatable-js')
        <script src="{{asset('assets/js/pages/users/role.js')}}"></script>
    @endpush

@endsection
