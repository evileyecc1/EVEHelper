<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAllianceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alliances', function (Blueprint $table) {
            $table->unsignedInteger('alliance_id')->primary();
            $table->unsignedInteger('creator_corporation_id');
            $table->unsignedInteger('creator_id');
            $table->unsignedInteger('executor_corporation_id')->nullable();
            $table->unsignedInteger('faction_id')->nullable();
            $table->dateTime('date_founded');
            $table->string('name');
            $table->string('ticker');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alliances');
    }
}
