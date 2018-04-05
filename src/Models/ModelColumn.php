<?php

namespace Drivezy\LaravelRecordManager\Models;

use Drivezy\LaravelRecordManager\Observers\ModelColumnObserver;
use Drivezy\LaravelUtility\Models\BaseModel;
use Drivezy\LaravelUtility\Models\LookupValue;

/**
 * Class ModelColumn
 * @package Drivezy\LaravelRecordManager\Models
 */
class ModelColumn extends BaseModel {

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function model () {
        return $this->belongsTo(Model::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function column_type () {
        return $this->belongsTo(LookupValue::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reference_model () {
        return $this->belongsTo(Model::class);
    }

    /**
     *Override the boot functionality to add up the observer
     */
    public static function boot () {
        parent::boot();
        self::observe(new ModelColumnObserver());
    }
}