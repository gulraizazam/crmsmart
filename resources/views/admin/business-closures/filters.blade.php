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
                <label>Location:</label>
                <select class="form-control" id="search_location_id">
                </select>
            </div>
            <div class="filterouterdiv mb-0">
                <label>Start Date:</label>
                <input type="text" class="form-control datatable-input" id="search_start_date" placeholder="Select" readonly data-col-index="2">
            </div>
            <div class="filterouterdiv mb-0">
                <label>End Date:</label>
                <input type="text" class="form-control datatable-input" id="search_end_date" placeholder="Select" readonly data-col-index="3">
            </div>
            <div class="sneat-filter-actions">
                <div class="input-icon mb-0">
                    <a href="javascript:void(0);" class="btn btn-primary px-6 font-weight-bold" id="kt_search">Search</a>
                    <a href="javascript:void(0);" class="btn btn-secondary px-6 font-weight-bold ml-3" id="kt_reset">Reset</a>
                </div>
            </div>
        </div>
    </div>
</div>
