"use strict";

function sneatPatientInitials(name) {
    if (!name) {
        return '?';
    }
    var parts = String(name).trim().split(/\s+/);
    if (!parts.length || !parts[0]) {
        return '?';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

function sneatPatientAvatarTone(id) {
    var tones = ['primary', 'info', 'success', 'warning'];
    var n = parseInt(id, 10);
    if (isNaN(n)) {
        n = 0;
    }
    return tones[Math.abs(n) % tones.length];
}

/**
 * Shared Treatment Column Definitions
 * 
 * This file contains column definitions shared between:
 * - Main treatments module (treatmentDatatable.js)
 * - Patient card treatments section (treatments.js)
 * 
 * This ensures column changes are reflected in both places.
 */

/**
 * Get treatment columns
 * @param {boolean} includePatientColumn - Whether to include patient name column (false for patient card)
 * @param {object} perms - Permissions object (optional, will use global if not provided)
 * @returns {array} Column definitions
 */
function getTreatmentColumns(includePatientColumn = true, perms = null) {
    // Permissions are read dynamically in templates, not at initialization
    // This allows the datatable to work even before permissions are loaded from API
    
    var columns = [];

    if (includePatientColumn) {
        columns.push({
            field: 'name',
            title: 'Patient',
            width: 220,
            template: function (data) {
                var detail_url = route('admin.appointments.detail', { id: data.id });
                var view_url = route('admin.patients.card', { id: data.patient_id });
                var initials = sneatPatientInitials(data.name);
                var tone = sneatPatientAvatarTone(data.patient_id);
                return '<div class="sneat-patient-cell">' +
                    '<span class="sneat-patient-avatar sneat-patient-avatar--' + tone + '" aria-hidden="true">' + initials + '</span>' +
                    '<div class="sneat-patient-copy">' +
                        '<a class="sneat-patient-name" href="' + view_url + '">' + (data.name || 'N/A') + '</a>' +
                        '<div class="sneat-patient-meta">' +
                            '<a href="javascript:void(0);" class="sneat-patient-id" onclick="viewDetail(`' + detail_url + '`)">' + data.Patient_ID + '</a>' +
                            '<span class="sneat-patient-sep" aria-hidden="true"></span>' +
                            phoneClip(data) +
                        '</div>' +
                    '</div>' +
                '</div>';
            }
        });
    } else {
        columns.push({
            field: 'Patient_ID',
            title: 'ID',
            width: 60,
            sortable: false,
            template: function (data) {
                var detail_url = route('admin.appointments.detail', { id: data.id });
                return '<a href="javascript:void(0);" onclick="viewDetail(`' + detail_url + '`)">' + data.Patient_ID + '</a>';
            }
        });
    }
    
    columns.push({
        field: 'scheduled_date',
        title: 'Scheduled',
        width: 80,
        template: function (data) {
            var p = perms || (typeof permissions !== 'undefined' ? permissions : {});
            if (data.appointment_status_id == "Arrived" || data.appointment_status_id == "Cancelled" || data.appointment_status_id == "Converted") {
                return '<span>' + data.scheduled_date + '</span>';
            } else {
                if (p.schedule_edit) {
                    return '<a href="javascript:void(0);" onclick="editSchedule(' + data.id + ',' + data.doctorId + ',' + data.locationId + ');"><br> ' + data.scheduled_date + ' <i style="color: #cc8600; font-size: large" class="la la-pencil"></i></a>';
                } else {
                    return '<span>' + data.scheduled_date + '</span>';
                }
            }
        }
    });
    
    columns.push({
        field: 'service_id',
        title: 'Service',
        width: 90,
    });
    
    columns.push({
        field: 'doctor_id',
        title: 'Doctor',
        width: 80,
    });
    
    columns.push({
        field: 'appointment_status_id',
        title: 'Status',
        width: 80,
        template: function (data) {
            var p = perms || (typeof permissions !== 'undefined' ? permissions : {});
            if (p.status) {
                if (data.scheduled_date == '-') {
                    return '<span>Un-Scheduled</span>';
                } else if (data.appointment_status == 2) {
                    return '<span style="color: #8950FC;">' + data.appointment_status_id + '</span>';
                } else {
                    return '<a href="javascript:void(0);" onclick="editStatus(' + data.id + ');">' + data.appointment_status_id + ' <i style="color: #cc8600; font-size: large" class="la la-pencil"></i></a>';
                }
            } else {
                return '<span class="badge badge-dark">' + data.appointment_status_id + '</span>';
            }
        }
    });
    
    columns.push({
        field: 'location_id',
        title: 'Centre',
        width: 90,
    });
    
    // Include city column only for main module
    
    
    columns.push({
        field: 'created_at',
        title: 'Created At',
        width: 'auto',
        template: function (data) {
            return formatDate(data.created_at);
        }
    });
    
    // Include created_by, updated_by, converted_by only for main module
    if (includePatientColumn) {
        columns.push({
            field: 'created_by',
            title: 'Created By',
            width: 'auto',
        });
        columns.push({
            field: 'updated_by',
            title: 'Updated By',
            width: 'auto',
        });
        columns.push({
            field: 'converted_by',
            title: 'Rescheduled By',
            width: 'auto',
        });
    }
    
    // Actions column
    columns.push({
        field: 'actions',
        title: 'Actions',
        sortable: false,
        width: 190,
        overflow: 'visible',
        autoHide: false,
        template: function (data) {
            // Use the main module's actions function if available
            if (typeof actions === 'function') {
                return actions(data);
            }
            return '';
        }
    });
    
    return columns;
}
