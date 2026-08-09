<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // студент
            $table->foreignId('column_id')->constrained()->cascadeOnDelete();
            $table->string('value', 50)->default(''); // балл или текст («зачёт»)
            $table->unique(['group_id', 'user_id', 'column_id']);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('grades'); }
};
