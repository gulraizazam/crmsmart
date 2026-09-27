@extends('admin.layouts.inventory-erp')
@section('title', 'Period Locks')
@section('content')
@push('css')
    <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=12" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/sneat-inventory-erp.css') }}?v=1" rel="stylesheet" type="text/css" />
@endpush
<div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-inv-page" id="kt_content">
    <div class="d-flex flex-column-fluid">
        <div class="container-fluid sneat-page">
            <div class="card card-custom sneat-page-card">
                <div class="card-header">
                    <div class="card-title sneat-page-title-wrap"><h3 class="card-label">Inventory Period Locks</h3></div>
                </div>
                <div class="card-body">
                    <p class="inv-meta mb-3">Locked months block posting and reversing documents dated in that period.</p>

                    <form method="post" action="{{ route('admin.inventory-erp.finance.periods.lock') }}" class="form-row inv-filters align-items-end mb-4">
                        @csrf
                        <div class="col-md-2">
                            <label>Year</label>
                            <input type="number" name="period_year" class="form-control" value="{{ date('Y') }}" min="2000" max="2100" required>
                        </div>
                        <div class="col-md-2">
                            <label>Month</label>
                            <select name="period_month" class="form-control" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ (int) date('n') === $m ? 'selected' : '' }}>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Notes</label>
                            <input type="text" name="notes" class="form-control" placeholder="Optional">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary" type="submit" onclick="return confirm('Lock this period?');">Lock</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-head-custom table-vertical-center">
                            <thead>
                                <tr>
                                    <th>Period</th><th>Status</th><th>Locked at</th><th>Notes</th><th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($locks as $lock)
                                    <tr>
                                        <td class="inv-value">{{ $lock->label() }}</td>
                                        <td>
                                            @if($lock->isLocked())
                                                <span class="badge badge-soft">Locked</span>
                                            @else
                                                <span class="badge badge-soft-muted">Unlocked</span>
                                            @endif
                                        </td>
                                        <td>{{ optional($lock->locked_at)->format('d M Y H:i') ?: '—' }}</td>
                                        <td class="inv-meta">{{ $lock->notes ?: '—' }}</td>
                                        <td class="text-right">
                                            @if($lock->isLocked())
                                                <form method="post" action="{{ route('admin.inventory-erp.finance.periods.unlock', $lock->id) }}" class="d-inline" onsubmit="return confirm('Unlock {{ $lock->label() }}?');">
                                                    @csrf
                                                    <input type="hidden" name="notes" value="Manual unlock">
                                                    <button class="btn btn-sm btn-light-danger" type="submit">Unlock</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No period locks yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $locks->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
