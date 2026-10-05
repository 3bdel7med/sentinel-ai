<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sentinel_logs', function (Blueprint $table) {
            $table->id();

            // One row per unique error (class + file + line). Repeats only bump `occurrences`.
            $table->string('hash', 40)->unique();

            $table->string('exception_class');
            $table->text('message');
            $table->string('file', 500);
            $table->unsignedInteger('line');
            $table->longText('trace')->nullable();
            $table->json('code_snippet')->nullable();

            // Request context
            $table->string('method', 10)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('environment', 50)->nullable();

            $table->unsignedInteger('occurrences')->default(1);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('resolved_at')->nullable();

            // AI results: pending | done | failed | skipped
            $table->string('ai_status', 20)->default('pending');
            $table->longText('ai_analysis')->nullable();
            $table->text('ai_error')->nullable();
            $table->longText('ai_test')->nullable();

            $table->timestamps();

            $table->index(['resolved_at', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentinel_logs');
    }
};
