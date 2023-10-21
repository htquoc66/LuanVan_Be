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
        Schema::create('law_text_notarized_document', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('law_text_id');
            $table->unsignedBigInteger('notarized_document_id');
            $table->timestamps();

            $table->foreign('law_text_id')->references('id')->on('law_texts');
            $table->foreign('notarized_document_id')->references('id')->on('notarized_documents');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('law_text_notarized_document');
    }
};
