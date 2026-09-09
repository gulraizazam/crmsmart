<div class="card card-custom sneat-page-card">
    <div class="card-header">
        <div class="card-title sneat-page-title-wrap">
            <h3 class="card-label">{{ $rolesFormTitle ?? 'Role' }}</h3>
        </div>
        <div class="card-toolbar">
            <a href="{{route('admin.roles.index')}}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i>
                Back
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="form-group row">
            <div class="fv-row col-md-6 my-md-0">
                <label>Name <span class="text text-danger">*</span></label>
                <input type="text" value="{{$role->name ?? ''}}" name="name" class="form-control custom-field" placeholder="Name"/>
            </div>
            <div class="fv-row col-md-6 my-md-0">
                <label>Commission <span class="text text-danger">*</span></label>
                <div class="input-group sneat-commission-group">
                    <input type="number" value="{{$role->commission ?? ''}}" name="commission" min="0" max="100" class="form-control custom-field" placeholder="Commission" />
                    <div class="input-group-append percentage-align">
                        <span class="input-group-text">%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
