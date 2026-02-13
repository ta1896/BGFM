<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\CompetitionSeason;
use App\Models\GameMatch;
use App\Models\Lineup;
use App\Models\Player;
use App\Models\SponsorContract;
use App\Models\StadiumProject;
use App\Models\TrainingCamp;
use App\Models\User;
use App\Services\SimulationSettingsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(SimulationSettingsService $simulationSettings): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'admins' => User::where('is_admin', true)->count(),
                'clubs' => Club::count(),
                'cpu_clubs' => Club::where('is_cpu', true)->count(),
                'players' => Player::count(),
                'lineups' => Lineup::count(),
                'scheduled_matches' => GameMatch::where('status', 'scheduled')->count(),
                'active_sponsors' => SponsorContract::where('status', 'active')->count(),
                'active_stadium_projects' => StadiumProject::where('status', 'active')->count(),
                'active_training_camps' => TrainingCamp::whereIn('status', ['planned', 'active'])->count(),
            ],
            'latestUsers' => User::latest()->limit(8)->get(),
            'latestClubs' => Club::with('user')->latest()->limit(8)->get(),
            'activeCompetitionSeasons' => CompetitionSeason::query()
                ->with(['competition', 'season'])
                ->where('is_finished', false)
                ->whereHas('competition', fn ($q) => $q->where('type', 'league'))
                ->orderBy('id')
                ->get(),
            'simulationSettings' => $simulationSettings->adminSettings(),
        ]);
    }
}
