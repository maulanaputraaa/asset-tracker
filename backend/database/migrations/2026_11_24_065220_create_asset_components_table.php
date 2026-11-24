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
        Schema::create('asset_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_asset_id')->constrained('assets')->onDelete('cascade');
            $table->foreignId('child_asset_id')->constrained('assets')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_components');
    }
};
