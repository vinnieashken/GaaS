@extends('includes.layout')
@php(extract($data))
@section('content')
    <div class="row px-5">
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Create App</h5>
                    <h6 class="card-subtitle text-muted"></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('apps.store')  }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control"  placeholder="Name" value="{{ old('name') }}" required >
                                @if($errors->has('name'))
                                    <div class="error text-danger mt-2">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-4">
                                <label for="">Status</label>
                                <select class="form-control" name="status">
                                    @foreach($statuses as $status)
                                        <option selected value="{{ $status }}">{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(auth()->user()->designation == \App\Models\User::EXTERNAL)
                                <input type="hidden" name="designation" value="{{ ''.\App\Models\User::EXTERNAL }}">
                                <input type="hidden" name="parent_email" value="{{ auth()->user()->parent->email }}">
                            @else
                                <div class="form-group col-md-4">
                                    <label for="inputEmail4">Owner Email</label>
                                    <input type="email" name="parent_email" class="form-control"  placeholder="Optional email of parent account" value="{{ old('parent_email') }}">
                                    @if($errors->has('parent_email'))
                                        <div class="error text-danger mt-2">{{ $errors->first('parent_email') }}</div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="inputEmail4">Description</label>
                                <textarea name="description" rows="3" class="form-control w-100"></textarea>
                                @if($errors->has('description'))
                                    <div class="error text-danger mt-2">{{ $errors->first('description') }}</div>
                                @endif
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
