<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; }
        @page { margin: 20px 24px; }
    </style>
</head>
<body>
@include('admin.reports.profitloss._header')
@include('admin.reports.profitloss._table', ['compact' => true])
</body>
</html>
