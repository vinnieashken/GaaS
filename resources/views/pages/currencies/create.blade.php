@extends('includes.layout')
@php(extract($data))
@section('content')
    <div class="row px-5">
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Create Currency</h5>
                    <h6 class="card-subtitle text-muted"></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('currencies.store')  }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control"  placeholder="Name" value="{{ old('name') }}" required >
                                @if($errors->has('name'))
                                    <div class="error text-danger mt-2">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">Code</label>
                                <input type="text" name="code" class="form-control"  placeholder="Currency Code e.g KES" value="{{ old('code') }}" required >
                                @if($errors->has('code'))
                                    <div class="error text-danger mt-2">{{ $errors->first('code') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">Country Code</label>
                                <input type="text" name="country" class="form-control"  placeholder="Country code e.g US" value="{{ old('country') }}" required >
                                @if($errors->has('country'))
                                    <div class="error text-danger mt-2">{{ $errors->first('country') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-3">
                                <label for="">Status</label>
                                <select class="form-control" name="status">
                                    @foreach($statuses as $status)
                                        <option selected value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
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
