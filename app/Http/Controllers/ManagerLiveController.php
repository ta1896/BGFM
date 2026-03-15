<?php

namespace App\Http\Controllers;

use App\Services\LiveOverviewService;
use Inertia\Inertia;
use Inertia\Response;

class ManagerLiveController extends Controller
{
    public function index(LiveOverviewService $liveOverviewService): Response
    {
        $overview = $liveOverviewService->overview();

        return Inertia::render('Managers/Online', [
            'onlineManagers' => $overview['onlineManagers'],
            'onlineWindowMinutes' => $overview['onlineWindowMinutes'],
        ]);
    }

    public function ticker(LiveOverviewService $liveOverviewService): Response
    {
        $overview = $liveOverviewService->overview();

        return Inertia::render('Managers/Ticker', [
            'liveMatches' => $overview['liveMatches'],
            'onlineManagersCount' => $overview['onlineManagersCount'],
        ]);
    }
}
