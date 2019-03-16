<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorporationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporations', function (Blueprint $table) {
            $table->unsignedInteger('corporation_id')->primary();
            $table->unsignedInteger('alliance_id')->nullable();
            $table->unsignedInteger('ceo_id');
            $table->unsignedInteger('creator_id');
            $table->dateTime('date_founded')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('faction_id')->nullable();
            $table->unsignedInteger('home_station_id')->nullable();
            $table->unsignedInteger('member_count');
            $table->string('name');
            $table->unsignedBigInteger('shares')->nullable();
            $table->float('tax_rate');
            $table->string('ticker');
            $table->string('url')->nullable();
            $table->boolean('war_eligible')->nullable();
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
        Schema::dropIfExists('corporations');
    }
}
