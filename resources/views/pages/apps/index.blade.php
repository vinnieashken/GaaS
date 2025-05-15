@extends('includes.layout')

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row d-flex justify-content-end px-5 mb-3">
                <a class="btn btn-primary text-white" href="{{ route('apps.create') }}">Create App</a>
            </div>
            @livewire('data-table',['options'=> $data['options']])
        </div>
    </main>
@endsection
@section('js')

@endsection
