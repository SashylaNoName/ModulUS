<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('groups', function (Blueprint $table) {
            $table->boolean('sum_m1')->default(true)->after('invite_token');
            $table->boolean('sum_m2')->default(true)->after('sum_m1');
            $table->boolean('sum_m3')->default(true)->after('sum_m2');
            $table->boolean('sum_total')->default(false)->after('sum_m3');
        });
    }
    public function down(): void {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['sum_m1','sum_m2','sum_m3','sum_total']);
        });
    }
};
