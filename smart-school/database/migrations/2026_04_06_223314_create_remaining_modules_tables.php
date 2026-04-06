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
        // Lesson Plans
        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('lesson_date');
            $table->string('topic');
            $table->text('objectives')->nullable();
            $table->text('teaching_methodology')->nullable();
            $table->text('resources_required')->nullable();
            $table->text('homework_assigned')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed'])->default('planned');
            $table->timestamps();
        });

        // Notices/Communications
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->enum('audience', ['all', 'students', 'teachers', 'parents', 'staff'])->default('all');
            $table->json('target_ids')->nullable(); // Specific user/class/section IDs
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Download Center
        Schema::create('downloads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['assignment', 'study_material', 'syllabus', 'notice', 'other'])->default('other');
            $table->foreignId('class_id')->constrained()->nullable()->onDelete('set null');
            $table->foreignId('subject_id')->constrained()->nullable()->onDelete('set null');
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->nullable(); // in KB
            $table->integer('download_count')->default(0);
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Homework
        Schema::create('homework_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->date('assigned_date');
            $table->date('due_date');
            $table->integer('max_marks')->nullable();
            $table->string('attachment')->nullable();
            $table->enum('status', ['active', 'completed', 'archived'])->default('active');
            $table->timestamps();
        });

        // Homework Submissions
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_assignment_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->text('submission_text')->nullable();
            $table->string('attachment')->nullable();
            $table->dateTime('submitted_at');
            $table->integer('marks_obtained')->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->enum('status', ['pending', 'submitted', 'graded', 'late'])->default('pending');
            $table->timestamps();
        });

        // Library - Book Categories
        Schema::create('book_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Library - Books
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('publisher')->nullable();
            $table->string('isbn')->nullable();
            $table->foreignId('category_id')->constrained('book_categories')->onDelete('set null');
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies');
            $table->string('rack_number')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->year('published_year')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Library - Book Issues
        Schema::create('book_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('issue_number')->unique();
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->boolean('is_returned')->default(false);
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('returned_to')->constrained('users')->nullable()->onDelete('set null');
            $table->timestamps();
        });

        // Inventory - Item Categories
        Schema::create('item_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Inventory - Items
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('item_code')->unique();
            $table->foreignId('category_id')->constrained('item_categories')->onDelete('set null');
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('unit')->default('piece');
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->integer('reorder_level')->nullable();
            $table->string('location')->nullable();
            $table->foreignId('supplier_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('purchase_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Inventory - Item Transactions
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('transaction_type', ['in', 'out', 'adjustment']);
            $table->integer('quantity');
            $table->text('reason')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Transport - Routes
        Schema::create('transport_routes', function (Blueprint $table) {
            $table->id();
            $table->string('route_name');
            $table->string('route_number')->unique();
            $table->text('route_description')->nullable();
            $table->string('start_point');
            $table->string('end_point');
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('fare', 10, 2);
            $table->time('departure_time')->nullable();
            $table->time('arrival_time')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->integer('capacity')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Transport - Route Stops
        Schema::create('transport_route_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('transport_routes')->onDelete('cascade');
            $table->string('stop_name');
            $table->string('location')->nullable();
            $table->time('arrival_time')->nullable();
            $table->integer('stop_order');
            $table->timestamps();
        });

        // Hostel - Hostel Buildings
        Schema::create('hostels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['boys', 'girls', 'mixed'])->default('mixed');
            $table->text('address')->nullable();
            $table->string('warden_name')->nullable();
            $table->string('warden_phone')->nullable();
            $table->integer('total_capacity')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Hostel - Rooms
        Schema::create('hostel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained()->onDelete('cascade');
            $table->string('room_number');
            $table->enum('type', ['single', 'double', 'triple', 'dormitory'])->default('double');
            $table->integer('capacity');
            $table->decimal('monthly_fare', 10, 2);
            $table->text('amenities')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Hostel - Room Assignments
        Schema::create('hostel_room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained('hostel_rooms')->onDelete('cascade');
            $table->date('check_in_date');
            $table->date('check_out_date')->nullable();
            $table->decimal('monthly_fare', 10, 2);
            $table->enum('status', ['active', 'checked_out'])->default('active');
            $table->timestamps();
        });

        // Certificates
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['achievement', 'participation', 'completion', 'id_card', 'bonafide', 'transfer'])->default('achievement');
            $table->text('template_html')->nullable();
            $table->string('background_image')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Certificate Issuances
        Schema::create('certificate_issuances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('certificate_templates')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->string('certificate_number')->unique();
            $table->date('issue_date');
            $table->text('custom_data')->nullable(); // JSON for dynamic content
            $table->string('generated_file')->nullable();
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Front CMS - Pages
        Schema::create('cms_pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('content');
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->boolean('is_published')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Front CMS - Menus
        Schema::create('cms_menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('url')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('cms_menus')->onDelete('cascade');
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Front CMS - Events
        Schema::create('cms_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime')->nullable();
            $table->string('location')->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Front CMS - Gallery
        Schema::create('cms_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path');
            $table->foreignId('album_id')->nullable()->constrained('cms_pages')->onDelete('set null');
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Front CMS - News
        Schema::create('cms_news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('featured_image')->nullable();
            $table->date('publish_date');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Alumni
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->string('current_job_title')->nullable();
            $table->string('current_company')->nullable();
            $table->text('work_experience')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Alumni Events
        Schema::create('alumni_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->dateTime('event_datetime');
            $table->string('location')->nullable();
            $table->string('featured_image')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Calendar Events
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime')->nullable();
            $table->enum('event_type', ['academic', 'exam', 'holiday', 'meeting', 'personal', 'other'])->default('other');
            $table->enum('visibility', ['public', 'private', 'staff_only', 'students_only'])->default('public');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // e.g., "daily", "weekly", "monthly"
            $table->timestamps();
        });

        // To-Do Lists
        Schema::create('todo_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
        });

        // Chat Messages
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // System Settings
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->nullable();
            $table->timestamps();
        });

        // Addons/Extensions
        Schema::create('addons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unique_key')->unique();
            $table->text('description')->nullable();
            $table->string('version')->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // Behaviour Records
        Schema::create('behaviour_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['positive', 'negative', 'neutral'])->default('neutral');
            $table->string('category')->nullable(); // e.g., "Discipline", "Achievement", "Attendance"
            $table->text('description');
            $table->date('incident_date');
            $table->text('action_taken')->nullable();
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Online Courses
        Schema::create('online_courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->string('thumbnail')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->integer('duration_hours')->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced'])->default('beginner');
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        // Course Lessons
        Schema::create('course_lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('online_courses')->onDelete('cascade');
            $table->string('title');
            $table->text('content')->nullable();
            $table->string('video_url')->nullable();
            $table->integer('duration_minutes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_free_preview')->default(false);
            $table->timestamps();
        });

        // Course Enrollments
        Schema::create('course_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('online_courses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('enrollment_date');
            $table->enum('status', ['active', 'completed', 'dropped'])->default('active');
            $table->integer('progress_percentage')->default(0);
            $table->date('completed_at')->nullable();
            $table->timestamps();
        });

        // Zoom/Gmeet Meetings
        Schema::create('virtual_meetings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('platform', ['zoom', 'gmeet'])->default('zoom');
            $table->foreignId('host_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('scheduled_at');
            $table->integer('duration_minutes');
            $table->string('meeting_id')->nullable();
            $table->string('meeting_password')->nullable();
            $table->string('join_url')->nullable();
            $table->enum('audience', ['all', 'students', 'teachers', 'staff'])->default('all');
            $table->json('participant_ids')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->enum('status', ['scheduled', 'started', 'ended', 'cancelled'])->default('scheduled');
            $table->timestamps();
        });

        // Multi-Branch Support
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('principal_name')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Add branch_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained()->after('id');
        });

        // Two Factor Authentication
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->text('two_factor_secret')->nullable()->after('two_factor_enabled');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'two_factor_enabled', 'two_factor_secret', 'two_factor_recovery_codes']);
        });
        
        Schema::dropIfExists('branches');
        Schema::dropIfExists('virtual_meetings');
        Schema::dropIfExists('course_enrollments');
        Schema::dropIfExists('course_lessons');
        Schema::dropIfExists('online_courses');
        Schema::dropIfExists('behaviour_records');
        Schema::dropIfExists('addons');
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('todo_lists');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('alumni_events');
        Schema::dropIfExists('alumni');
        Schema::dropIfExists('cms_news');
        Schema::dropIfExists('cms_gallery_images');
        Schema::dropIfExists('cms_events');
        Schema::dropIfExists('cms_menus');
        Schema::dropIfExists('cms_pages');
        Schema::dropIfExists('certificate_issuances');
        Schema::dropIfExists('certificate_templates');
        Schema::dropIfExists('hostel_room_assignments');
        Schema::dropIfExists('hostel_rooms');
        Schema::dropIfExists('hostels');
        Schema::dropIfExists('transport_route_stops');
        Schema::dropIfExists('transport_routes');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('item_categories');
        Schema::dropIfExists('book_issues');
        Schema::dropIfExists('books');
        Schema::dropIfExists('book_categories');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_assignments');
        Schema::dropIfExists('downloads');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('lesson_plans');
    }
};
