<?php

namespace Drivezy\LaravelRecordManager\Observers;

use Drivezy\LaravelUtility\Observers\BaseObserver;

/**
 * Class FormPreferenceObserver
 * @package Drivezy\LaravelRecordManager\Observers
 */
class FormPreferenceObserver extends BaseObserver
{

    /**
     * @var array
     */
    protected $rules = [];

//    /**
//     * @param Eloquent $model
//     * @return bool
//     */
//    public function saving (Eloquent $model) {
////        $isFormConfigurator = AccessManager::hasPermission('form-configurator');
////        if ( !$isFormConfigurator ) return false;
//
//        return parent::saving($model);
//    }
//
//    /**
//     * @param Eloquent $model
//     * @return bool|void
//     */
//    public function deleting (Eloquent $model) {
////        $isFormConfigurator = AccessManager::hasPermission('form-configurator');
////        if ( !$isFormConfigurator ) return false;
//
//        parent::deleting($model);
//    }

}
