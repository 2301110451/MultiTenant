<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('users', function (Blueprint $table) {
            $table->string('appearance_accent_color', 7)->nullable()->after('is_active');
            $table->string('appearance_background_color', 7)->nullable()->after('appearance_accent_color');
            $table->string('appearance_sidebar_background_color', 7)->nullable()->after('appearance_background_color');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('users', function (Blueprint $table) {
            $table->dropColumn([
                'appearance_accent_color',
                'appearance_background_color',
                'appearance_sidebar_background_color',
            ]);
        });
    }
};
