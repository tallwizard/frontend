<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDependencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dependences', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->nullable(false);
            $table->string('name', 200)->nullable(false);
            $table->unsignedBigInteger('institutions_id');
			$table->unsignedBigInteger('users_id');
			$table->foreign('users_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('institutions_id')->references('id')->on('institutions');
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
        Schema::dropIfExists('dependences');
    }
}
