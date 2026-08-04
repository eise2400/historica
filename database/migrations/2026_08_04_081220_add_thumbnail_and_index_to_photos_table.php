<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->string('thumbnail_path')->nullable()->after('image_path');
            $table->index(['is_published', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('photos', function (Blueprint $table) {
            $table->dropIndex(['is_published', 'created_at']);
            $table->dropColumn('thumbnail_path');
        });
    }
};
