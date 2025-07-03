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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('business_name');
            $table->text('description');
            $table->string('business_photo')->nullable();
            $table->decimal('valuation', 15, 2);
            $table->decimal('money_needed', 15, 2);
            $table->decimal('percentage_offered', 5, 2); // e.g., 10.50 for 10.5%
            $table->string('location');
            $table->integer('employees_count')->default(0);
            $table->year('founded_year')->nullable();
            $table->text('business_model')->nullable();
            $table->text('target_market')->nullable();
            $table->text('competitive_advantages')->nullable();
            $table->json('financial_highlights')->nullable(); // revenue, profit, etc.
            $table->enum('status', ['active', 'pending', 'closed'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
