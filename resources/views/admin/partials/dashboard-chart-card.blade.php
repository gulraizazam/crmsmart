<div class="{{ $cols }} mb-4">
    <div class="card card-custom dash-widget dash-chart-card">
        <div class="card-header">
            <div>
                <h3 class="card-title mb-0">{{ $title }}</h3>
                @if (!empty($subtitle ?? null))
                    <p class="dash-chart-subtitle">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        <div class="card-body">
            <div id="{{ $id }}" class="dash-chart" style="min-height: {{ $height }}px;"></div>
        </div>
    </div>
</div>
