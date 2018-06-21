<?php

namespace Drivezy\LaravelRecordManager\Library;

use Drivezy\LaravelRecordManager\Models\DataModel;
use Drivezy\LaravelUtility\Models\BaseModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Class DataManager
 * @package Drivezy\LaravelRecordManager\Library
 */
class DataManager {
    protected $includes, $sqlCacheIdentifier = false;

    protected $model, $base, $data;

    protected $dictionary = [];
    protected $rejectedColumns = [];
    protected $relationships = [];
    protected $layout = [];
    protected $restrictions = [];
    protected $tables = [];
    protected $sql = [];
    protected $aggregation_operator = null;
    protected $aggregation_column = null;

    protected $stats, $order = false;
    protected $limit = 20;
    protected $page = 1;

    /**
     * DataManager constructor.
     * @param $model
     * @param array $args
     */
    public function __construct ($model, $args = []) {
        $this->model = $model;

        foreach ( $args as $key => $value ) {
            $this->{$key} = $value;
        }
    }

    /**
     *
     */
    public function process ($id = null) {
        $this->model->actions = ModelManager::getModelActions($this->model);
        $this->base = strtolower($this->model->name);

        self::setReadDictionary($this->base, $this->model);

        $this->relationships[ $this->base ] = $this->model;
        $this->relationships[ $this->base ]['form_layouts'] = PreferenceManager::getFormPreference(DataModel::class, $this->model->id);

        $this->tables[ $this->base ] = $this->model->table_name;

        $this->tables[ $this->base ] = [
            'table' => $this->model->table_name,
            'join'  => null,
        ];

        array_push($this->restrictions, '`' . $this->base . '`.deleted_at is null');
    }

    /**
     * This will create the join condition for the alias as part of its relationship with the parent one
     * @param $relationship
     * @param $base
     */
    protected function setupColumnJoins ($model, $relationship, $base) {
        $source = (object) [
            'base'   => $base,
            'table'  => $model->table_name,
            'column' => $relationship->source_column ? $relationship->source_column->name : 'id',
        ];

        $alias = (object) [
            'base'   => $base . '.' . $relationship->name,
            'table'  => $relationship->reference_model->table_name,
            'column' => $relationship->alias_column ? $relationship->alias_column->name : 'id',
        ];


        $joinCondition = '`' . $source->base . '`.' . $source->column . ' = `' . $alias->base . '`.' . $alias->column;

        //check for additional join condition
        if ( $relationship->join_definition ) {
            $join = str_replace('current', '`' . $source->base . '`', $relationship->join_definition);
            $join = str_replace('alias', '`' . $alias->base . '`', $join);

            $joinCondition .= 'AND ' . $join;
        }

        $join = '`' . $alias->base . '`.deleted_at is null';
        array_push($this->restrictions, $join);

        $this->tables[ $alias->base ] = [
            'table' => $alias->table,
            'join'  => $joinCondition,
        ];
    }

    /**
     * Get the select items which are to be part of the record
     * Also create necessary alias and the return element
     * @return string
     */
    private function getSelectItems () {
        self::fixSelectItems();

        $query = '';
        foreach ( $this->layout as $key => $value ) {
            if ( !$query )
                $query = $value . ' as \'' . $key . '\'';
            else
                $query .= ', ' . $value . ' as \'' . $key . '\'';
        }

        return $query;
    }

    /**
     * Get the select items which are part of the requested layout
     * Also load the parent items part of the dictionary
     */
    private function fixSelectItems () {
        $columns = [];
        foreach ( $this->dictionary[ $this->base ] as $item ) {
            $columns[ $this->base . '.' . $item->name ] = '`' . $this->base . '`.' . $item->name;
        }

        //add only those columns which are permitted for the user
        foreach ( $this->layout as $item ) {
            $name = $item['object'] . '.' . $item['column'];
            if ( !in_array($name, $this->rejectedColumns) )
                $columns[ $name ] = '`' . $item['object'] . '`.' . $item['column'];
        }

        foreach ( $this->relationships as $key => $value ) {
            $columns[ $key . '.id' ] = '`' . $key . '`.id';
        }
        $this->layout = $columns;
    }

    /**
     * Check if the cache against the sql conditions is present
     * If yes then load back to the system
     * @return array|bool|mixed
     */
    protected function loadDataFromCache () {
        if ( !$this->sqlCacheIdentifier ) return false;

        $record = Cache::get($this->sqlCacheIdentifier, false);
        if ( !$record ) return false;

        $this->sql = $record->sql;

        return true;
    }

    /**
     * Create the sql join against the tables that are attached as part of the inclusions
     * This is part of the where condition
     * @return mixed|string
     */
    private function getJoins () {
        $query = '';
        foreach ( $this->restrictions as $join ) {
            if ( !$join ) continue;

            if ( $query )
                $query .= ' AND ' . $join;
            else
                $query = $join;
        }

        return $query;
    }

    /**
     * create array of  necessary join conditions against the tables that are part of the includes.
     * @return string
     */
    private function getTableDefinitions () {
        $query = '';
        foreach ( $this->tables as $key => $value ) {
            if ( $query )
                $query .= ' LEFT JOIN ' . $value['table'] . ' `' . $key . '`';
            else
                $query = $value['table'] . ' `' . $key . '`';

            if ( $value['join'] )
                $query .= ' ON ' . $value['join'];
        }

        return $query;
    }

    /**
     * Create the data related to base query excluding the restrictive condition
     * Then save it to the cache so that it can be fetched
     * back without need of too much query iteration
     */
    protected function constructQuery () {
        $this->sql['columns'] = self::getSelectItems();
        $this->sql['tables'] = self::getTableDefinitions();
        $this->sql['joins'] = self::getJoins() ? : ' 1 = 1';

        $this->sqlCacheIdentifier = md5($this->model->model_hash . '-' . microtime('true') . '-' . md5($this->includes));
        Cache::put($this->sqlCacheIdentifier, (object) [
            'user_id' => Auth::id(),
            'sql'     => $this->sql,
            'time'    => strtotime('now'),
        ], 30);
    }

    /**
     * @param $base
     * @param $model
     */
    protected function setReadDictionary ($base, $model) {
        $columns = ModelManager::getModelDictionary($model, 'r');
        $this->dictionary[ $base ] = $columns->allowed;

        foreach ( $columns->restrictedIdentifiers as $item )
            array_push($this->rejectedColumns, $base . '.' . $item);
    }
}