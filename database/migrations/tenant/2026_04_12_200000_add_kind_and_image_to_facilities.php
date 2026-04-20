<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('facilities', function (Blueprint $table) {
            $table->string('kind', 32)->default('facility')->after('name');
            $table->string('image_path', 500)->nullable()->after('kind');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('facilities', function (Blueprint $table) {
            $table->dropColumn(['kind', 'image_path']);
        });
    }
};
