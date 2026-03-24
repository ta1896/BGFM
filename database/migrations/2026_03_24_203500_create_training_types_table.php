<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 80);
            $table->string('slug', 80)->unique();
            $table->string('description', 255)->nullable();
            $table->string('category', 24)->default('technical');
            $table->string('team_focus', 32);
            $table->string('unit_focus', 32)->nullable();
            $table->string('default_intensity', 16)->default('medium');
            $table->string('tone', 24)->default('cyan');
            $table->string('icon', 32)->default('GraduationCap');
            $table->json('effects')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('training_types')->insert([
            [
                'name' => 'Pressing',
                'slug' => 'pressing',
                'description' => 'Intensives Pressing mit Fokus auf Laufarbeit und Umschalten.',
                'category' => 'fitness',
                'team_focus' => 'pressing',
                'unit_focus' => 'pressing',
                'default_intensity' => 'high',
                'tone' => 'amber',
                'icon' => 'Lightning',
                'effects' => json_encode([
                    ['attribute' => 'stamina_effect', 'delta' => -2],
                    ['attribute' => 'morale_effect', 'delta' => 1],
                    ['attribute' => 'form_effect', 'delta' => 1],
                    ['attribute' => 'physical', 'delta' => 1],
                    ['attribute' => 'attr_tactical', 'delta' => 1],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'sort_order' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Defensiv kompakt',
                'slug' => 'defending-shape',
                'description' => 'Arbeitet an Staffelung, Kompaktheit und Zweikampfverhalten.',
                'category' => 'tactics',
                'team_focus' => 'defending_shape',
                'unit_focus' => 'defending',
                'default_intensity' => 'medium',
                'tone' => 'emerald',
                'icon' => 'Target',
                'effects' => json_encode([
                    ['attribute' => 'stamina_effect', 'delta' => -1],
                    ['attribute' => 'morale_effect', 'delta' => 1],
                    ['attribute' => 'form_effect', 'delta' => 1],
                    ['attribute' => 'defending', 'delta' => 1],
                    ['attribute' => 'attr_defending', 'delta' => 1],
                    ['attribute' => 'attr_tactical', 'delta' => 1],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'sort_order' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Spielaufbau',
                'slug' => 'build-up',
                'description' => 'Sauberer Aufbau mit Fokus auf Technik und Passspiel.',
                'category' => 'technical',
                'team_focus' => 'build_up',
                'unit_focus' => 'build_up',
                'default_intensity' => 'medium',
                'tone' => 'cyan',
                'icon' => 'GraduationCap',
                'effects' => json_encode([
                    ['attribute' => 'stamina_effect', 'delta' => -1],
                    ['attribute' => 'form_effect', 'delta' => 1],
                    ['attribute' => 'passing', 'delta' => 1],
                    ['attribute' => 'technical', 'delta' => 1],
                    ['attribute' => 'attr_tactical', 'delta' => 1],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'sort_order' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Chance Creation',
                'slug' => 'chance-creation',
                'description' => 'Kreative Muster fuer den letzten Pass und Zwischenraeume.',
                'category' => 'technical',
                'team_focus' => 'chance_creation',
                'unit_focus' => 'creativity',
                'default_intensity' => 'medium',
                'tone' => 'violet',
                'icon' => 'GraduationCap',
                'effects' => json_encode([
                    ['attribute' => 'stamina_effect', 'delta' => -1],
                    ['attribute' => 'morale_effect', 'delta' => 1],
                    ['attribute' => 'form_effect', 'delta' => 1],
                    ['attribute' => 'passing', 'delta' => 1],
                    ['attribute' => 'technical', 'delta' => 1],
                    ['attribute' => 'attr_creativity', 'delta' => 1],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'sort_order' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Abschluss',
                'slug' => 'finishing',
                'description' => 'Torabschluss, Strafraumbesetzung und letzte Aktionen.',
                'category' => 'technical',
                'team_focus' => 'finishing',
                'unit_focus' => 'finishing_focus',
                'default_intensity' => 'high',
                'tone' => 'rose',
                'icon' => 'Target',
                'effects' => json_encode([
                    ['attribute' => 'stamina_effect', 'delta' => -1],
                    ['attribute' => 'morale_effect', 'delta' => 1],
                    ['attribute' => 'form_effect', 'delta' => 1],
                    ['attribute' => 'shooting', 'delta' => 1],
                    ['attribute' => 'attr_attacking', 'delta' => 1],
                    ['attribute' => 'pace', 'delta' => 1],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'sort_order' => 50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Standards',
                'slug' => 'set-pieces',
                'description' => 'Fokus auf ruhende Baelle und Ablauforganisation.',
                'category' => 'tactics',
                'team_focus' => 'set_pieces',
                'unit_focus' => 'passing',
                'default_intensity' => 'low',
                'tone' => 'fuchsia',
                'icon' => 'Rows',
                'effects' => json_encode([
                    ['attribute' => 'morale_effect', 'delta' => 1],
                    ['attribute' => 'form_effect', 'delta' => 1],
                    ['attribute' => 'passing', 'delta' => 1],
                    ['attribute' => 'technical', 'delta' => 1],
                    ['attribute' => 'attr_creativity', 'delta' => 1],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'sort_order' => 60,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Regeneration',
                'slug' => 'recovery',
                'description' => 'Belastung runterfahren und koerperliche Frische hochziehen.',
                'category' => 'recovery',
                'team_focus' => 'recovery',
                'unit_focus' => 'recovery',
                'default_intensity' => 'low',
                'tone' => 'emerald',
                'icon' => 'Heartbeat',
                'effects' => json_encode([
                    ['attribute' => 'stamina_effect', 'delta' => 2],
                    ['attribute' => 'morale_effect', 'delta' => 2],
                    ['attribute' => 'stamina', 'delta' => 1],
                ], JSON_THROW_ON_ERROR),
                'is_active' => true,
                'sort_order' => 70,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('training_types');
    }
};
