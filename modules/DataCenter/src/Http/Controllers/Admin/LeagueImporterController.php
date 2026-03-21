<?php

namespace App\Modules\DataCenter\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Queue;
use App\Modules\DataCenter\Jobs\ImportLeagueJob;
use Modules\DataCenter\Models\ImportLog;


class LeagueImporterController extends Controller
{
    public function index()
    {
        $importedClubs = \App\Models\Club::where('is_imported', true)
            ->withCount('players')
            ->orderBy('updated_at', 'desc')
            ->get();

        $importLogs = ImportLog::orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return Inertia::render('Modules/DataCenter/Admin/LeagueImporter', [
            'status' => session('status'),
            'importedClubs' => $importedClubs,
            'importLogs' => $importLogs,
            'queueSize' => Queue::size(), 
        ]);

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'league_id' => 'required|string',
            'season' => 'required|string',
        ]);

        // Create initial log
        $log = ImportLog::create([
            'league_id' => $validated['league_id'],
            'season' => $validated['season'],
            'status' => 'pending',
            'message' => 'Warteschlange initiiert...',
            'details' => ['type' => 'bulk_league']
        ]);

        ImportLeagueJob::dispatch(
            $validated['league_id'],
            $validated['season'],
            $log->id
        );

        return back()->with('status', "Bulk-Import für {$validated['league_id']} ({$validated['season']}) wurde in die Warteschlange gestellt.");
    }

    public function clear()
    {
        ImportLog::truncate();
        return back()->with('status', 'Import-Journal wurde geleert.');
    }
}
