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
        Schema::create('info_entries', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            // $table->integer('info_id');
            $table->enum('type', ['category', 'tag','list','note']);
            $table->integer('type_id');
            $table->enum('origin',['feed','source']);
            $table->integer('origin_id');
            // $table->integer('parent_id')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->text('contents')->nullable();
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
        Schema::dropIfExists('info_entries');
    }
};
