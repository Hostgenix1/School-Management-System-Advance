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
        // Student Attendance
        Schema::create('student_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'excused'])->default('present');
            $table->text('remarks')->nullable();
            $table->foreignId('marked_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['student_id', 'attendance_date']);
        });

        // Staff Attendance
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_member_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('check_in_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->enum('status', ['present', 'absent', 'late', 'half_day', 'on_leave'])->default('present');
            $table->text('remarks')->nullable();
            $table->string('qr_code_data')->nullable();
            $table->timestamps();
            
            $table->unique(['staff_member_id', 'attendance_date']);
        });

        // Exam Types
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Unit Test", "Half Yearly", "Annual"
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_session_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->text('instructions')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Exam Schedules
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room_number')->nullable();
            $table->integer('total_marks');
            $table->integer('passing_marks');
            $table->timestamps();
        });

        // Grade Systems
        Schema::create('grade_systems', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('school_session_id')->constrained()->onDelete('cascade');
            $table->char('grade');
            $table->string('grade_point')->nullable();
            $table->decimal('min_percentage', 5, 2);
            $table->decimal('max_percentage', 5, 2);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Student Exam Marks
        Schema::create('student_exam_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_schedule_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->integer('marks_obtained');
            $table->integer('total_marks');
            $table->char('grade')->nullable();
            $table->string('grade_point')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('entered_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->constrained('users')->nullable()->onDelete('set null');
            $table->timestamps();
            
            $table->unique(['exam_schedule_id', 'student_id']);
        });

        // Online Exams
        Schema::create('online_exams', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->dateTime('available_from');
            $table->dateTime('available_until');
            $table->integer('duration_minutes');
            $table->integer('total_marks');
            $table->integer('passing_marks');
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_results_immediately')->default(false);
            $table->enum('status', ['draft', 'published', 'completed'])->default('draft');
            $table->timestamps();
        });

        // Online Exam Questions
        Schema::create('online_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_id')->constrained()->onDelete('cascade');
            $table->text('question');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer', 'essay'])->default('multiple_choice');
            $table->json('options')->nullable(); // For MCQ options
            $table->text('correct_answer');
            $table->integer('marks');
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Online Exam Attempts
        Schema::create('online_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('online_exam_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->dateTime('started_at');
            $table->dateTime('submitted_at')->nullable();
            $table->integer('marks_obtained')->nullable();
            $table->enum('status', ['in_progress', 'submitted', 'timed_out'])->default('in_progress');
            $table->json('answers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_attempts');
        Schema::dropIfExists('online_exam_questions');
        Schema::dropIfExists('online_exams');
        Schema::dropIfExists('student_exam_marks');
        Schema::dropIfExists('grade_systems');
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
        Schema::dropIfExists('staff_attendances');
        Schema::dropIfExists('student_attendances');
    }
};
