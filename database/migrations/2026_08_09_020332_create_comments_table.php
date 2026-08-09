<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grade_id')->constrained()->cascadeOnDelete(); // к какой оценке/ячейке
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();   // автор
            $table->text('text')->nullable();
            $table->string('image')->nullable(); // путь к фото в storage
            $table->string('file')->nullable();  // путь к файлу в storage
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('comments'); }
};
