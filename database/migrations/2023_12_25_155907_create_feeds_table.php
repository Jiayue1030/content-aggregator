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
        Schema::create('feeds', function (Blueprint $table) {
            $table->id();
            $table->integer('source_id');
            $table->string('title'); // Title of the feed item
            $table->text('description')->nullable(); // Description of the feed item
            $table->text('content')->nullable(); // Description of the feed item
            $table->string('link')->nullable(); // Link to the original article
            $table->string('guid')->nullable(); // Unique identifier for the feed item
            $table->json('categories')->nullable();
            $table->json('authors')->nullable();
            // $table->string('contents')->nullable();
            $table->timestamp('pubdate')->nullable(); // Publication date of the feed item
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
        Schema::dropIfExists('feeds');
    }
};
