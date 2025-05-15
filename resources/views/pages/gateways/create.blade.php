@extends('includes.layout')
@php(extract($data))
@section('css')
    <style>
        .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
            color: #495057;
            background: #e9ecef;
            border: 1px solid #dee2e6;
            border-radius: .2rem;
            cursor: default;
            float: left;
            margin: .2rem .3rem .3rem 1rem;
            padding: 0.2rem .5rem;
        }
    </style>
@endsection
@section('content')
    <div class="row px-5">
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Create Gateway</h5>
                    <h6 class="card-subtitle text-muted"></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('gateways.store')  }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="">Identifier</label>
                                <input type="text" name="identifier" class="form-control"  placeholder="Identifier" value="{{ old('identifier') }}" required >
                                @if($errors->has('name'))
                                    <div class="error text-danger mt-2">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control"  placeholder="Name" value="{{ old('name') }}" required >
                                @if($errors->has('name'))
                                    <div class="error text-danger mt-2">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">Provider</label>
                                <select class="form-control" name="provider">
                                    @foreach($providers as $provider)
                                        <option value="{{ $provider }}">{{ ($provider) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">Type</label>
                                <select class="form-control" name="type">
                                    @foreach($types as $type)
                                        <option value="{{ $type }}">{{ ($type) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-2">
                                <label for="">Status</label>
                                <select class="form-control" name="status">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="description" value="{{ old('description') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Image Url</label>
                                <input type="text" name="image_url" class="form-control" placeholder="Image url" value="{{ old('image_url') }}" >
                            </div>
                        </div>
                        <div class="form-group col-12 col-md-12">
                            <label for="currencies" class="font-weight-bold">Select Currencies</label>
                            <select class="form-control select2 currencies-select" name="currencies[]" multiple="multiple" required>
                                @foreach($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ ucfirst($currency->code) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-12">
                            <label for="config">Configuration</label>
                            <textarea class="form-control" name="config" rows="10" required></textarea>
                        </div>
                        <div class="row d-flex justify-content-center">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('js')
    <script>
        $(document).ready(function() {

            $('.select2').each(function() {
                $(this)
                    .wrap('<div class="position-relative"></div>')
                    .select2({
                        placeholder: 'Select value',
                        dropdownParent: $(this).parent()
                    });
            })

            $('.currencies-select').select2({
                // theme: "classic",
                allowClear: true,
                width: '100%' // Ensure full-width select
            });
        });
    </script>
@endsection
