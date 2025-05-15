@section('css')
    <style>
        .d-none > :first-child {
            display: none !important;
        }
    </style>
@endsection
<div>
    @php(extract($data))
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">{{ $title }}</h5>
                </div>

                <div class="card-body">
                    <div class="row d-flex justify-content-end">
                        <div class="col-sm-3">
                            @include('partials._search',['search'=>'Search ....'])
                        </div>
                    </div>

                    <div class="row mb-3 d-flex justify-content-start">
                        <div class="col-sm-1">
                            @include('partials._paginate',['pagination'=>'perPage'])
                        </div>
                        <div class="col-sm-1">
                            @include('partials._loader')
                        </div>
                        @foreach($filters as $title => $values)
                            <div class="col-md-2">
                                <div class="input-group">
                                    <select wire:model.live="selectorValues.{{$title}}" class="form-select form-control-sm" id="{{ $title }}" aria-label="{{ $title }}">
                                        <option value="">--Filter by {{$title}}--</option>
                                        @foreach($values as $value)
                                            <option value="{{ $value}}">{{ ucfirst($value) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <table id="datatables-basic" class="table table-striped mt-2" style="width:100%">
                        <thead>
                        <tr>
                           @foreach($columns as $column)
                                <th>{{ $column }}</th>
                           @endforeach
                            @if(count($actions))
                                <th>Actions</th>
                            @endif
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($records as $record)
                            <tr>
                               @foreach(array_values($selectors) as $selector)
                                    @php($property = resolve_properties($selector,$record) )
                                    <td>{{ ($property instanceof DateTime) ? date_create($property)->format('d-M-Y H:i:s A'): $property }}</td>
                               @endforeach

                                @if(count($actions))
                                <td>
                                    @foreach($actions as $action_name => $action)
                                        @if(isset($action['permission']) && $action['permission'])
                                            @can($action['permission'])
                                                @if($action['type'] == 'link')
                                                    <a title="{{ $action_name }}" class="mx-2" href="{{ route($action['route'], $record) }}">
                                                        <i class="{{ $action['icon'] }}"></i>
                                                    </a>
                                                @else
                                                    @include('partials._delete-button', [
                                                        'method' => $action['method'],
                                                        'url' => route($action['route'], $record),
                                                        'icon' => $action['icon'],
                                                        'action_name' => $action_name
                                                    ])
                                                @endif
                                            @endcan
                                        @else
                                            @if($action['type'] == 'link')
                                                <a title="{{ $action_name }}" class="mx-2" href="{{ route($action['route'], $record) }}">
                                                    <i class="{{ $action['icon'] }}"></i>
                                                </a>
                                            @else
                                                @include('partials._delete-button', [
                                                    'method' => $action['method'],
                                                    'url' => route($action['route'], $record),
                                                    'icon' => $action['icon'],
                                                    'action_name' => $action_name
                                                ])
                                            @endif
                                        @endif
                                    @endforeach
                                </td>
                                @endif
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        <tr>

                        </tr>
                        </tfoot>
                    </table>
                    @include('partials._pager',['items'=>$records])
                </div>

            </div>
        </div>
    </div>
</div>

