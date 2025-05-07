
@extends('includes.layout')

@section('content')
    <main class="content">
        <div class="container-fluid p-0">
            <div class="row d-flex justify-content-end px-5 mb-3">
                <a class="btn btn-primary text-white" href="{{ route('users.create') }}">Add User</a>
            </div>
{{--            @livewire('admin.users-livewire')--}}

            @livewire('data-table',['options'=> $data['options']])

{{--            @livewire('users-table')--}}
{{--            <livewire:user-table theme="bootstrap-4"/>--}}
        </div>
    </main>
@endsection
@section('js')

@endsection
