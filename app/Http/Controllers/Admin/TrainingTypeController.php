<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrainingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;

class TrainingTypeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/TrainingTypes/Index', [
            'trainingTypes' => TrainingType::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->map(fn (TrainingType $type) => [
                    'id' => $type->id,
                    'name' => $type->name,
                    'slug' => $type->slug,
                    'description' => $type->description,
                    'category' => $type->category,
                    'team_focus' => $type->team_focus,
                    'unit_focus' => $type->unit_focus,
                    'default_intensity' => $type->default_intensity,
                    'tone' => $type->tone,
                    'icon' => $type->icon,
                    'sort_order' => $type->sort_order,
                    'is_active' => $type->is_active,
                    'effects' => collect((array) $type->effects)
                        ->map(fn (array $effect) => [
                            'attribute' => (string) ($effect['attribute'] ?? ''),
                            'delta' => (int) ($effect['delta'] ?? 0),
                        ])
                        ->values()
                        ->all(),
                ])
                ->values()
                ->all(),
            'options' => [
                'categories' => TrainingType::CATEGORY_OPTIONS,
                'intensities' => TrainingType::INTENSITY_OPTIONS,
                'tones' => TrainingType::TONE_OPTIONS,
                'icons' => TrainingType::ICON_OPTIONS,
                'effects' => TrainingType::EFFECT_OPTIONS,
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePayload($request);

        TrainingType::create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['name']),
            'effects' => $this->normalizeEffects($validated['effects'] ?? []),
        ]);

        return back()->with('status', 'Trainingstyp wurde erstellt.');
    }

    public function update(Request $request, TrainingType $trainingType): RedirectResponse
    {
        $validated = $this->validatePayload($request, $trainingType);

        $trainingType->update([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['name'], $trainingType->id),
            'effects' => $this->normalizeEffects($validated['effects'] ?? []),
        ]);

        return back()->with('status', 'Trainingstyp wurde aktualisiert.');
    }

    public function destroy(TrainingType $trainingType): RedirectResponse
    {
        $trainingType->delete();

        return back()->with('status', 'Trainingstyp wurde geloescht.');
    }

    private function validatePayload(Request $request, ?TrainingType $trainingType = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:' . implode(',', array_keys(TrainingType::CATEGORY_OPTIONS))],
            'team_focus' => ['required', 'string', 'max:32'],
            'unit_focus' => ['nullable', 'string', 'max:32'],
            'default_intensity' => ['required', 'string', 'in:' . implode(',', array_keys(TrainingType::INTENSITY_OPTIONS))],
            'tone' => ['required', 'string', 'in:' . implode(',', array_keys(TrainingType::TONE_OPTIONS))],
            'icon' => ['required', 'string', 'in:' . implode(',', array_keys(TrainingType::ICON_OPTIONS))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['required', 'boolean'],
            'effects' => ['nullable', 'array'],
            'effects.*.attribute' => ['required', 'string', 'in:' . implode(',', array_keys(TrainingType::EFFECT_OPTIONS))],
            'effects.*.delta' => ['required', 'integer', 'between:-10,10'],
        ]);
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'training-type';
        $slug = $base;
        $counter = 2;

        while (
            TrainingType::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * @param array<int, array{attribute?:string,delta?:int|string}> $effects
     * @return array<int, array{attribute:string,delta:int}>
     */
    private function normalizeEffects(array $effects): array
    {
        return collect($effects)
            ->map(fn (array $effect) => [
                'attribute' => (string) ($effect['attribute'] ?? ''),
                'delta' => (int) ($effect['delta'] ?? 0),
            ])
            ->filter(fn (array $effect) => $effect['attribute'] !== '' && $effect['delta'] !== 0)
            ->values()
            ->all();
    }
}
