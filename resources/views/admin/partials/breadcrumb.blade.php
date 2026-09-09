<div class="subheader py-2" id="kt_subheader">
    <div class="d-flex align-items-center flex-wrap">
        <div class="d-flex flex-column">
            @if(!empty($module))
                <h5 class="text-dark font-weight-bold my-0">{{ $module }}</h5>
            @endif
            <ul class="breadcrumb breadcrumb-transparent p-0 my-1 font-size-sm">
                <li class="breadcrumb-item text-muted">
                    <a href="{{ route('admin.home') }}" class="text-muted">Home</a>
                </li>
                @if(!empty($title))
                    <li class="breadcrumb-item text-muted">
                        <span class="text-muted">{{ $title }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
