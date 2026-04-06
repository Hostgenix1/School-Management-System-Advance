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
        // Classes table
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('numeric_name')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Sections table
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->string('room_number')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Subjects table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Subject-Teacher assignment
        Schema::create('subject_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->date('assigned_from');
            $table->date('assigned_to')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Class Timetables
        Schema::create('class_timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_number')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Student Promotions
        Schema::create('student_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('from_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('from_section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('to_class_id')->constrained('classes')->onDelete('cascade');
            $table->foreignId('to_section_id')->constrained('sections')->onDelete('cascade');
            $table->foreignId('school_session_id')->constrained('sessions')->onDelete('cascade');
            $table->enum('status', ['promoted', 'detained', 'left'])->default('promoted');
            $table->text('remarks')->nullable();
            $table->date('promotion_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
        Schema::dropIfExists('class_timetables');
        Schema::dropIfExists('subject_teachers');
        Schema::dropIfExists('subjects');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('classes');
    }
};
