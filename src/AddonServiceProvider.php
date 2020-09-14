<?php

namespace DoDSoftware\DynamicFieldHintsForBackpack;

use Backpack\CRUD\app\Library\CrudPanel\CrudPanel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AddonServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from PostgreSQL connections
         */
        CrudPanel::macro('getPostgresColumnComments', function($model) {

            /** @var Model $model */
            $connectionName = $model->getConnectionName();
            $dbSettings = config("database.connections.$connectionName");

            $database = Arr::get($dbSettings, 'database');
            $schema   = Arr::get($dbSettings, 'schema');
            $table    = $model->getTable();
            $query    = "SELECT
                                cols.column_name,
                                (
                                    SELECT
                                        pg_catalog.col_description(c.oid, cols.ordinal_position::int)
                                    FROM
                                        pg_catalog.pg_class c
                                    WHERE
                                        c.oid = (SELECT ('\"' || cols.table_name || '\"')::regclass::oid)
                                        AND c.relname = cols.table_name
                                ) AS column_comment
                            FROM
                                information_schema.columns cols
                            WHERE
                                cols.table_catalog    = '$database'
                                AND cols.table_name   = '$table'
                                AND cols.table_schema = '$schema';";

            $normalizedDetails = [];
            if ($columnDetails =  DB::connection($connectionName)->select(DB::raw($query))) {
                foreach ($columnDetails as $column) {
                    $normalizedDetails[$column->column_name] = trim($column->column_comment);
                }
            }
            return $normalizedDetails;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from MySQL connections
         */
        CrudPanel::macro('getMysqlColumnComments', function($model) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $table      = $model->getTable();
            $connection = $model->getConnectionName();
            $columns    = DB::connection($connection)->select(DB::raw('SHOW FULL COLUMNS FROM '.$table.';'));

            $normalizedDetails = [];
            if (is_countable($columns)) {
                foreach ($columns as $column) {
                    $normalizedDetails[$column->Field] = trim($column->Comment);
                }
            }
            return $normalizedDetails;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from MS SQL Server connections
         */
        CrudPanel::macro('getSqlserverColumnComments', function($model) {
            /** @var \Illuminate\Database\Eloquent\Model $model */
            $table      = $model->getTable();
            $query      = "SELECT    T.name AS Table_Name ,
                                      C.name AS column_name ,
                                      EP.value AS column_comment
                            FROM      sys.tables AS T
                            JOIN      sys.columns C 
                            ON        T.object_id = C.object_id
                            LEFT JOIN sys.extended_properties EP 
                            ON        T.object_id = EP.major_id 
                            AND       C.column_id = EP.minor_id
                            AND       T.name = '$table';";
            $columns = DB::connection($model->getConnectionName())->select(DB::raw($query));

            $normalizedDetails = [];
            if (is_countable($columns)) {
                foreach ($columns as $column) {
                    $normalizedDetails[$column->column_name] = trim($column->column_comment);
                }
            }
            return $normalizedDetails;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from the current model's connection
         */
        CrudPanel::macro('getColumnComments', function() {

            /** @var $this CrudPanel*/
            $model      = $this->getModel();
            /** @var $instance Model*/
            $instance   = new $model;
            $dbSettings = config("database.connections.{$instance->getConnectionName()}");
            $columns    = [];

            if ($driver = Arr::get($dbSettings, 'driver')) {
                switch ($driver) {
                    case 'mysql':
                        $columns = $this->getMysqlColumnComments($model);
                        break;
                    case 'sqlsrv':
                        $columns = $this->getSqlserverColumnComments($model);
                        break;
                    case 'pgsql':
                        $columns = $this->getPostgresColumnComments($model);
                        break;
                }
            }
            return $columns;
        });

        /**
         * Add a method to the CrudPanel object whose respoinsibility it will be to
         * get column comments from the current model's connection and add them as hints
         * on the currently configured _fields
         */
        CrudPanel::macro('setFieldHintsFromColumnComments', function() {
            /** @var $this CrudPanel*/
            $columns = $this->getColumnComments();

            if (is_countable($columns)) {
                /** @var $this CrudPanel*/
                $fields     = $this->fields();
                foreach ($fields as $key => $field) {
                    if (!isset($field['hint']) && isset($field['name'])) {
                        $columnComment = Arr::get($columns, $field['name']);
                        if ($columnComment) {
                            /** @var $this CrudPanel*/
                            $this->modifyField($field['name'], ['hint' => trim($columnComment)]);
                        }
                    }
                }
            }
        });
    }
}