@extends('includes.layout')
@php(extract($data))
@section('content')
    <div class="row px-5">
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">View App</h5>
                    <h6 class="card-subtitle text-muted"></h6>
                </div>
                <div class="card-body">
                    <div>
                        <p>
                            <b>APP NAME&nbsp;&nbsp;</b>{{ $app->name }}
                        </p>
                        <p>
                            <b>CLIENT ID&nbsp;&nbsp;</b>{{$app->key}}
                        </p>
                        <p>
                            <b>CLIENT SECRET&nbsp;&nbsp;</b>{{$app->secret}}
                        </p>
                        <p>
                            <b>OWNER&nbsp;&nbsp;</b>{{ $app->user->email }}
                        </p>
                        <p>
                            <b>GATEWAYS&nbsp;&nbsp;</b> @foreach($app->gateways as $gateway) {{ $gateway->name.','   }}@endforeach
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

