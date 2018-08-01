<?php

namespace Drivezy\LaravelRecordManager\Library;

use Drivezy\LaravelRecordManager\Models\BusinessRule;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Facades\Auth;

/**
 * Class BusinessRuleManager
 * @package Drivezy\LaravelRecordManager\Library
 */
class BusinessRuleManager {

    /**
     * @param $model
     */
    public static function getQueryStrings ($model) {
        $query = BusinessRule::where('model_id', $model->id)
            ->where('on_query', true);
        $rules = self::getRules($query);

        $records = [];
        $user = Auth::user();

        foreach ( $rules as $rule ) {
            if ( !( new BusinessRuleEvaluator($rule) )->process() )
                continue;

            $query = null;
            eval($rule->script->script);

            if ( $query )
                array_push($records, $query);
        }

        return $records;
    }

    /**
     * @param Eloquent $model
     * @return array
     */
    public static function handleCreatingRules (Eloquent $model) {
        $query = BusinessRule::where('model_hash', $model->model_hash)
            ->where('execution_type_id', 61)
            ->where('on_insert', true);

        $rules = self::getRules($query);

        return self::evaluateBusinessRules($model, $rules);
    }

    /**
     * @param Eloquent $model
     */
    public static function handleCreatedRules (Eloquent $model) {
        $query = BusinessRule::where('model_hash', $model->model_hash)
            ->where('execution_type_id', 62)
            ->where('on_insert', true);

        $rules = self::getRules($query);

        return self::evaluateBusinessRules($model, $rules);
    }

    /**
     * @param Eloquent $model
     * @return array
     */
    public static function handleUpdatingRules (Eloquent $model) {
        $query = BusinessRule::where('model_hash', $model->model_hash)
            ->where('execution_type_id', 61)
            ->where('on_update', true);

        $rules = self::getRules($query);

        return self::evaluateBusinessRules($model, $rules);
    }

    /**
     * @param Eloquent $model
     */
    public static function handleUpdateRules (Eloquent $model) {
        $query = BusinessRule::where('model_hash', $model->model_hash)
            ->where('execution_type_id', 62)
            ->where('on_update', true);

        $rules = self::getRules($query);

        return self::evaluateBusinessRules($model, $rules);
    }

    /**
     * @param Eloquent $model
     * @return array
     */
    public static function handleDeletingRules (Eloquent $model) {
        $query = BusinessRule::where('model_hash', $model->model_hash)
            ->where('execution_type_id', 61)
            ->where('on_delete', true);

        $rules = self::getRules($query);

        return self::evaluateBusinessRules($model, $rules);
    }

    /**
     * @param Eloquent $model
     */
    public static function handleDeletedRules (Eloquent $model) {
        $query = BusinessRule::where('model_hash', $model->model_hash)
            ->where('execution_type_id', 62)
            ->where('on_delete', true);

        $rules = self::getRules($query);

        return self::evaluateBusinessRules($model, $rules);
    }

    /**
     * @param Eloquent $model
     * @param $rules
     * @return Eloquent
     */
    private static function evaluateBusinessRules (Eloquent $model, $rules) {
        $user = Auth::user();

        foreach ( $rules as $rule ) {
            if ( !( new BusinessRuleEvaluator($rule, $model) )->process() )
                continue;

            //check if aborting has been done
            if ( $model->abort ) return $model;

            //check if aborting of business rule is enabled
            if ( $model->abort_business_rule ) continue;

            eval($rule->script->script);
        }

        return $model;
    }

    /**
     * @param $query
     * @return mixed
     */
    private static function getRules ($query) {
        $query = $query->where('active', true)
            ->orderBy('order', 'asc')
            ->get();

        return $query;
    }
}