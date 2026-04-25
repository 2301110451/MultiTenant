<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('releases')) {
            return;
        }

        Schema::table('releases', function (Blueprint $table) {
            if (! Schema::hasColumn('releases', 'title')) {
                $table->string('title')->nullable()->after('id');
            }
            if (! Schema::hasColumn('releases', 'release_type')) {
                $table->string('release_type', 30)->nullable()->after('suggested_version');
            }
            if (! Schema::hasColumn('releases', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('source_commit_sha');
            }
            if (! Schema::hasColumn('releases', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            if (! Schema::hasColumn('releases', 'rejected_by')) {
                $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('releases', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            }
            if (! Schema::hasColumn('releases', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('rejected_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('releases')) {
            return;
        }

        Schema::table('releases', function (Blueprint $table) {
            $columns = ['title', 'release_type', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'published_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('releases', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
