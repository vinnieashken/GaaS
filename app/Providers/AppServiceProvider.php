<?php

namespace App\Providers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    public static $fullTextIndexes = [];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $token_expiry = (int)env('APP_TOKEN_EXPIRY') ?? 2592000 ;
        Passport::enablePasswordGrant();
        //set access token to expire after x days
        Passport::tokensExpireIn(Carbon::now()->addSeconds($token_expiry));
        // Set refresh token to expire after x days
        Passport::refreshTokensExpireIn(Carbon::now()->addSeconds($token_expiry));
        // Extend the PostgresGrammar to support CITEXT
        Schema::macro('useCitextGrammar', function () {
            DB::connection()->setSchemaGrammar(new class(DB::connection()) extends PostgresGrammar {
                public function __construct($connection)
                {
                    parent::__construct($connection);
                }

                protected function typeCitext()
                {
                    return 'CITEXT';
                }
            });
        });

        // Apply the custom grammar if using PostgreSQL
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            Schema::useCitextGrammar();
        }
        //Create macro for postgresql citext
        Blueprint::macro('citext', function ($column) {
            if (Schema::getConnection()->getDriverName() === 'pgsql') {
                return $this->addColumn('citext', $column);
            } else {
                // Fallback for MySQL (Use STRING)
                return $this->string($column);
            }
        });

        Blueprint::macro('fullTextSearch', function ($column,$nullable=false) {
            if (Schema::getConnection()->getDriverName() === 'mysql') {
                // Use MySQL fullText() indexing
                return $this->fullText($column);
            } elseif (Schema::getConnection()->getDriverName() === 'pgsql') {
                $table = $this->getTable();
                // PostgreSQL: Add a separate tsvector column
                $tsvColumn = "tsv_{$column}";
//                if($nullable)
//                    $this->text($tsvColumn)->nullable();
//                else
//                    $this->text($tsvColumn);
                // Create the GIN index in raw SQL
                // Store this table/column for later processing
                AppServiceProvider::$fullTextIndexes[] = [
                    'table' => $this->getTable(),
                    'column' => $column,
                    'tsvColumn' => $tsvColumn,
                ];

                // Store in a static property to persist the indexes
                static $fullTextIndexes = [];
                $fullTextIndexes[] = compact('table', 'column', 'tsvColumn');

                // Store indexes in the app instance for later execution
                app()->singleton('pending_fulltext_indexes', function () {
                    return [];
                });

                app()->extend('pending_fulltext_indexes', function ($indexes) use ($table, $column, $tsvColumn) {
                    $indexes[] = compact('table', 'column', 'tsvColumn');
                    return $indexes;
                });

                // Run when the app is terminating (ensuring migrations are finished)
                app()->terminating(function () {
                    $indexes = app('pending_fulltext_indexes');
                    foreach ($indexes as $index) {
                        DB::statement("ALTER TABLE {$index['table']} ADD COLUMN {$index['tsvColumn']} tsvector GENERATED ALWAYS AS (to_tsvector('english', {$index['column']})) STORED");
                        DB::statement("CREATE INDEX {$index['tsvColumn']}_gin ON {$index['table']} USING GIN (to_tsvector('english', {$index['column']}))");
                    }
                });

                return $this->text($column);
            }
        });
    }


}
