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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->constrained();
            $table->foreignId('technician_id')->nullable()->constrained('users');
            
            $table->enum('type', ['Preventive', 'Corrective', 'Inspection']);
            $table->enum('status', ['Open', 'Assigned', 'In Progress', 'Waiting Parts', 'Completed', 'Closed']);
            $table->string('title');
            $table->text('description');
            
            // Biaya & Waktu
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->decimal('actual_cost', 15, 2)->default(0);
            $table->integer('estimated_hours')->default(0);
            
            $table->timestamp('due_date');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
