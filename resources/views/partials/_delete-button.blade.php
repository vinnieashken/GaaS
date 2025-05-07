<a href="#" class="deleteDialog action-icon" data-toggle="modal" data-target="#confirmDeleteModal" data-url="{{$url??''}}">
    <i class="{{ $icon ?? '' }}"></i>
</a>

@section('confirm-modal')
<div id="confirmDeleteModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body p-4">
                <div class="text-center">
                    <i class="dripicons-warning h1 text-warning"></i>
                    <p class="mt-3"> Do you want to confirm this action?</p>
                    <form id="action" method="post" action="">
                        @csrf
                        @method($method ?? 'post')
                        <div class="text-center">
                            <input id="target" name="modal" type="hidden" value="{{old('modal')}}">
                            <div class="modal-footer text-center">
                                <button type="button" class="btn btn-secondary btn-sm btn-rounded" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger btn-sm btn-rounded">{{ isset($action_name) ? ucfirst($action_name): 'Delete' }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
@endsection
