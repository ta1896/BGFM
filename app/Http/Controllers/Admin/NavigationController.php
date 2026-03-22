<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNavigationItemRequest;
use App\Http\Requests\UpdateNavigationItemRequest;
use App\Models\NavigationItem;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class NavigationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Navigation/Index', [
            'items' => NavigationItem::with('children')
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNavigationItemRequest $request): RedirectResponse
    {
        NavigationItem::create($request->validated());
        $this->clearNavigationCache();

        return redirect()->back()->with('success', 'Navigationspunkt erstellt.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNavigationItemRequest $request, $id): RedirectResponse
    {
        $navigationItem = NavigationItem::findOrFail($id);
        $navigationItem->update($request->validated());
        $this->clearNavigationCache();

        return redirect()->back()->with('success', 'Navigationspunkt aktualisiert.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $navigationItem = NavigationItem::findOrFail($id);
        $navigationItem->delete();
        $this->clearNavigationCache();

        return redirect()->back()->with('success', 'Navigationspunkt gelöscht.');
    }

    /**
     * Reorder navigation items.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:navigation_items,id',
            'items.*.parent_id' => 'nullable|exists:navigation_items,id',
            'items.*.sort_order' => 'required|integer',
            'items.*.group' => 'required|string',
        ]);

        foreach ($validated['items'] as $item) {
            NavigationItem::where('id', $item['id'])->update([
                'parent_id' => $item['parent_id'],
                'sort_order' => $item['sort_order'],
                'group' => $item['group'],
            ]);
        }

        $this->clearNavigationCache();

        return back()->with('success', 'Navigation neu angeordnet.');
    }

    private function clearNavigationCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('navigation_admin');
        \Illuminate\Support\Facades\Cache::forget('navigation_manager');
    }
}
