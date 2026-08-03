<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_person_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();

            $table->decimal('x_percent', 5, 2)->nullable();
            $table->decimal('y_percent', 5, 2)->nullable();
            $table->string('note', 200)->nullable();

            $table->string('status', 10)->default('approved');
            $table->foreignId('suggested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('created_at')->nullable();

            $table->unique(['photo_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_person_tags');
    }
};
