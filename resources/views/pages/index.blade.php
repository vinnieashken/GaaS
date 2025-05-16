
@extends('includes.layout')
@php(extract($data))
@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row">
                <div class="col-12 col-md-6 col-xl d-flex">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="row">
                                <div class="col-8">
                                    <h3 class="mb-2">4.562</h3>
                                    <div class="mb-0">Sales Today</div>
                                </div>
                                <div class="col-4 ml-auto text-right">
                                    <div class="d-inline-block mt-2">
                                        <i class="feather-lg text-primary" data-feather="truck"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl d-flex">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="row">
                                <div class="col-8">
                                    <h3 class="mb-2">27.424</h3>
                                    <div class="mb-0">Visitors Today</div>
                                </div>
                                <div class="col-4 ml-auto text-right">
                                    <div class="d-inline-block mt-2">
                                        <i class="feather-lg text-warning" data-feather="activity"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl d-flex">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="row">
                                <div class="col-8">
                                    <h3 class="mb-2">$ 29.200</h3>
                                    <div class="mb-0">Total Earnings</div>
                                </div>
                                <div class="col-4 ml-auto text-right">
                                    <div class="d-inline-block mt-2">
                                        <i class="feather-lg text-danger" data-feather="dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl d-flex">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="row">
                                <div class="col-8">
                                    <h3 class="mb-2">67</h3>
                                    <div class="mb-0">Pending Orders</div>
                                </div>
                                <div class="col-4 ml-auto text-right">
                                    <div class="d-inline-block mt-2">
                                        <i class="feather-lg text-success" data-feather="shopping-cart"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-xl d-none d-xxl-flex">
                    <div class="card flex-fill">
                        <div class="card-body py-4">
                            <div class="row">
                                <div class="col-8">
                                    <h3 class="mb-2">$ 49.400</h3>
                                    <div class="mb-0">Total Revenue</div>
                                </div>
                                <div class="col-4 ml-auto text-right">
                                    <div class="d-inline-block mt-2">
                                        <i class="feather-lg text-info" data-feather="dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@section('js')

@endsection

