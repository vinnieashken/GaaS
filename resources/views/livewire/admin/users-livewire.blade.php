<div>
    @php(extract($data))
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Users</h5>
                </div>

                <div class="card-body">
{{--                    <div class="row d-flex justify-content-end px-5 mb-3">--}}
{{--                        <a class="btn btn-primary text-white" href="{{ route('users.create') }}">Add User</a>--}}
{{--                    </div>--}}
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-3">
                            @include('partials._search',['search'=>'Search users ....'])
                        </div>
                    </div>

                    <div class="row mb-3 d-flex justify-content-start">
                        <div class="col-sm-1">
                            @include('partials._paginate',['pagination'=>'perPage'])
                        </div>
                        <div class="col-sm-1">
                            @include('partials._loader')
                        </div>
                    </div>
                    <table id="datatables-basic" class="table table-striped mt-2" style="width:100%">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->phone }}</td>
                                <td>{{ $user->status }}</td>
                                <td>{{ date_create($user->created_at)->format('d-M-Y H:i:s A') }}</td>
                                <td>
{{--                                    @can('edit_users')--}}
                                        <a title="edit" class="mx-2" href="{{ route('users.edit',$user) }}">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
{{--                                    @endcan--}}
{{--                                    @can('delete_users')--}}
                                        @include('partials._delete-button',['url'=>route('users.destroy',$user)])
{{--                                    @endcan--}}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>

                        </tr>
                        </tfoot>
                    </table>
                    @include('partials._pager',['items'=>$users])
                </div>

            </div>
        </div>
    </div>
</div>
