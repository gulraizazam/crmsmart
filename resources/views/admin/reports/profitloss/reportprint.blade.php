<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit &amp; Loss Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        @page { margin: 12px 18px; }
        @media print { * { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
@include('admin.reports.profitloss._header')
@include('admin.reports.profitloss._table')
<script>
    window.print();
    setTimeout(function () { window.close(); }, 100);
</script>
</body>
</html>
