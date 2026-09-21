@php
    $index = $index ?? 0;
    $item = $item ?? [];
    $frequencies = $frequencies ?? [];
@endphp
<div class="sneat-rx-item" data-rx-item>
    <div class="sneat-rx-item-head">
        <strong class="sneat-rx-item-title">Medicine</strong>
        <button type="button" class="btn btn-sm btn-light-danger remove-rx-item">Remove</button>
    </div>
    <div class="sneat-rx-field">
        <label>Medicine name</label>
        <input type="text" name="items[{{ $index }}][medicine_name]" class="form-control" required
               value="{{ $item['medicine_name'] ?? '' }}" placeholder="e.g. Augmentin" autocomplete="off">
    </div>
    <div class="sneat-rx-item-grid">
        <div class="sneat-rx-field">
            <label>Dose</label>
            <input type="text" name="items[{{ $index }}][dose]" class="form-control"
                   value="{{ $item['dose'] ?? '' }}" placeholder="e.g. 500mg" autocomplete="off">
        </div>
        <div class="sneat-rx-field">
            <label>Frequency</label>
            <select name="items[{{ $index }}][frequency]" class="form-control">
                <option value="">Select</option>
                @foreach($frequencies as $freq)
                    <option value="{{ $freq }}" {{ ($item['frequency'] ?? '') === $freq ? 'selected' : '' }}>{{ $freq }}</option>
                @endforeach
            </select>
        </div>
        <div class="sneat-rx-field">
            <label>Duration</label>
            <input type="text" name="items[{{ $index }}][duration]" class="form-control"
                   value="{{ $item['duration'] ?? '' }}" placeholder="e.g. 5 days" autocomplete="off">
        </div>
    </div>
    <div class="sneat-rx-field">
        <label>Instructions</label>
        <input type="text" name="items[{{ $index }}][instructions]" class="form-control"
               value="{{ $item['instructions'] ?? '' }}" placeholder="After food" autocomplete="off">
    </div>
</div>
