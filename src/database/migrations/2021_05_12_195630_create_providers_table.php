<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('type_clients_id', 10)->nullable(false);
            $table->string('type_regimes_id')->nullable(false);
            $table->string('type_documents_id')->nullable(false);
            $table->string('document', 100)->nullable(false)->unique();
            $table->string('office_name', 200)->nullable(false);
            $table->string('phone', 20)->nullable(false);
            $table->string('email', 100)->nullable(false);
            $table->unsignedBigInteger('cities_id');
            $table->string('address', 200)->nullable(false);
            $table->string('agent_name', 200)->nullable(false);
            $table->string('agent_document', 100)->nullable(false);
            $table->string('email_autoship', 100)->nullable(false);
            $table->enum('dian_test', [1, 2]);
            $table->unsignedBigInteger('software_data_id');
			$table->unsignedBigInteger('users_id');
			$table->foreign('users_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('type_clients_id')->references('id')->on('type_clients')->onUpdate('cascade');
            $table->foreign('type_regimes_id')->references('id')->on('type_regimes')->onUpdate('cascade');
            $table->foreign('type_documents_id')->references('id')->on('type_documents')->onUpdate('cascade');
            $table->foreign('cities_id')->references('id')->on('cities')->onUpdate('cascade');
            $table->foreign('software_data_id')->references('id')->on('software_data')->onUpdate('cascade');
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
        Schema::dropIfExists('providers');
    }
}
