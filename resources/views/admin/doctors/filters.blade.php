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

            <div class="filterouterdiv mb-0 js-filter-item" data-filter="name" data-default="1">
                <label>Name:</label>
                <input type="text" class="form-control filter-field" placeholder="Enter Name" id="search_name" />
            </div>

            @if(\Illuminate\Support\Facades\Gate::allows("view_inactive_doctors"))
            <div class="filterouterdiv mb-0 js-filter-item" data-filter="status" data-default="1">
                <label>Status:</label>
                <select class="form-control filter-field select2" name="status" id="search_status">
                </select>
            </div>
            @else
            <div class="filterouterdiv mb-0 js-filter-item" data-filter="email" data-default="1">
                <label>Email:</label>
                <input type="text" class="form-control filter-field" placeholder="Email" id="search_email" />
            </div>
            @endif

            <div class="filterouterdiv mb-0 js-filter-item" data-filter="role" data-default="1">
                <label>Role:</label>
                <select class="form-control filter-field select2" name="role_id" id="search_role">
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="phone" data-label="Phone">
                <label>
                    Phone:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Phone filter">&times;</button>
                </label>
                <input type="number" class="form-control filter-field" placeholder="eg: 03000000000" id="search_phone" />
            </div>

            @if(\Illuminate\Support\Facades\Gate::allows("view_inactive_doctors"))
            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="email" data-label="Email">
                <label>
                    Email:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Email filter">&times;</button>
                </label>
                <input type="text" class="form-control filter-field" placeholder="Email" id="search_email" />
            </div>
            @endif

            <div class="filterouterdiv mb-0 js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="gender" data-label="Gender">
                <label>
                    Gender:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Gender filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="search_gender" name="gender">
                    <option value="">All</option>
                    <option value="1">Male</option>
                    <option value="2">Female</option>
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

            <div class="sneat-filter-actions">
                <div class="js-add-filter-wrap">
                    <button type="button" class="btn sneat-add-filter-btn js-add-filter-btn" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus"></i>
                        Add filter
                    </button>
                    <div class="sneat-add-filter-menu js-filter-menu sneat-filter-item-hidden" role="menu">
                        <button type="button" class="js-filter-menu-item" data-filter="phone" role="menuitem">Phone</button>
                        @if(\Illuminate\Support\Facades\Gate::allows("view_inactive_doctors"))
                        <button type="button" class="js-filter-menu-item" data-filter="email" role="menuitem">Email</button>
                        @endif
                        <button type="button" class="js-filter-menu-item" data-filter="gender" role="menuitem">Gender</button>
                        <button type="button" class="js-filter-menu-item" data-filter="created_at" role="menuitem">Created At</button>
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
