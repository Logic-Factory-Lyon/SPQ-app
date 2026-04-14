<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mac_machines', function (Blueprint $table) {
            $table->string('profile')->nullable()->after('name');
        });

        Schema::table('project_members', function (Blueprint $table) {
            $table->foreignId('mac_machine_id')->nullable()->after('role')
                  ->constrained('mac_machines')->nullOnDelete();
            $table->dropColumn('openclaw_profile');
        });
    }

    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropForeign(['mac_machine_id']);
            $table->dropColumn('mac_machine_id');
            $table->string('openclaw_profile')->nullable()->after('role');
        });

        Schema::table('mac_machines', function (Blueprint $table) {
            $table->dropColumn('profile');
        });
    }
};
