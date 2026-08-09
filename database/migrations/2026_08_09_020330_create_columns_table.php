<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['module','total','exam','retake','commission','score','grade','intermediate'])->default('intermediate');
            $table->string('position')->nullable();   // before1/before2/before3 (для intermediate)
            $table->unsignedTinyInteger('sum_into')->nullable(); // 1/2/3 — в какой модуль суммируется
            $table->boolean('hidden')->default(false); // скрыт от студентов
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('columns'); }
};
