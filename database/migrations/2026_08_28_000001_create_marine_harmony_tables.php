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
        Schema::create('deposits', function (Blueprint $table) {
            $table->string('id')->primary(); // custom ID like DEP-... or HIST-DEP-...
            $table->string('member');
            $table->date('date')->nullable();
            $table->text('period')->nullable(); // Month period like '2025-11' or comma-separated
            $table->string('method')->default('Bank'); // Bank, Mobile Banking, Cash, Historical Record
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('bank_ref')->nullable();
            $table->string('tx_type')->nullable();
            $table->string('mobile_wallet')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('mobile_ref')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('cash_location')->nullable();
            $table->string('special')->default('No');
            $table->text('remarks')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->string('status')->default('Pending'); // Approved, Pending, Rejected
            $table->string('submitted_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->boolean('historical')->default(false);
            $table->string('historical_type')->nullable();
            $table->timestamps();
        });

        Schema::create('incomes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date')->nullable();
            $table->string('source')->nullable(); // Bank Profit, Land, Rent, Business, Others
            $table->string('purpose')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('details')->nullable();
            $table->string('ref')->nullable();
            $table->string('status')->default('Pending'); // Approved, Pending, Rejected
            $table->string('submitted_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->boolean('historical')->default(false);
            $table->string('historical_type')->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date')->nullable();
            $table->string('category')->nullable(); // VAT, Source Tax, Bank Charge, Excise Duty, Others
            $table->string('description')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('details')->nullable();
            $table->string('ref')->nullable();
            $table->string('status')->default('Pending'); // Approved, Pending, Rejected
            $table->string('submitted_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->boolean('historical')->default(false);
            $table->string('historical_type')->nullable();
            $table->timestamps();
        });

        Schema::create('investments', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->date('date')->nullable();
            $table->string('institution')->nullable(); // Institution / Investment name
            $table->string('purpose')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->text('details')->nullable();
            $table->string('ref')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->integer('term_months')->nullable();
            $table->date('maturity_date')->nullable();
            $table->string('status')->default('Pending'); // Approved, Pending, Rejected
            $table->string('submitted_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->date('approval_date')->nullable();
            $table->boolean('historical')->default(false);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('deposits');
    }
};
