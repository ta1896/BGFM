<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchTickerTemplate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TickerTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchTickerTemplate::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('text', 'like', "%{$search}%");
        }

        if ($request->filled('event_types')) {
            $types = (array) $request->event_types;
            $query->whereIn('event_type', $types);
        }

        if ($request->filled('mood') && $request->mood !== '') {
            $query->where('mood', $request->mood);
        }

        if ($request->filled('commentator_style') && $request->commentator_style !== '') {
            $query->where('commentator_style', $request->commentator_style);
        }

        $templates = $query->orderBy('event_type')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('Admin/TickerTemplates/Index', [
            'templates'  => $templates,
            'eventTypes' => MatchTickerTemplate::EVENT_TYPES,
            'moods'      => MatchTickerTemplate::MOODS,
            'styles'     => MatchTickerTemplate::STYLES,
            'filters'    => $request->only(['search', 'event_types', 'mood', 'commentator_style']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/TickerTemplates/Form', [
            'eventTypes' => MatchTickerTemplate::EVENT_TYPES,
            'moods'      => MatchTickerTemplate::MOODS,
            'styles'     => MatchTickerTemplate::STYLES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type'        => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::EVENT_TYPES)),
            'text'              => 'required|string',
            'priority'          => 'required|string|in:low,normal,high',
            'mood'              => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::MOODS)),
            'commentator_style' => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::STYLES)),
            'locale'            => 'required|string|size:2',
        ]);

        $this->validatePlaceholders($validated['text']);

        MatchTickerTemplate::create($validated);

        return redirect()->route('admin.ticker-templates.index')->with('status', 'Vorlage erfolgreich erstellt.');
    }

    public function edit(MatchTickerTemplate $tickerTemplate)
    {
        return Inertia::render('Admin/TickerTemplates/Form', [
            'tickerTemplate' => $tickerTemplate,
            'eventTypes'     => MatchTickerTemplate::EVENT_TYPES,
            'moods'          => MatchTickerTemplate::MOODS,
            'styles'         => MatchTickerTemplate::STYLES,
        ]);
    }

    public function update(Request $request, MatchTickerTemplate $tickerTemplate)
    {
        $validated = $request->validate([
            'event_type'        => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::EVENT_TYPES)),
            'text'              => 'required|string',
            'priority'          => 'required|string|in:low,normal,high',
            'mood'              => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::MOODS)),
            'commentator_style' => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::STYLES)),
            'locale'            => 'required|string|size:2',
        ]);

        $this->validatePlaceholders($validated['text']);

        $tickerTemplate->update($validated);

        return redirect()->route('admin.ticker-templates.index')->with('status', 'Vorlage erfolgreich aktualisiert.');
    }

    private function validatePlaceholders(string $text): void
    {
        $allowed = ['player', 'opponent', 'club', 'score'];
        preg_match_all('/\{([^}]+)\}/', $text, $matches);

        foreach ($matches[1] as $placeholder) {
            if (!in_array($placeholder, $allowed)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'text' => ["Der Platzhalter {{$placeholder}} ist nicht erlaubt. Erlaubt sind: " . implode(', ', array_map(fn($p) => '{' . $p . '}', $allowed))],
                ]);
            }
        }
    }

    public function destroy(MatchTickerTemplate $tickerTemplate)
    {
        $tickerTemplate->delete();

        return redirect()->route('admin.ticker-templates.index')->with('status', 'Vorlage erfolgreich gelöscht.');
    }
}
