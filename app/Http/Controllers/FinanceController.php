<?php

namespace App\Http\Controllers;

use App\Models\ClubFinancialTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $activeClub = app()->has('activeClub') ? app('activeClub') : null;
        $clubs = $request->user()->isAdmin()
            ? \App\Models\Club::where('is_cpu', false)->orderBy('name')->get()
            : $request->user()->clubs()->orderBy('name')->get();

        if (!$activeClub && $clubs->isNotEmpty()) {
            $activeClub = $clubs->first();
        }

        $transactions = collect();
        if ($activeClub) {
            $transactions = ClubFinancialTransaction::query()
                ->where('club_id', $activeClub->id)
                ->latest('booked_at')
                ->paginate(25)
                ->withQueryString()
                ->through(function ($tx) {
                    return [
                        'id' => $tx->id,
                        'amount' => $tx->amount,
                        'balance_after' => $tx->balance_after,
                        'direction' => $tx->direction,
                        'context_type' => $tx->context_type,
                        'asset_type' => $tx->asset_type,
                        'note' => $tx->note,
                        'booked_at_formatted' => $tx->booked_at?->format('d.m.Y H:i'),
                    ];
                });
        }

        return \Inertia\Inertia::render('Finances/Index', [
            'activeClub' => $activeClub,
            'transactions' => $transactions,
        ]);
    }
}
