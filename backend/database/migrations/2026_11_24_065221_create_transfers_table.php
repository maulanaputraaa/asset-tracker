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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('asset_id')->constrained();
            $table->foreignId('from_site_id')->constrained('sites');
            $table->foreignId('to_site_id')->constrained('sites');
            $table->foreignId('requested_by')->constrained('users');
            $table->enum('status', ['Draft', 'Pending', 'Approved', 'In Transit', 'Received', 'Rejected'])->default('Draft');
            $table->text('reason')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
