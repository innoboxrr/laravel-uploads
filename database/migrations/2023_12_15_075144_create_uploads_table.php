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
        Schema::create('uploads', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('filename');
            $table->string('mime_type');
            $table->string('extension');
            $table->string('size'); // in bytes
            $table->string('path');
            $table->string('disk');
            $table->string('visibility')->default('public');
            $table->string('uploadable_type')->nullable();
            $table->string('uploadable_id')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('uploads');
    }
};
