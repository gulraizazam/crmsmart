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

            <div class="filterouterdiv mb-0 js-filter-item" data-filter="patient" data-default="1">
                <label>Patient Search:</label>
                <select class="form-control select2-patient-search" id="search_patient_id" name="search_patient_id">
                    <option value=""></option>
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item" data-filter="name" data-default="1">
                <label>Name:</label>
                <input class="form-control filter-field" id="search_name" placeholder="Enter Name">
            </div>

            <div class="filterouterdiv mb-0 js-filter-item" data-filter="membership" data-default="1">
                <label>Membership:</label>
                <select class="form-control filter-field select2" id="search_membership">
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="gender" data-label="Gender">
                <label>
                    Gender:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Gender filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_gender">
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden @if($errors->has('date_range')) has-error @endif" data-filter="created_at" data-label="Created At">
                <label>
                    Created at:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Created At filter">&times;</button>
                </label>
                <div class="input-group">
                    {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'Select Date Range']) !!}
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Gate::allows("view_inactive_patients"))
            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="status" data-label="Status">
                <label>
                    Status:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Status filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_status">
                </select>
            </div>
            @endif

            <div class="sneat-filter-actions">
                <div class="js-add-filter-wrap">
                    <button type="button" class="btn sneat-add-filter-btn js-add-filter-btn" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus"></i>
                        Add filter
                    </button>
                    <div class="sneat-add-filter-menu js-filter-menu sneat-filter-item-hidden" role="menu">
                        <button type="button" class="js-filter-menu-item" data-filter="gender" role="menuitem">Gender</button>
                        <button type="button" class="js-filter-menu-item" data-filter="created_at" role="menuitem">Created At</button>
                        @if(\Illuminate\Support\Facades\Gate::allows("view_inactive_patients"))
                        <button type="button" class="js-filter-menu-item" data-filter="status" role="menuitem">Status</button>
                        @endif
                    </div>
                </div>
                @include('admin.partials.filter-buttons')
            </div>

        </div>
    </div>

</div>

@push('js')
    <script src="{{ asset('assets/sneat/js/sneat-filter-picker.js') }}?v=1"></script>
@endpush
