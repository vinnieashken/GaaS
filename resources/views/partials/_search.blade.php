<input type="text" placeholder="{{ $search??'Search ...'}}"
       wire:model.live.debounce.500ms="search"
       id="search-input" name="search" class="form-control" placeholder=".input-sm">
