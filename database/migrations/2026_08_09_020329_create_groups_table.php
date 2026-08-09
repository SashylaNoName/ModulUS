<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');                              // ПИб-231
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // преподаватель
            $table->enum('level', ['Бакалавриат', 'Магистратура'])->default('Бакалавриат');
            $table->year('year');                                // год поступления
            $table->tinyInteger('number')->default(1);           // номер группы (1/2)
            $table->string('invite_token', 32)->unique();        // токен ссылки-приглашения
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('groups'); }
};
