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
        Schema::table('assets', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
            $table->foreignId('vendor_id')->nullable()->after('category_id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('vendor_id')->constrained()->nullOnDelete();
            $table->string('photo')->nullable()->after('specifications');
            $table->json('documents')->nullable()->after('photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['vendor_id', 'department_id']);
            $table->dropColumn(['uuid', 'vendor_id', 'department_id', 'photo', 'documents']);
        });
    }
};
