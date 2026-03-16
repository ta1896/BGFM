<?php

namespace App\Modules\LiveCenter\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\LiveOverviewService;
use Inertia\Inertia;
use Inertia\Response;

class LiveCenterController extends Controller
{
    public function index(LiveOverviewService $liveOverviewService): Response
    {
        $overview = $liveOverviewService->overview();

        return Inertia::render('Modules/LiveCenter/Online', [
            'onlineManagers' => $overview['onlineManagers'],
            'onlineWindowMinutes' => $overview['onlineWindowMinutes'],
        ]);
    }

    public function ticker(LiveOverviewService $liveOverviewService): Response
    {
        $overview = $liveOverviewService->overview();

        return Inertia::render('Modules/LiveCenter/Ticker', [
            'liveMatches' => $overview['liveMatches'],
            'onlineManagersCount' => $overview['onlineManagersCount'],
        ]);
    }
}
