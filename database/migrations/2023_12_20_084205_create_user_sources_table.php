<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_sources', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('source_id');
            $table->string('name')->nullable();
            $table->string('reference')->nullable();
            $table->enum('status', ['active', 'suspended','disabled'])->nullable()->default('active');
            $table->integer('created_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_sources');
    }
};
