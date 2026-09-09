<div class="mt-2 mb-7 js-filter-bar">
    <div class="sneat-filter-row plan-filters">

        <div class="filterouterdiv mb-0 patient-search-wider" id="patient_id">
            <label>Patient Search:</label>
            <select class="form-control filter-field select2-patient-search" id="search_patient_id">
            </select>
        </div>

        <div class="filterouterdiv mb-0 search_input">
            <label>Plan ID:</label>
            <select class="form-control filter-field package_id" id="search_plan_id"></select>
        </div>

        <div class="filterouterdiv mb-0">
            <label>Centre:</label>
            <select class="form-control filter-field select2" id="search_location_id"></select>
        </div>

        <div class="filterouterdiv mb-0 @if($errors->has('date_range')) has-error @endif">
            {!! Form::label('date_range', 'Created at:') !!}
            <div class="input-group">
                {!! Form::text('date_range', null, ['id' => 'date_range', 'class' => 'form-control', 'autocomplete' => 'off', 'placeholder' => 'Select Date Range']) !!}
            </div>
        </div>

        <div class="sneat-filter-actions">
            @include('admin.partials.filter-buttons')
        </div>
    </div>
</div>
