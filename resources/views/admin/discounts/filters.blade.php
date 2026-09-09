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
                <input type="text" class="form-control filter-field" placeholder="Enter Name" id="search_name" />
            </div>

            <div class="filterouterdiv mb-0">
                <label>Valid From:</label>
                <div class="input-daterange input-group to-from-datepicker">
                    <input type="text" class="form-control filter-field datatable-input" autocomplete="off" placeholder="From" id="search_start" />
                </div>
            </div>

            <div class="filterouterdiv mb-0">
                <label>Valid Till:</label>
                <div class="input-daterange input-group to-from-datepicker">
                    <input type="text" class="form-control filter-field datatable-input" autocomplete="off"placeholder="To" id="search_end" />
                </div>
            </div>

            @if(\Illuminate\Support\Facades\Gate::allows("view_inactive_discounts"))
            <div class="filterouterdiv mb-0">
                <label>Status:</label>
                <select class="form-control filter-field select2" name="status" id="search_status">
                </select>
            </div>
            @endif

            <div class="sneat-filter-actions">
                @include('admin.partials.filter-buttons')
            </div>

        </div>
    </div>
</div>
