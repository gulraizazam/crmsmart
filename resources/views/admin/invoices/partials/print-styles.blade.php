{{-- Shared print styles for consultancy, treatment, and plan invoices. --}}
@php
    $compact = $compact ?? false;
@endphp
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #1f2933;
        background: #ffffff;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .cinv-shell {
        max-width: 760px;
        margin: 0 auto;
        background: #ffffff;
        padding: {{ $compact ? '18px 22px 16px' : '28px 32px 22px' }};
        border: 1px solid #e5e7eb;
    }
    .cinv-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        padding-bottom: {{ $compact ? '12px' : '16px' }};
        border-bottom: 1px solid #d1d5db;
    }
    .cinv-brand img {
        height: {{ $compact ? '52px' : '62px' }};
        width: auto;
        max-width: 260px;
        display: block;
        background: #161310;
        padding: 6px 10px;
        border-radius: 4px;
    }
    .cinv-brand-sub {
        margin-top: 6px;
        font-size: 11px;
        color: #6b7280;
        letter-spacing: 0.04em;
    }
    .cinv-doc-type { text-align: right; }
    .cinv-doc-type .cinv-kicker {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 4px;
    }
    .cinv-doc-type .cinv-title {
        font-size: {{ $compact ? '18px' : '20px' }};
        font-weight: 700;
        color: #111827;
        letter-spacing: 0.01em;
    }
    .cinv-doc-type .cinv-subtitle {
        margin-top: 4px;
        font-size: 12px;
        color: #4b5563;
    }
    .cinv-info-wrap { padding: {{ $compact ? '12px 0 4px' : '16px 0 8px' }}; }
    .cinv-info-grid { width: 100%; border-collapse: collapse; }
    .cinv-info-grid td {
        width: 25%;
        padding: {{ $compact ? '6px 10px 8px 0' : '8px 12px 10px 0' }};
        vertical-align: top;
        border-bottom: 1px solid #f3f4f6;
    }
    .cinv-info-label {
        font-size: 10px;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin-bottom: 3px;
    }
    .cinv-info-value {
        font-size: {{ $compact ? '12.5px' : '13px' }};
        font-weight: 600;
        color: #111827;
        line-height: 1.35;
    }
    .cinv-summary {
        margin: {{ $compact ? '10px 0 0' : '14px 0 0' }};
        padding: {{ $compact ? '10px 0' : '12px 0' }};
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }
    .cinv-summary-title {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 4px;
    }
    .cinv-service-name {
        font-size: {{ $compact ? '14px' : '15px' }};
        font-weight: 700;
        color: #111827;
        margin-bottom: 3px;
    }
    .cinv-service-meta {
        font-size: 11px;
        color: #6b7280;
        line-height: 1.45;
    }
    .cinv-section-title {
        margin: {{ $compact ? '12px 0 6px' : '16px 0 8px' }};
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6b7280;
    }
    .cinv-ledger { margin: {{ $compact ? '8px 0 0' : '12px 0 0' }}; }
    .cinv-ledger table { width: 100%; border-collapse: collapse; }
    .cinv-ledger .row td { border-bottom: 1px solid #f3f4f6; }
    .cinv-ledger td {
        padding: {{ $compact ? '7px 0' : '9px 0' }};
        font-size: 13px;
        color: #374151;
    }
    .cinv-ledger td.label { color: #4b5563; }
    .cinv-ledger td.amount { text-align: right; font-weight: 600; color: #111827; }
    .cinv-ledger .total-row td {
        border-top: 1px solid #111827;
        border-bottom: none;
        padding-top: 10px;
        font-size: 13px;
        font-weight: 700;
        color: #111827;
    }
    .cinv-ledger .total-row td.amount { font-size: 16px; }
    table.cinv-items,
    table.cinv-pay {
        width: 100%;
        border-collapse: collapse;
        font-size: {{ $compact ? '11px' : '12px' }};
    }
    table.cinv-items thead th,
    table.cinv-pay thead th {
        background: #f9fafb;
        color: #4b5563;
        text-align: left;
        padding: {{ $compact ? '7px 8px' : '8px 10px' }};
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }
    table.cinv-items thead th.right,
    table.cinv-pay thead th.right { text-align: right; }
    table.cinv-items tbody td,
    table.cinv-pay tbody td {
        padding: {{ $compact ? '7px 8px' : '8px 10px' }};
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        vertical-align: middle;
    }
    table.cinv-items tbody td.name { font-weight: 600; color: #111827; }
    table.cinv-items tbody td.right,
    table.cinv-pay tbody td.right { text-align: right; }
    table.cinv-pay tbody tr.tot td {
        background: #f9fafb;
        font-weight: 700;
        color: #111827;
        border-bottom: 1px solid #e5e7eb;
    }
    .cinv-note {
        margin: {{ $compact ? '10px 0 0' : '14px 0 0' }};
        font-size: 11px;
        color: #6b7280;
        line-height: 1.5;
    }
    .cinv-note strong { color: #111827; font-weight: 700; }
    .cinv-signatures {
        margin: {{ $compact ? '22px 0 8px' : '36px 0 10px' }};
        display: flex;
        justify-content: space-between;
        gap: 48px;
    }
    .cinv-sig-block { flex: 1; text-align: center; }
    .cinv-sig-line {
        border-top: 1px solid #9ca3af;
        padding-top: 8px;
        font-size: 11px;
        color: #4b5563;
    }
    .cinv-sig-name {
        margin-top: 3px;
        font-size: 11px;
        color: #6b7280;
    }
    .cinv-footer {
        margin-top: {{ $compact ? '10px' : '14px' }};
        padding-top: 10px;
        border-top: 1px solid #e5e7eb;
        color: #6b7280;
        font-size: 10px;
        text-align: center;
        line-height: 1.5;
    }
    .cinv-footer strong { color: #111827; font-weight: 700; letter-spacing: 0.04em; }

    @if(($download ?? null) != 'download')
        @media not print {
            .cinv-shell { margin-top: 24px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(17, 24, 39, 0.08); }
        }
        @page { size: A4 portrait; margin: {{ $compact ? '8mm 8mm' : '10mm 10mm' }}; }
    @endif
    @media print {
        body { background: #ffffff; }
        .cinv-shell { box-shadow: none; margin: 0; border: none; padding: 0; }
        table.cinv-items tbody tr, table.cinv-pay tbody tr { page-break-inside: avoid; }
    }
</style>
