<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->enum('checklist_type', ['SPQ', 'POLICY_ISSUANCE']);
            $table->string('item_name');
            $table->boolean('is_critical')->default(false);
            $table->enum('status', ['COMPLETE', 'INCOMPLETE', 'INVALID', 'NEED_CLARIFICATION'])->default('INCOMPLETE');
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('workflow_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('role', ['admin', 'marketing', 'bc', 'underwriter', 'supervisor']);
            $table->string('task_type');
            $table->enum('status', ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'SKIPPED'])->default('PENDING');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sla_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->string('status');
            $table->timestamp('started_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_overdue')->default(false);
            $table->timestamps();
        });

        Schema::create('ai_verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->string('provider')->default('mock');
            $table->string('model')->nullable();
            $table->text('prompt')->nullable();
            $table->json('response_json')->nullable();
            $table->unsignedTinyInteger('confidence_score')->nullable();
            $table->enum('risk_level', ['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH'])->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values_json')->nullable();
            $table->json('new_values_json')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('ai_verification_logs');
        Schema::dropIfExists('sla_logs');
        Schema::dropIfExists('workflow_tasks');
        Schema::dropIfExists('document_checklists');
    }
};
