<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loa_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained('prospects')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->text('extracted_text')->nullable();
            $table->string('issuer_name')->nullable();
            $table->string('issuer_position')->nullable();
            $table->string('entity_scope')->nullable();
            $table->string('validity_period')->nullable();
            $table->string('appointed_party')->nullable();
            $table->unsignedTinyInteger('loa_score')->nullable();
            $table->enum('loa_status', ['VALID', 'NEED_CLARIFICATION', 'SUSPICIOUS', 'REJECT_RECOMMENDED'])->nullable();
            $table->json('red_flags_json')->nullable();
            $table->json('ai_result_json')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // Back-fill the FK constraint now that loa_documents table exists
        Schema::table('single_support_assignments', function (Blueprint $table) {
            $table->foreign('loa_document_id')->references('id')->on('loa_documents')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('single_support_assignments', function (Blueprint $table) {
            $table->dropForeign(['loa_document_id']);
        });
        Schema::dropIfExists('loa_documents');
    }
};
