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
        Schema::create('reviews', function (Blueprint $table) {
            $table->id(); // Cột id tự động tạo
            $table->unsignedBigInteger('customer_id');
            $table->string('content'); // Nội dung đánh giá
            $table->integer('rating'); // Điểm đánh giá
            $table->integer('status')->default(1); 
            $table->timestamps(); // Thời gian tạo và cập nhật
           
            $table->foreign('customer_id')->references('id')->on('customers');
        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
