<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HasFullTextSearch
{
    /**
     * Perform a full-text search on the specified columns.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $searchTerm
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFullTextSearch($query, string $searchTerm, array $columns = [])
    {
        $driver = config('database.default');
        $table = $query->getModel()->getTable();

        if (empty($columns)) {
            // If no columns are provided, default to all string-based columns
            $columns = $this->getSearchableColumns($table);
        }

        if ($driver === 'pgsql') {
            // PostgreSQL assumes a tsvector column exists
            $tsvectorColumn = "tsv_" . $columns[0]; // Assumes naming pattern like `tsv_content`
            return $query->whereRaw("$tsvectorColumn @@ plainto_tsquery(?)", [$searchTerm]);
        } elseif ($driver === 'mysql') {
            // MySQL uses MATCH ... AGAINST
            $matchColumns = implode(',', $columns);
            return $query->whereRaw("MATCH($matchColumns) AGAINST(?)", [$searchTerm]);
        }

        // Default fallback (if no full-text support)
        return $query->where(function ($q) use ($columns, $searchTerm) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%$searchTerm%");
            }
        });
    }

    /**
     * Get all string-based columns from the table if no columns are provided.
     *
     * @param string $table
     * @return array
     */
    protected function getSearchableColumns(string $table): array
    {
        $columns = DB::select("SELECT column_name, data_type
                               FROM information_schema.columns
                               WHERE table_name = ?", [$table]);

        return collect($columns)
            ->filter(fn($col) => in_array($col->data_type, ['text', 'varchar', 'char']))
            ->pluck('column_name')
            ->toArray();
    }
}
