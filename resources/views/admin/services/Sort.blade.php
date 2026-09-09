@extends('admin.layouts.master')

@section('content')
    @push('css')
        <link href="{{ asset('assets/css/sneat-consultancies.css') }}?v=9" rel="stylesheet" type="text/css" />
        <link href="{{ asset('assets/css/sneat-services.css') }}?v=1" rel="stylesheet" type="text/css" />
    @endpush

    <div class="content d-flex flex-column flex-column-fluid sneat-appt-page sneat-services-page" id="kt_content">
        <div class="d-flex flex-column-fluid">
            <div class="container-fluid sneat-page">
                <div class="card card-custom sneat-page-card">
                    <div class="card-header">
                        <div class="card-title sneat-page-title-wrap">
                            <h3 class="card-label">Sort Services</h3>
                        </div>
                        <div class="card-toolbar">
                            <a href="{{route('admin.services.index')}}" class="btn btn-primary">
                                <i class="fa fa-arrow-left"></i> back
                            </a>
                        </div>
                    </div>
                    <div class="row mr-2 ml-2 mt-5 sneat-service-sort-zone">
                        <div class="col-lg-12 services-draggable-zone" id="services-draggable-zone">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script src="{{asset('assets/plugins/custom/draggable/draggable.bundle.js?v=7.2.9')}}"></script>
        <script src="{{asset('assets/js/pages/services-sort.js')}}"></script>
    @endpush

@endsection
