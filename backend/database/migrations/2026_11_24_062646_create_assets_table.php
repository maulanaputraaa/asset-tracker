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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained('asset_categories');
            $table->foreignId('site_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained();

            $table->string('asset_code')->unique(); // Auto-generate QR target
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable();
            
            $table->enum('status', [
                'Registered', 'Available', 'In Use', 'In Transit', 
                'Under Maintenance', 'Damaged', 'Lost', 'Disposed'
            ])->default('Registered');
            
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->json('specifications')->nullable(); // Simpan atribut kustom dari kategori
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
