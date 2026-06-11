<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('leave_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('created_by')->constrained('users');
                $table->string('action');
                $table->text('description');
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_histories');
    }
};