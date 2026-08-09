<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // получатель
            $table->string('type', 30);   // grade/comment/join/reply
            $table->string('icon', 16)->default('🔔');
            $table->string('title');
            $table->text('text')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read']);
        });
    }
    public function down(): void { Schema::dropIfExists('notifications'); }
};
