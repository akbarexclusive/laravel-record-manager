<?php

use Drivezy\LaravelUtility\LaravelUtility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDzDeviceTokensTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up () {
        Schema::create('dz_device_tokens', function (Blueprint $table) {
            $userTable = LaravelUtility::getUserTable();

            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('token');
            $table->dateTime('last_access_time')->nullable();
            $table->string('version')->nullable();

            $table->unsignedBigInteger('token_source_id')->nullable();
            $table->unsignedBigInteger('platform_id')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('user_id')->references('id')->on($userTable);

            $table->foreign('token_source_id')->references('id')->on('dz_lookup_values');
            $table->foreign('platform_id')->references('id')->on('dz_lookup_values');

            $table->foreign('created_by')->references('id')->on($userTable);
            $table->foreign('updated_by')->references('id')->on($userTable);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down () {
        Schema::dropIfExists('dz_device_tokens');
    }
}
