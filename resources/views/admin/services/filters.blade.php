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
                <input type="text" class="form-control filter-field" placeholder="Name" id="search_name" />
            </div>
            @if(\Illuminate\Support\Facades\Gate::allows("view_inactive_services"))
            <div class="filterouterdiv mb-0">
                <label>Status:</label>
                <select class="form-control filter-field select2" id="search_status">
                    <option value="">All</option>
                    <option value="1">Active</option>
                    <option value="0">Inactive</option>
                </select>
            </div>
            @endif
            <div class="sneat-filter-actions">
                @include('admin.partials.filter-buttons')
            </div>
        </div>
    </div>
</div>
