<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void {

        Schema::create('tasks', function (Blueprint $table) {
            
            $table->id();
            $table->string('title', 191);
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->constrained('users');
            $table->foreignId('group_id')->constrained('task_groups');
            $table->dateTime('deadline');
            $table->unsignedTinyInteger('priority');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();

        });
    }

    public function down(): void {

        Schema::dropIfExists('tasks');
        
    }
};
