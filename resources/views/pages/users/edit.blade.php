@extends('includes.layout')
@php(extract($data))
@section('content')
    <div class="row px-5">
        <div class="col-md-12 mt-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Update User</h5>
                    <h6 class="card-subtitle text-muted"></h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('users.update',$user)  }}">
                        @method('PATCH')
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="inputEmail4">Email</label>
                                <input type="email" name="email" class="form-control"  placeholder="Email" value="{{ old('email',$user->email) }}" required>
                                @if($errors->has('email'))
                                    <div class="error text-danger mt-2">{{ $errors->first('email') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Name</label>
                                <input type="text" name="name" class="form-control"  placeholder="Name" value="{{ old('name',$user->name) }}" required >
                                @if($errors->has('name'))
                                    <div class="error text-danger mt-2">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="">Password *( Leave blank if not changing )</label>
                                <input type="password" name="password" class="form-control"  placeholder="Password" value="" >
                                @if($errors->has('password'))
                                    <div class="error text-danger mt-2">{{ $errors->first('password') }}</div>
                                @endif
                            </div>
                            <div class="form-group col-md-6">
                                <label for="">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control" value=""  placeholder="Confirm Password" >
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label for="">Status</label>
                                <select class="form-control" name="status">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}"
                                                @if($status === $user->status)
                                                    selected
                                                @endif
                                        >{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(auth()->user()->designation == \App\Models\User::EXTERNAL)
                                <input type="hidden" name="designation" value="{{ ''.\App\Models\User::EXTERNAL }}">
                                <input type="hidden" name="parent_email" value="{{ auth()->user()->parent->email }}">
                            @else
                            <div class="form-group col-md-3">
                                <label for="">Category</label>
                                <select class="form-control" name="designation">
                                    @foreach($designations as $designation)
                                        <option @if($user->designation) selected @endif value="{{ $designation }}">{{ ucfirst($designation) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="inputEmail4">Parent Email</label>
                                <input type="email" name="parent_email" class="form-control"  placeholder="Optional email of parent account" value="{{ old('parent_email',($user->id !== $user->parent_id? @$user->parent->email:null)) }}">
                                @if($errors->has('parent_email'))
                                    <div class="error text-danger mt-2">{{ $errors->first('parent_email') }}</div>
                                @endif
                            </div>
                            @endif
                        </div>

                        <div class="form-row mt-1">
                            <div class="form-group w-100">
                                <label class="form-label">Permissions</label>
                                <p class="text-info">
                                    <strong>
                                        This permission is necessary otherwise the user will not access the
                                        dashboard at all. If you wish to block a user simply deactivate
                                        their accounts from you dashboard
                                    </strong>
                                </p>
                                @error('permissions')
                                <div class="is-invalid text-danger ">
                                    {{ $message }}
                                </div>
                                @enderror
                                <div class="row">
                                    @foreach($permissions->chunk(6) as $groups)

                                        @foreach($groups as $key=> $group)
                                            <div class="col-md-3 mb-3">
                                                <h4 class="section-title">{{ucwords(str_replace('_', ' ', $key))}}</h4>
                                                @foreach($group as $permission)
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" class="custom-control-input"
                                                               id="customCheck{{$permission->id}}"
                                                               name="permissions[]"
                                                               @if(in_array($permission->id, old('permissions')?? $user->permissions->pluck('id')->toArray() ))
                                                               checked
                                                               @endif
                                                               value="{{$permission->id}}">
                                                        <label class="custom-control-label"
                                                               for="customCheck{{$permission->id}}">
                                                            {{$permission->display_name}}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
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

