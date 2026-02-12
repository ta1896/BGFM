<?php

namespace App\Http\Controllers;

use App\Models\ClubFinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $clubId = (int) $request->query('club');
        $clubs = $request->user()->clubs()->orderBy('name')->get();
        $activeClub = $clubs->firstWhere('id', $clubId) ?? $clubs->first();

        $transactions = collect();
        if ($activeClub) {
            $transactions = ClubFinancialTransaction::query()
                ->where('club_id', $activeClub->id)
                ->latest('booked_at')
                ->paginate(25)
                ->withQueryString();
        }

        return view('finances.index', [
            'clubs' => $clubs,
            'activeClub' => $activeClub,
            'transactions' => $transactions,
        ]);
    }
}
