<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MatchTickerTemplate;
use Illuminate\Http\Request;

class TickerTemplateController extends Controller
{
    public function index(Request $request)
    {
        $query = MatchTickerTemplate::query();

        if ($request->filled('event_types')) {
            $types = (array) $request->event_types;
            $query->whereIn('event_type', $types);
        }

        $templates = $query->orderBy('event_type')
            ->orderBy('created_at', 'desc')
            ->paginate(50)
            ->withQueryString();

        $eventTypes = MatchTickerTemplate::EVENT_TYPES;

        return view('admin.ticker-templates.index', compact('templates', 'eventTypes'));
    }

    public function create()
    {
        $eventTypes = MatchTickerTemplate::EVENT_TYPES;
        return view('admin.ticker-templates.create', compact('eventTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type' => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::EVENT_TYPES)),
            'text' => 'required|string',
            'priority' => 'required|string|in:low,normal,high',
            'locale' => 'required|string|size:2',
        ]);

        MatchTickerTemplate::create($validated);

        return redirect()->route('admin.ticker-templates.index')->with('success', 'Vorlage erfolgreich erstellt.');
    }

    public function edit(MatchTickerTemplate $tickerTemplate)
    {
        $eventTypes = MatchTickerTemplate::EVENT_TYPES;
        return view('admin.ticker-templates.edit', compact('tickerTemplate', 'eventTypes'));
    }

    public function update(Request $request, MatchTickerTemplate $tickerTemplate)
    {
        $validated = $request->validate([
            'event_type' => 'required|string|in:' . implode(',', array_keys(MatchTickerTemplate::EVENT_TYPES)),
            'text' => 'required|string',
            'priority' => 'required|string|in:low,normal,high',
            'locale' => 'required|string|size:2',
        ]);

        $tickerTemplate->update($validated);

        return redirect()->route('admin.ticker-templates.index')->with('success', 'Vorlage erfolgreich aktualisiert.');
    }

    public function destroy(MatchTickerTemplate $tickerTemplate)
    {
        $tickerTemplate->delete();

        return redirect()->route('admin.ticker-templates.index')->with('success', 'Vorlage erfolgreich gelöscht.');
    }
}
