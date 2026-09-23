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
            $table->text('error_message');
            $table->string('file_path');
            $table->integer('line_number');
            $table->longText('ai_analysis')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sentinel_logs');
    }
};
