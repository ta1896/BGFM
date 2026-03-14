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

        if (!$activeClub) {
            $activeClub = $request->user()->isAdmin()
                ? \App\Models\Club::query()->where('is_cpu', false)->orderBy('name')->first()
                : $request->user()->clubs()->where('is_cpu', false)->orderBy('name')->first();
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
            'activeClub' => $activeClub ? [
                'id' => $activeClub->id,
                'name' => $activeClub->name,
                'budget' => $activeClub->budget,
                'coins' => $activeClub->coins,
            ] : null,
            'transactions' => $transactions,
        ]);
    }
}
