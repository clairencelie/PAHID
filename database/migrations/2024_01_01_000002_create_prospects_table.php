<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('prospect_code')->unique();
            $table->string('prospect_name');
            $table->enum('input_type', ['LEGAL_ENTITY', 'BRAND', 'GROUP', 'PROPERTY', 'SUBSIDIARY', 'UNKNOWN'])->default('UNKNOWN');
            $table->string('legal_entity_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('group_name')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('occupation')->nullable();
            $table->decimal('estimated_premium', 15, 2)->nullable();
            $table->string('client_pic_name')->nullable();
            $table->string('client_pic_position')->nullable();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('marketing_user_id')->constrained('users');
            $table->enum('status', [
                'DRAFT', 'SUBMITTED', 'AI_VERIFICATION', 'NEED_CLARIFICATION',
                'DUPLICATE_REVIEW', 'LOA_REVIEW', 'BC_REVIEW', 'UW_REVIEW',
                'DOCUMENT_COMPLETION', 'APPROVED_FOR_FOLLOW_UP', 'READY_FOR_POLICY',
                'POLICY_ISSUED', 'REJECTED', 'CANCELLED'
            ])->default('DRAFT');
            $table->enum('risk_level', ['LOW', 'MEDIUM', 'HIGH', 'VERY_HIGH'])->nullable();
            $table->unsignedTinyInteger('duplicate_score')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
