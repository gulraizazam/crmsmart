@extends('admin.layouts.master')
@section('title', 'Lead Departments')
@section('content')

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-leads-page" id="kt_content">
        @include('admin.partials.breadcrumb', ['module' => 'Leads', 'title' => 'Lead Departments'])

        <div class="d-flex flex-column-fluid">
            <div class="container">
                <div class="card card-custom">
                    <div class="card-header py-3">
                        <div class="card-title">
                            <h3 class="card-label">Lead Departments</h3>
                        </div>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-primary" id="add_department_btn">
                                <i class="la la-plus"></i>
                                Add Department
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-4">Map each department to one or more centres. A lead created at that centre can then be tagged with the matching department.</p>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Department</th>
                                        <th>Centres</th>
                                        <th>Status</th>
                                        <th width="140">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="lead_departments_table">
                                    @forelse($departments as $department)
                                        <tr data-id="{{ $department->id }}">
                                            <td>{{ $department->name }}</td>
                                            <td>{{ $department->locations->pluck('name')->join(', ') ?: '—' }}</td>
                                            <td>{{ $department->active ? 'Active' : 'Inactive' }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-light-primary js-edit-department" data-id="{{ $department->id }}">Edit</button>
                                                <button type="button" class="btn btn-sm btn-light-danger js-delete-department" data-id="{{ $department->id }}">Delete</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr class="js-empty-row">
                                            <td colspan="4" class="text-center">No departments yet. Add one and attach it to centres.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal_lead_department" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form id="lead_department_form">
                    <div class="modal-header">
                        <h5 class="modal-title" id="lead_department_title">Add Department</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="department_id" value="">
                        <div class="form-group">
                            <label>Department name</label>
                            <input type="text" name="name" id="department_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Centres</label>
                            <select name="location_ids[]" id="department_locations" class="form-control select2" multiple>
                                @foreach($locations as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Leads at these centres can use this department.</small>
                        </div>
                        <div class="form-group">
                            <label class="custom_checkbox">
                                <input type="checkbox" name="active" id="department_active" value="1" checked>
                                <strong></strong>
                                <span>Active</span>
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        (function () {
            var departments = @json($departments->map(function ($row) {
                return [
                    'id' => $row->id,
                    'name' => $row->name,
                    'active' => (int) $row->active,
                    'location_ids' => $row->locations->pluck('id'),
                    'locations' => $row->locations->pluck('name'),
                ];
            })->values());

            function renderTable() {
                var $body = $('#lead_departments_table');
                if (!departments.length) {
                    $body.html('<tr class="js-empty-row"><td colspan="4" class="text-center">No departments yet. Add one and attach it to centres.</td></tr>');
                    return;
                }
                var html = '';
                departments.forEach(function (row) {
                    html += '<tr data-id="' + row.id + '">' +
                        '<td>' + $('<div>').text(row.name).html() + '</td>' +
                        '<td>' + $('<div>').text((row.locations || []).join(', ') || '—').html() + '</td>' +
                        '<td>' + (row.active ? 'Active' : 'Inactive') + '</td>' +
                        '<td>' +
                            '<button type="button" class="btn btn-sm btn-light-primary js-edit-department" data-id="' + row.id + '">Edit</button> ' +
                            '<button type="button" class="btn btn-sm btn-light-danger js-delete-department" data-id="' + row.id + '">Delete</button>' +
                        '</td></tr>';
                });
                $body.html(html);
            }

            function openForm(row) {
                $('#department_id').val(row ? row.id : '');
                $('#department_name').val(row ? row.name : '');
                $('#department_active').prop('checked', !row || Number(row.active) === 1);
                $('#department_locations').val(row ? row.location_ids : []).trigger('change');
                $('#lead_department_title').text(row ? 'Edit Department' : 'Add Department');
                $('#modal_lead_department').modal('show');
            }

            $('#department_locations').select2({
                width: '100%',
                dropdownParent: $('#modal_lead_department')
            });

            $('#add_department_btn').on('click', function () {
                openForm(null);
            });

            $(document).on('click', '.js-edit-department', function () {
                var id = Number($(this).data('id'));
                var row = departments.find(function (item) { return Number(item.id) === id; });
                if (row) {
                    openForm(row);
                }
            });

            $(document).on('click', '.js-delete-department', function () {
                var id = Number($(this).data('id'));
                if (!id || !confirm('Delete this department?')) {
                    return;
                }
                $.ajax({
                    url: route('admin.lead_departments.destroy', { id: id }),
                    type: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function (response) {
                        if (!response.status) {
                            toastr.error(response.message || 'Could not delete department.');
                            return;
                        }
                        departments = departments.filter(function (item) { return Number(item.id) !== id; });
                        renderTable();
                        toastr.success(response.message);
                    },
                    error: function (xhr) {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete department.');
                    }
                });
            });

            $('#lead_department_form').on('submit', function (e) {
                e.preventDefault();
                var id = $('#department_id').val();
                var payload = {
                    name: $('#department_name').val(),
                    active: $('#department_active').is(':checked') ? 1 : 0,
                    location_ids: $('#department_locations').val() || []
                };
                $.ajax({
                    url: id ? route('admin.lead_departments.update', { id: id }) : route('admin.lead_departments.store'),
                    type: id ? 'PUT' : 'POST',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    data: payload,
                    success: function (response) {
                        if (!response.status) {
                            toastr.error(response.message || 'Could not save department.');
                            return;
                        }
                        var saved = response.data.department;
                        var idx = departments.findIndex(function (item) { return Number(item.id) === Number(saved.id); });
                        if (idx >= 0) {
                            departments[idx] = saved;
                        } else {
                            departments.push(saved);
                        }
                        $('#modal_lead_department').modal('hide');
                        renderTable();
                        toastr.success(response.message);
                    },
                    error: function (xhr) {
                        toastr.error((xhr.responseJSON && xhr.responseJSON.message) || 'Could not save department.');
                    }
                });
            });
        })();
    </script>
@endpush
