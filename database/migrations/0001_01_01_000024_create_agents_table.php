<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_machine_id')->constrained('mac_machines')->cascadeOnDelete();
            $table->string('name');           // nom affiché (ex: "Assistant Ventes")
            $table->string('profile');        // slug --profile openclaw (ex: "sales")
            $table->timestamps();
        });

        // Remplacer mac_machine_id par agent_id sur project_members
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropForeign(['mac_machine_id']);
            $table->dropColumn('mac_machine_id');
            $table->foreignId('agent_id')->nullable()->after('role')
                  ->constrained('agents')->nullOnDelete();
        });

        // Supprimer profile de mac_machines (déplacé sur agents)
        Schema::table('mac_machines', function (Blueprint $table) {
            $table->dropColumn('profile');
        });
    }

    public function down(): void
    {
        Schema::table('mac_machines', function (Blueprint $table) {
            $table->string('profile')->nullable()->after('name');
        });

        Schema::table('project_members', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
            $table->foreignId('mac_machine_id')->nullable()->after('role')
                  ->constrained('mac_machines')->nullOnDelete();
        });

        Schema::dropIfExists('agents');
    }
};
