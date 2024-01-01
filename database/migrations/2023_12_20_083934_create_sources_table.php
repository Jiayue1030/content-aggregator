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
        Schema::create('sources', function (Blueprint $table) {
            //crawlerController -> readRss
            $table->id();
            $table->string('title');
            $table->string('url'); //url source from user
            $table->string('rss_url')->nullable(); //real rss subscribe url
            $table->string('link')->nullable(); //Public website from the rss source
            $table->integer('created_by');
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_rss')->nullable()->default(false);
            $table->string('language')->nullable()->default('en');
            $table->json('metadata')->nullable();
            $table->json('author')->nullable();
            $table->timestamps();
            $table->softDeletes($column = 'deleted_at', $precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sources');
    }
};
