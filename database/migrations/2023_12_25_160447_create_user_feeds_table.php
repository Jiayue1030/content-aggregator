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
        Schema::create('user_feeds', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('source_id');
            $table->integer('feed_id');
            $table->json('references')->nullable();
            $table->enum('status', ['active', 'suspended','disabled'])->nullable()->default('active');
            $table->boolean('is_read')->default(false);
            $table->boolean('is_star')->default(false);
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
        Schema::dropIfExists('user_feeds');
    }
};
