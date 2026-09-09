@push("css")
    <style>
        .filterouterdiv .croxcli {
            position: absolute;
            bottom: 0px;
            right: 0;
            padding-left: 11px !important;
            padding: 9px 11px;
        }

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

        .mobile-filter-toggle .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            padding: 12px;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .filter-toggle-arrow {
            margin-left: auto;
            transition: transform 0.3s ease;
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

            <div class="filterouterdiv mb-0 position-relative patient-search-wider js-filter-item" data-filter="patient" data-default="1">
                <label>Patient Search:</label>
                <select class="form-control filter-field select2-patient-search" id="treatment_patient_id" onchange="SetPatient()">
                </select>
            </div>

            <div class="filterouterdiv mb-0 js-filter-item" data-filter="scheduled" data-default="1">
                <label>Scheduled:</label>
                <div class="input-daterange input-group to-from-datepicker datefromto">
                    <input type="text" id="treatment_search_start" autocomplete="off" class="form-control filter-field datatable-input" name="created_start" placeholder="From" onchange="SetFromdate()">
                    <div class="input-group-append" style="width: 0;">
                        <span class="input-group-text">
                            <i class="la la-ellipsis-h"></i>
                        </span>
                    </div>
                    <input type="text" id="treatment_appoint_end" autocomplete="off" class="form-control filter-field datatable-input" name="created_end" placeholder="To" onchange="SetTodate()">
                </div>
            </div>

            <div class="filterouterdiv mb-0 appoint_search_status js-filter-item" data-filter="status" data-default="1">
                <label>Status:</label>
                <select class="form-control filter-field select2" id="treatment_search_status" onchange="SetStatus()"></select>
            </div>

            <div class="filterouterdiv mb-0 appoint_search_status js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="service" data-label="Service">
                <label>
                    Service:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Service filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="treatment_search_service" onchange="SetService()"></select>
            </div>

            <div class="filterouterdiv mb-0 center-filter js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="centre" data-label="Centre">
                <label>
                    Centre:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Centre filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="treatment_search_centre" onchange="SetCenter()"></select>
            </div>

            <div class="filterouterdiv mb-0 doctor-field js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="doctor" data-label="Doctor">
                <label>
                    Doctor:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Doctor filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="treatment_search_doctor" onchange="SetDocId()"></select>
            </div>

            <div class="filterouterdiv mb-0 appoint_search_status js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="created_by" data-label="Created By">
                <label>
                    Created By:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Created By filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="treatment_search_created_by" onchange="SetCreated()">
                </select>
            </div>

            <div class="filterouterdiv mb-0 created-at-field js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="created_at" data-label="Created At">
                <label>
                    Created At:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Created At filter">&times;</button>
                </label>
                <div class="input-daterange input-group to-from-datepicker datefromto">
                    <input type="text" id="treatment_search_created_from" autocomplete="off" class="form-control filter-field datatable-input" name="created_from" placeholder="From" onchange="SetAdvanceFromdate()">
                    <div class="input-group-append" style="width: 0;">
                        <span class="input-group-text">
                            <i class="la la-ellipsis-h"></i>
                        </span>
                    </div>
                    <input type="text" id="treatment_search_created_to" autocomplete="off" class="form-control filter-field datatable-input" name="created_to" placeholder="To" onchange="SetAdvanceTodate()">
                </div>
            </div>

            <div class="filterouterdiv mb-0 updated-by-field js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="updated_by" data-label="Updated By">
                <label>
                    Updated By:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Updated By filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="treatment_search_updated_by" onchange="SetUpdatedBy()">
                </select>
            </div>

            <div class="filterouterdiv mb-0 rescheduled-by-field js-filter-item is-filter-optional sneat-filter-item-hidden" data-filter="rescheduled_by" data-label="Rescheduled By">
                <label>
                    Rescheduled By:
                    <button type="button" class="sneat-filter-remove js-remove-filter" aria-label="Remove Rescheduled By filter">&times;</button>
                </label>
                <select class="form-control filter-field select2" id="treatment_search_rescheduled_by" onchange="SetRescheduledBy()">
                </select>
            </div>

            <div class="sneat-filter-actions">
                <div class="js-add-filter-wrap">
                    <button type="button" class="btn sneat-add-filter-btn js-add-filter-btn" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-plus"></i>
                        Add filter
                    </button>
                    <div class="sneat-add-filter-menu js-filter-menu sneat-filter-item-hidden" role="menu">
                        <button type="button" class="js-filter-menu-item" data-filter="service" role="menuitem">Service</button>
                        <button type="button" class="js-filter-menu-item" data-filter="centre" role="menuitem">Centre</button>
                        <button type="button" class="js-filter-menu-item" data-filter="doctor" role="menuitem">Doctor</button>
                        <button type="button" class="js-filter-menu-item" data-filter="created_by" role="menuitem">Created By</button>
                        <button type="button" class="js-filter-menu-item" data-filter="created_at" role="menuitem">Created At</button>
                        <button type="button" class="js-filter-menu-item" data-filter="updated_by" role="menuitem">Updated By</button>
                        <button type="button" class="js-filter-menu-item" data-filter="rescheduled_by" role="menuitem">Rescheduled By</button>
                    </div>
                </div>
                @include('admin.partials.filter-buttons', ['custom_reset', $custom_reset])
            </div>

        </div>
    </div>

</div>

@push('js')
    <script src="{{ asset('assets/sneat/js/sneat-filter-picker.js') }}?v=1"></script>
@endpush
