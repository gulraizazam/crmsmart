@push("css")
    <style>
        @media (max-width: 991px) {
            .all-filters-wrapper {
                display: none;
            }
            .mobile-filter-toggle {
                margin-bottom: 10px;
            }
        }

        @media (min-width: 992px) {
            .mobile-filter-toggle {
                display: none !important;
            }
        }
    </style>
@endpush

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

            <div class="filterouterdiv mb-0 patient-search-wider js-filter-item" data-filter="search" data-default="1">
                <label>Search Lead:</label>
                <input type="text" class="form-control lead_search_filter" placeholder="Search by ID, Name or Phone" id="lead_search_filter" autocomplete="off" />
                <input type="hidden" id="search_id" class="filter-field" />
                <input type="hidden" id="search_full_name" class="filter-field" />
                <input type="hidden" id="search_phone" class="filter-field" />
                <div class="suggesstion-box-leads" style="display: none; position: absolute; z-index: 1130; background: white; border: 1px solid #d9dee3; max-height: 300px; overflow-y: auto; width: 100%;">
                    <ul class="suggestion-list-leads" style="list-style: none; padding: 0; margin: 0;"></ul>
                </div>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item" data-filter="city" data-default="1">
                <label>City:</label>
                <select class="form-control filter-field select2" id="search_city_id" onchange="LoadLoc()"></select>
            </div>

            @if(request('type') == '')
            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="status" data-label="Lead Status">
                <label>
                    Lead Status:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Lead Status filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_status_id"></select>
            </div>
            @endif

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="centre" data-label="Centre">
                <label>
                    Centre:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Centre filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_location_id">
                    <option value="">All</option>
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="service" data-label="Service">
                <label>
                    Service:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Service filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_service_id"></select>
            </div>

            @if(request('type') != '')
            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="status" data-label="Lead Status">
                <label>
                    Lead Status:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Lead Status filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_status_id"></select>
            </div>
            @endif

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="gender" data-label="Gender">
                <label>
                    Gender:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Gender filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_gender_id">
                    <option value="">Select</option>
                    <option value="1">Male</option>
                    <option value="2">Female</option>
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="created_at" data-label="Created At">
                <label>
                    Created at:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Created At filter">&times;</button>
                </label>
                <div class="input-group">
                    {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'Select Date Range']) !!}
                </div>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="department" data-label="Department">
                <label>
                    Department:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Department filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_department_id">
                    <option value="">All</option>
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="assigned_to" data-label="Assigned To">
                <label>
                    Assigned To:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Assigned To filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_assigned_to">
                    <option value="">All</option>
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="created_by" data-label="Created By">
                <label>
                    Created By:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Created By filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_created_by">
                </select>
            </div>

            <div class="sneat-filter-actions">
                <div class="js-add-filter-wrap">
                    <button type="button" class="btn sneat-add-filter-btn js-add-filter-btn" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus"></i>
                        Add filter
                    </button>
                    <div class="sneat-add-filter-menu js-filter-menu sneat-filter-item-hidden" role="menu">
                        <button type="button" class="js-filter-menu-item" data-filter="centre" role="menuitem">Centre</button>
                        <button type="button" class="js-filter-menu-item" data-filter="service" role="menuitem">Service</button>
                        <button type="button" class="js-filter-menu-item" data-filter="status" role="menuitem">Lead Status</button>
                        <button type="button" class="js-filter-menu-item" data-filter="gender" role="menuitem">Gender</button>
                        <button type="button" class="js-filter-menu-item" data-filter="created_at" role="menuitem">Created At</button>
                        <button type="button" class="js-filter-menu-item" data-filter="department" role="menuitem">Department</button>
                        <button type="button" class="js-filter-menu-item" data-filter="assigned_to" role="menuitem">Assigned To</button>
                        <button type="button" class="js-filter-menu-item" data-filter="created_by" role="menuitem">Created By</button>
                    </div>
                </div>
                @include('admin.partials.filter-buttons')
            </div>

        </div>
    </div>

</div>

@push('js')
    <script src="{{ asset('assets/sneat/js/sneat-filter-picker.js') }}?v=2"></script>
@endpush
