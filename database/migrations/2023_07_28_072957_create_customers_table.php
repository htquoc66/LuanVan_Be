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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('type_id')->constrained('customer_types');
            $table->string('name');
            $table->string('idCard_number');
            $table->date('idCard_issued_date');
            $table->string('idCard_issued_place');
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('phone');
            $table->string('email');
            $table->string('address');
            $table->string('password')->nullable();
            $table->string('avatar')->nullable();
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
        Schema::dropIfExists('customers');
    }
};
