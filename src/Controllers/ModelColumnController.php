<?php

namespace Drivezy\LaravelRecordManager\Controllers;

use Drivezy\LaravelRecordManager\Models\ModelColumn;

/**
 * Class ModelColumnController
 * @package Drivezy\LaravelRecordManager\Controller
 */
class ModelColumnController extends RecordController {
    /**
     * @var string
     */
    public $model = ModelColumn::class;
}