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
        // Fee Groups/Categories
        Schema::create('fee_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Fee Types
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_group_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Fee Assignments (assign fees to classes)
        Schema::create('fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_session_id')->constrained()->onDelete('cascade');
            $table->date('due_date')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Student Fee Invoices
        Schema::create('student_fee_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_session_id')->constrained()->onDelete('cascade');
            $table->foreignId('class_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2);
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // Fee Invoice Items
        Schema::create('student_fee_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained('student_fee_invoices')->onDelete('cascade');
            $table->foreignId('fee_type_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Fee Payments
        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->constrained('student_fee_invoices')->onDelete('cascade');
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'online', 'cheque', 'bank_transfer'])->default('cash');
            $table->string('transaction_id')->nullable();
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->text('payment_remarks')->nullable();
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Income Heads
        Schema::create('income_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Income Records
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('income_head_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->nullable();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'online', 'cheque', 'bank_transfer'])->default('cash');
            $table->string('payer_name');
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('received_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Expense Heads
        Schema::create('expense_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Expense Records
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_head_id')->constrained()->onDelete('cascade');
            $table->string('bill_number')->nullable();
            $table->date('date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'card', 'online', 'cheque', 'bank_transfer'])->default('cash');
            $table->string('payee_name');
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('approved_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_heads');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('income_heads');
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('student_fee_invoice_items');
        Schema::dropIfExists('student_fee_invoices');
        Schema::dropIfExists('fee_assignments');
        Schema::dropIfExists('fee_types');
        Schema::dropIfExists('fee_groups');
    }
};
