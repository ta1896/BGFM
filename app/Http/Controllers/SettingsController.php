<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $userClubs = \App\Models\Club::where('is_cpu', false)->orderBy('name')->get();
        } else {
            $userClubs = $user->clubs()->orderBy('name')->get();
        }

        try {
            $passkeys = $user->webAuthnCredentials->map(function ($credential) {
                return [
                    'id' => $credential->id,
                    'alias' => $credential->alias,
                    'created_at_formatted' => $credential->created_at->format('d.m.Y H:i'),
                ];
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $passkeys = collect();
            session()->flash('error', 'Database migration missing for WebAuthn. Please run "php artisan migrate".');
        }

        return \Inertia\Inertia::render('Settings/Index', [
            'userClubs' => $userClubs,
            'passkeys' => $passkeys,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'default_club_id' => ['nullable', 'exists:clubs,id'],
        ]);

        $user = $request->user();

        // Ensure user actually owns the club if set
        if ($validated['default_club_id']) {
            if (!$user->isAdmin() && !$user->clubs()->where('id', $validated['default_club_id'])->exists()) {
                return back()->withErrors(['default_club_id' => 'Invalid club selection.']);
            }
        }

        $user->update([
            'default_club_id' => $validated['default_club_id'],
        ]);

        return back()->with('status', 'Einstellungen gespeichert.');
    }

    public function destroyPasskey(Request $request, $id): RedirectResponse
    {
        $request->user()->webAuthnCredentials()->where('id', $id)->delete();

        return back()->with('status', 'passkey-deleted');
    }
}
