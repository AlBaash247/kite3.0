<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('author_id');
            $table->unsignedBigInteger('project_id');
            $table->string('status');
            $table->string('importance');
            $table->date('due_date')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('author_id')->references('id')->on('users')->onDelete('cascade');
            // You may want to add a foreign key for project_id if you have a projects table
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
