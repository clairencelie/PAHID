<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('single_support_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->foreignId('entity_group_id')->nullable()->constrained('entity_groups')->nullOnDelete();
            $table->enum('assignment_level', ['ENTITY', 'BRAND', 'SITE', 'GROUP'])->default('ENTITY');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('marketing_user_id')->constrained('users');
            $table->foreignId('approved_by')->constrained('users');
            $table->enum('approval_source', ['FIRST_VALID_REGISTRATION', 'VALID_LOA', 'SUPERVISOR_DECISION', 'MANUAL_OVERRIDE']);
            $table->text('approval_reason')->nullable();
            $table->unsignedBigInteger('loa_document_id')->nullable();
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'REVOKED', 'SUPERSEDED', 'DISPUTED'])->default('ACTIVE');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            $table->timestamps();
        });

        Schema::create('protected_prospect_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('single_support_assignment_id')->constrained('single_support_assignments')->cascadeOnDelete();
            $table->string('alias_name');
            $table->string('normalized_alias_name');
            $table->enum('alias_type', ['LEGAL_ENTITY', 'BRAND', 'GROUP', 'PROPERTY', 'ABBREVIATION', 'TYPO_VARIATION', 'FORMER_NAME', 'OTHER'])->default('OTHER');
            $table->enum('source', ['USER_INPUT', 'AI_DETECTED', 'ADMIN_CONFIRMED', 'LOA', 'DOCUMENT', 'MANUAL'])->default('MANUAL');
            $table->unsignedTinyInteger('confidence_score')->default(100);
            $table->timestamps();
        });

        Schema::create('single_support_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('new_prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->foreignId('existing_assignment_id')->constrained('single_support_assignments')->cascadeOnDelete();
            $table->enum('conflict_type', ['DUPLICATE_ENTITY', 'DUPLICATE_BRAND', 'GROUP_RELATED', 'ALIAS_MATCH', 'ADDRESS_MATCH', 'LOA_CONFLICT', 'MANUAL_REVIEW']);
            $table->unsignedTinyInteger('conflict_score')->default(0);
            $table->enum('risk_level', ['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH'])->default('MEDIUM');
            $table->string('detected_alias')->nullable();
            $table->string('matched_alias')->nullable();
            $table->json('ai_reasons_json')->nullable();
            $table->enum('status', ['OPEN', 'NEED_CLARIFICATION', 'APPROVED_AS_DIFFERENT', 'REJECTED_DUPLICATE', 'ESCALATED'])->default('OPEN');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('prospect_conflicts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->foreignId('matched_prospect_id')->nullable()->constrained('prospects')->nullOnDelete();
            $table->foreignId('matched_entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->enum('conflict_type', ['DUPLICATE_ENTITY', 'DUPLICATE_BRAND', 'GROUP_RELATED', 'ALIAS_MATCH', 'ADDRESS_MATCH', 'LOA_CONFLICT', 'MANUAL_REVIEW']);
            $table->unsignedTinyInteger('score')->default(0);
            $table->enum('risk_level', ['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH'])->default('LOW');
            $table->json('reasons_json')->nullable();
            $table->enum('status', ['OPEN', 'NEED_CLARIFICATION', 'APPROVED_AS_DIFFERENT', 'REJECTED_DUPLICATE', 'ESCALATED'])->default('OPEN');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_conflicts');
        Schema::dropIfExists('single_support_conflicts');
        Schema::dropIfExists('protected_prospect_aliases');
        Schema::dropIfExists('single_support_assignments');
    }
};
