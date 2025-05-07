<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;

class DataTable extends Component
{
    use WithPagination;

    protected string $paginationTheme = 'bootstrap';

    public string $model;
    public array $columns = [];
    public array $filters = [];
    public array $search_columns = [];
    public array $actions=[];
    public string $sortField = 'id';
    public string $sortDirection = 'asc';
    public string $search = '';
    public string $perPage = '10';
    public array $relations = [];
    public array $relationsCount = [];
    public array $selectors = [];
    public array $selectorValues = []; // Store filter values here

    public function mount(array $options = [])
    {
        if (!class_exists($options['model'])) {
            throw new \Exception("Model [".$options['model']."] does not exist.");
        }

        $this->model = $options['model'];
        $this->columns = $options['columns'];
        $this->search_columns = $options['search_columns'];
        $this->actions = $options['actions'];
        $this->relations = $options['relations'];
        $this->filters = $options['filters'];
        $this->selectors = $options['selectors'];
        $this->relationsCount = $options['relationsCount'];

        // Initialize filter values
        foreach ($this->selectors as $key => $filter) {
            $this->selectorValues[$key] = "";
        }
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field && $this->sortDirection === 'asc' ? 'desc' : 'asc';
        $this->sortField = $field;
    }

    public function render()
    {
        //dd($this->filters);
        $select_columns = array_values($this->columns);
        $select_columns = array_filter($select_columns,function($val){
            return !str_contains($val,'.');
        });

        $query = $this->model::query();
        $query->select($select_columns)
            ->when(count($this->relations), function ($query) {
                $query->with($this->relations);

            })->when(count($this->relationsCount), function ($query) {
                $query->withCount($this->relationsCount);
            });
        $query->when($this->search, function ($q, $search) {
            foreach ($this->search_columns as $column => $type) {
                $criteria = $column.' like ?';
                if($type === 'numeric') {
                    if(is_numeric($search)) {
                        $q->orWhere($column,$search);
                    }
                }
                else
                    $q->orWhereRaw($criteria, ["%{$search}%"]);
            }
        });
        foreach ($this->filters as $column => $value) {
            if(is_array($value))
                $query->whereIn($column, $value);
            else
                $query->where($column, $value);
        }

        // Apply filters
        foreach ($this->selectorValues as $key => $value) {
            if (!empty($value)) {
                $query->where($key, $value);
            }
        }

        //dd($select_columns);
        $data['records'] = $query->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        $select_columns =  array_values($this->columns);

        $data['columns'] = array_keys($this->columns);
        $data['selectors'] = $select_columns;
        $data['filters'] = $this->selectors;

        return view('livewire.data-table',compact('data'));
    }
}
