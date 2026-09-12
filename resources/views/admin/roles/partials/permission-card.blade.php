@php
    $allowed = $allowed ?? [];
    $groups = $groups ?? [];
@endphp
<div class="card card-custom sneat-page-card sneat-role-perm-card">
    <div class="card-header">
        <div class="card-title sneat-page-title-wrap">
            <h3 class="card-label">{{ $cardTitle }}</h3>
        </div>
    </div>
    <div class="card-body">
        @if(count($groups))
            @foreach($groups as $permission)
                <div class="form-group row">
                    <label class="col-2 col-form-label"><strong>{{ $permission['title'] }}</strong></label>
                    <div class="col-9 col-form-label">
                        <div class="checkbox-inline">
                            <label class="checkbox permission_checkbox">
                                <input id="allow_{{ $permission['name'] }}" type="checkbox" name="permission[]"
                                       class="allow_all allow {{ $permission['name'] }} allow_{{ $permission['name'] }}"
                                       value="{{ $permission['name'] }}"
                                       @if(isset($allowed[$permission['id']])) checked="true" @endif
                                       onclick="FormValidation.checkMyModule(this,'allow_{{ $permission['name'] }}');">
                                <span></span>Display
                            </label>
                            @foreach($permission['children'] as $child)
                                <label class="checkbox permission_checkbox">
                                    <input id="sub-allow_{{ $child['name'] }}"
                                           type="checkbox" name="permission[]"
                                           class="allow_all allow {{ $permission['name'] }}  sub-allow_{{ $permission['name'] }}"
                                           value="{{ $child['name'] }}"
                                           @if(isset($allowed[$child['id']])) checked="true" @endif
                                           onclick="FormValidation.checkMyParent(this,'allow_{{ $permission['name'] }}' , 'sub-allow_{{ $permission['name'] }}', '{{ $child['name'] }}' );">
                                    <span></span>{{ $child['title'] }}</label>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
