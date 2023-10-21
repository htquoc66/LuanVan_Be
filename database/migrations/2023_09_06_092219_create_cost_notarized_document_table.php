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
        Schema::create('cost_notarized_document', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cost_id');
            $table->unsignedBigInteger('notarized_document_id');
            $table->text('description')->nullable();
            // Các trường khác bạn cần thêm vào bảng
            $table->timestamps();

            $table->foreign('cost_id')->references('id')->on('costs');
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
        Schema::dropIfExists('cost_notarized_document');
    }
};
