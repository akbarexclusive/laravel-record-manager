<?php

namespace Drivezy\LaravelRecordManager\Controller;

use Drivezy\LaravelAccessManager\Models\Route;

/**
 * Class RouteController
 * @package Drivezy\LaravelRecordManager\Controller
 */
class RouteController extends ReadRecordController {
    /**
     * @var string
     */
    public $model = Route::class;
}