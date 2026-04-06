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
        // Add role column to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher', 'student', 'parent', 'staff'])->default('student')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->date('date_of_birth')->nullable()->after('phone');
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('gender');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('zip_code')->nullable()->after('state');
            $table->string('country')->default('India')->after('zip_code');
            $table->string('profile_image')->nullable()->after('country');
            $table->boolean('active')->default(true)->after('profile_image');
        });

        // Student specific information
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('admission_number')->unique();
            $table->string('class_roll_number')->nullable();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_session_id')->constrained()->onDelete('cascade');
            $table->date('admission_date');
            $table->string('previous_school')->nullable();
            $table->string('father_name');
            $table->string('father_phone')->nullable();
            $table->string('father_occupation')->nullable();
            $table->string('mother_name');
            $table->string('mother_phone')->nullable();
            $table->string('mother_occupation')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_relation')->nullable();
            $table->text('medical_conditions')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['active', 'inactive', 'graduated', 'left'])->default('active');
            $table->timestamps();
        });

        // Staff/Teacher specific information
        Schema::create('staff_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('employee_id')->unique();
            $table->string('designation');
            $table->string('department')->nullable();
            $table->date('joining_date');
            $table->date('leaving_date')->nullable();
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->text('qualifications')->nullable();
            $table->text('experience')->nullable();
            $table->text('specializations')->nullable();
            $table->enum('employment_type', ['permanent', 'contract', 'temporary', 'part_time'])->default('permanent');
            $table->enum('status', ['active', 'on_leave', 'suspended', 'left'])->default('active');
            $table->timestamps();
        });

        // Parents/Guardians table
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('father_of_student_ids')->nullable(); // JSON array of student IDs
            $table->string('mother_of_student_ids')->nullable(); // JSON array of student IDs
            $table->string('guardian_of_student_ids')->nullable(); // JSON array of student IDs
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
        Schema::dropIfExists('staff_members');
        Schema::dropIfExists('students');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role', 'phone', 'date_of_birth', 'gender', 'address', 
                'city', 'state', 'zip_code', 'country', 'profile_image', 'active'
            ]);
        });
    }
};
