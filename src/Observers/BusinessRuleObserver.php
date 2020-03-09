<?php

namespace Drivezy\LaravelRecordManager\Observers;

use Drivezy\LaravelRecordManager\Models\DataModel;
use Drivezy\LaravelUtility\Observers\BaseObserver;
use Illuminate\Database\Eloquent\Model as Eloquent;

/**
 * Class BusinessRuleObserver
 * @package Drivezy\LaravelRecordManager\Observers
 */
class BusinessRuleObserver extends BaseObserver
{
    /**
     * @var array
     */
    protected $rules = [
        'model_id' => 'required',
    ];

    public function creating (Eloquent $model)
    {
        $model->model_hash = ( DataModel::find($model->model_id) )->model_hash;

        return parent::creating($model); // TODO: Change the autogenerated stub
    }
}
