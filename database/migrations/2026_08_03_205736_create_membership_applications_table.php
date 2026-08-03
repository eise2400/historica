<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('street', 200);
            $table->string('postal_code', 10);
            $table->string('city', 100);
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('message')->nullable();
            $table->boolean('handled')->default(false);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
    }
};
