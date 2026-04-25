<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('update_announcements', function (Blueprint $table): void {
            if (! Schema::hasColumn('update_announcements', 'version')) {
                $table->string('version', 50)->nullable()->after('title');
            }
            if (! Schema::hasColumn('update_announcements', 'update_type')) {
                $table->string('update_type', 30)->nullable()->after('version');
            }
            if (! Schema::hasColumn('update_announcements', 'source')) {
                $table->string('source', 20)->default('manual')->after('update_type');
            }
            if (! Schema::hasColumn('update_announcements', 'github_release_id')) {
                $table->unsignedBigInteger('github_release_id')->nullable()->unique()->after('source');
            }
            if (! Schema::hasColumn('update_announcements', 'github_tag_name')) {
                $table->string('github_tag_name', 60)->nullable()->after('github_release_id');
            }
            if (! Schema::hasColumn('update_announcements', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('published_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('update_announcements', function (Blueprint $table): void {
            $dropColumns = [];

            foreach ([
                'version',
                'update_type',
                'source',
                'github_release_id',
                'github_tag_name',
                'synced_at',
            ] as $column) {
                if (Schema::hasColumn('update_announcements', $column)) {
                    $dropColumns[] = $column;
                }
            }

            if ($dropColumns !== []) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
