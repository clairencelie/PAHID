<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->string('legal_name');
            $table->string('normalized_name');
            $table->string('npwp')->nullable();
            $table->string('nib')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('occupation')->nullable();
            $table->string('website')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('entity_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->string('alias_name');
            $table->string('normalized_alias_name');
            $table->enum('alias_type', ['LEGAL_ENTITY', 'BRAND', 'GROUP', 'PROPERTY', 'ABBREVIATION', 'TYPO_VARIATION', 'FORMER_NAME', 'OTHER'])->default('OTHER');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('entity_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name');
            $table->string('normalized_group_name');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('entity_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_group_id')->constrained('entity_groups')->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->string('relationship_type')->default('SUBSIDIARY');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_group_members');
        Schema::dropIfExists('entity_groups');
        Schema::dropIfExists('entity_aliases');
        Schema::dropIfExists('entities');
    }
};
