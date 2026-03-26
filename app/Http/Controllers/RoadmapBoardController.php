<?php

namespace App\Http\Controllers;

use App\Models\RoadmapComment;
use App\Models\RoadmapItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapBoardController extends Controller
{
    public function index(Request $request): Response
    {
        $this->ensureSeedItems($request->user()?->id);

        $items = RoadmapItem::query()
            ->with([
                'creator:id,name',
                'updater:id,name',
                'comments.user:id,name',
            ])
            ->orderByRaw("FIELD(status, 'in_progress', 'planned', 'done', 'cancelled')")
            ->orderBy('sort_order')
            ->orderByDesc('priority')
            ->orderBy('effort')
            ->orderBy('title')
            ->get();

        $mappedItems = $items->map(fn (RoadmapItem $item) => $this->mapItem($item));

        return Inertia::render('Roadmap/Index', [
            'items' => $mappedItems->values()->all(),
            'groups' => $this->groupItems($mappedItems),
            'topItems' => $mappedItems
                ->filter(fn (array $item) => in_array($item['status'], ['planned', 'in_progress'], true))
                ->sortByDesc('weighted_score')
                ->take(5)
                ->values()
                ->all(),
            'statusOptions' => [
                ['value' => 'planned', 'label' => 'Offen'],
                ['value' => 'in_progress', 'label' => 'In Arbeit'],
                ['value' => 'done', 'label' => 'Fertiggestellt'],
                ['value' => 'cancelled', 'label' => 'Abgelehnt'],
            ],
            'categoryOptions' => [
                ['value' => 'quick', 'label' => 'Quick Win'],
                ['value' => 'mid', 'label' => 'Mittelfristig'],
                ['value' => 'big', 'label' => 'Grosses Feature'],
            ],
            'sizeOptions' => [
                ['value' => 'small', 'label' => 'Klein'],
                ['value' => 'huge', 'label' => 'Gross'],
            ],
        ]);
    }

    public function updateOrder(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:roadmap_items,id'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        foreach ($validated['items'] as $itemData) {
            RoadmapItem::query()->where('id', $itemData['id'])->update([
                'sort_order' => $itemData['sort_order'],
            ]);
        }

        return back()->with('status', 'Roadmap order updated.');
    }

    public function storeItem(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['required', 'string', 'max:1200'],
            'status' => ['required', 'string', 'in:planned,in_progress,done,cancelled'],
            'category' => ['required', 'string', 'in:quick,mid,big'],
            'size_bucket' => ['required', 'string', 'in:small,huge'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:40'],
            'priority' => ['required', 'integer', 'min:1', 'max:5'],
            'effort' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $title = trim((string) $validated['title']);

        RoadmapItem::query()->create([
            ...$validated,
            'title' => $title,
            'tags' => $this->normalizeTags($validated['tags'] ?? []),
            'key' => $this->uniqueKey($title),
            'created_by_user_id' => $request->user()->id,
            'updated_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Roadmap item created.');
    }

    public function updateItem(Request $request, RoadmapItem $roadmapItem): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:160'],
            'summary' => ['sometimes', 'required', 'string', 'max:1200'],
            'status' => ['sometimes', 'required', 'string', 'in:planned,in_progress,done,cancelled'],
            'category' => ['sometimes', 'required', 'string', 'in:quick,mid,big'],
            'size_bucket' => ['sometimes', 'required', 'string', 'in:small,huge'],
            'tags' => ['sometimes', 'nullable', 'array'],
            'tags.*' => ['string', 'max:40'],
            'priority' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
            'effort' => ['sometimes', 'required', 'integer', 'min:1', 'max:5'],
        ]);

        if (array_key_exists('title', $validated)) {
            $validated['title'] = trim((string) $validated['title']);
        }

        if (array_key_exists('tags', $validated)) {
            $validated['tags'] = $this->normalizeTags($validated['tags'] ?? []);
        }

        $roadmapItem->update([
            ...$validated,
            'updated_by_user_id' => $request->user()->id,
        ]);

        return back()->with('status', 'Roadmap item updated.');
    }

    public function storeComment(Request $request, RoadmapItem $roadmapItem): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        RoadmapComment::query()->create([
            'roadmap_item_id' => $roadmapItem->id,
            'user_id' => $request->user()->id,
            'body' => trim((string) $validated['body']),
        ]);

        $roadmapItem->forceFill([
            'updated_by_user_id' => $request->user()->id,
        ])->save();

        return back()->with('status', 'Comment added.');
    }

    private function mapItem(RoadmapItem $item): array
    {
        return [
            'id' => $item->id,
            'key' => $item->key,
            'title' => $item->title,
            'summary' => $item->summary,
            'status' => $item->status,
            'category' => $item->category,
            'size_bucket' => $item->size_bucket,
            'tags' => collect($item->tags ?? [])->values()->all(),
            'priority' => (int) $item->priority,
            'effort' => (int) $item->effort,
            'weighted_score' => $this->weightedScore($item),
            'creator' => $item->creator ? [
                'id' => $item->creator->id,
                'name' => $item->creator->name,
            ] : null,
            'updater' => $item->updater ? [
                'id' => $item->updater->id,
                'name' => $item->updater->name,
            ] : null,
            'comments' => $item->comments->map(fn (RoadmapComment $comment) => [
                'id' => $comment->id,
                'body' => $comment->body,
                'created_at' => optional($comment->created_at)->diffForHumans(),
                'user' => $comment->user ? [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                ] : null,
            ])->values()->all(),
            'comments_count' => $item->comments->count(),
            'updated_at' => optional($item->updated_at)->diffForHumans(),
        ];
    }

    private function groupItems(Collection $items): array
    {
        return collect(['planned', 'in_progress', 'done', 'cancelled'])
            ->mapWithKeys(fn (string $status) => [
                $status => $items->where('status', $status)->values()->all(),
            ])
            ->all();
    }

    private function weightedScore(RoadmapItem $item): float
    {
        $statusMultiplier = match ($item->status) {
            'in_progress' => 1.15,
            'planned' => 1.00,
            default => 0.00,
        };

        $bucketBonus = match ($item->category) {
            'quick' => 8,
            'mid' => 4,
            default => 0,
        };

        return round((((((int) $item->priority) * 20) - (((int) $item->effort) * 6) + $bucketBonus) * $statusMultiplier), 1);
    }

    private function ensureSeedItems(?int $userId): void
    {
        if (!$userId) {
            return;
        }

        collect($this->seedItems())->each(function (array $item) use ($userId): void {
            $existing = RoadmapItem::query()->where('key', $item['key'])->first();

            if ($existing) {
                $existing->update([
                    'title' => $item['title'],
                    'summary' => $item['summary'],
                    'category' => $item['category'],
                    'size_bucket' => $item['size_bucket'],
                    'tags' => $item['tags'] ?? [],
                    'priority' => $item['priority'],
                    'effort' => $item['effort'],
                ]);

                return;
            }

            RoadmapItem::query()->create([
                ...$item,
                'tags' => $item['tags'] ?? [],
                'created_by_user_id' => $userId,
                'updated_by_user_id' => $userId,
            ]);
        });
    }

    private function normalizeTags(array $tags): array
    {
        return collect($tags)
            ->map(fn ($tag) => trim((string) $tag))
            ->filter(fn (string $tag) => $tag !== '')
            ->map(fn (string $tag) => mb_substr($tag, 0, 40))
            ->unique(fn (string $tag) => mb_strtolower($tag))
            ->values()
            ->all();
    }

    private function seedItems(): array
    {
        return [
            // 8. TRAININGSEFFIZIENZ & SPEZIALISIERUNG (10)
            ['key' => 'tactical-drill-visualizer', 'title' => 'Taktik-Drill-Visualisierung', 'summary' => 'Neue Animationen im Trainings-Tab zeigen grafisch, welche Laufwege und Passmuster aktuell trainiert werden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 1],
            ['key' => 'mental-resilience-coaching', 'title' => 'Resilienz-Coaching', 'summary' => 'Spezielle Einheiten zur Staerkung der mentalen Widerstandsfaehigkeit nach Rueckstaenden oder in Derby-Situationen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 2],
            ['key' => 'dead-ball-specialists-path', 'title' => 'Standard-Spezialisten-Akademie', 'summary' => 'Gezieltes Training fuer Freistoss- und Eckballschuetzen zur Erhoehung der Praezision und Effektivität.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 3],
            ['key' => 'recovery-yoga-sessions', 'title' => 'Regenerations-Yoga', 'summary' => 'Sanfte Einheiten zur Reduktion der Muskelspannung nach englischen Wochen (senkt das Verletzungsrisiko).', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 1, 'sort_order' => 4],
            ['key' => 'video-analysis-sessions', 'title' => 'Video-Analyse-Workshops', 'summary' => 'Steigert die taktische Intelligenz der Spieler durch Theorie-Sitzungen abseits des gruenen Rasens.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 5],
            ['key' => 'position-swap-training', 'title' => 'Positions-Flexibilitaet', 'summary' => 'Spieler lernen, auf Nebenpositionen auszuhelfen, ohne massive Abzuege in der Spielstaerke zu erhalten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 4, 'sort_order' => 6],
            ['key' => 'captain-leadership-seminars', 'title' => 'Leadership-Mentoring', 'summary' => 'Kapitaen und Vize-Kapitaene koennen ihre Fuehrungsqualitaeten in Gespraechen mit dem Staff verbessern.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 7],
            ['key' => 'match-engine-simulation-drills', 'title' => 'Schattenkabinet-Training', 'summary' => 'Simuliere Match-Szenarien (z.B. 0:1 Rueckstand 80. Min) im Training, um Automatismen zu festigen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 4, 'sort_order' => 8],
            ['key' => 'high-altitude-camp', 'title' => 'Hoehentrainingslager', 'summary' => 'Besonderes Trainingslager-Modul zur massiven Erhoehung der Ausdauerwerte (hohe Kosten).', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 4, 'sort_order' => 9],
            ['key' => 'biomechanics-analysis', 'title' => 'Biomechanik-Checkup', 'summary' => 'Optimierung des Laufstils einzelner Spieler zur Steigerung der Endgeschwindigkeit und Gelenkschonung.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 10],

            // 9. STAB & PERSONALWESEN (8)
            ['key' => 'staff-synergy-system', 'title' => 'Stab-Synergien', 'summary' => 'Trainer, Physios und Scouts arbeiten besser zusammen, wenn sie das gleiche Spielkonzept bevorzugen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 11],
            ['key' => 'data-analyst-hiring', 'title' => 'Data-Analyst-Abteilung', 'summary' => 'Heuere Analysten an, um detailliertere Statistiken ueber deine Spieler und die Konkurrenz freizuschalten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 2, 'sort_order' => 12],
            ['key' => 'staff-retirement-succession', 'title' => 'Nachfolgeplanung fuer Staff', 'summary' => 'Erfahrene Staff-Mitglieder koennen Mentoren fuer juengere Kollegen werden, bevor sie in Rente gehen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 13],
            ['key' => 'nutritionist-impact', 'title' => 'Ernaehrungsberater-Stelle', 'summary' => 'Verbessert die langfristige Fitness und verkuerzt die Erholungszeiten nach hoher Belastung.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1, 'sort_order' => 14],
            ['key' => 'staff-loyalty-contracts', 'title' => 'Staff-Treue-Boni', 'summary' => 'Verhindert, dass Top-Assistenten mitten in der Saison von der Konkurrenz abgeworben werden.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1, 'sort_order' => 15],
            ['key' => 'psychology-department', 'title' => 'Sportpsychologie-Zentrum', 'summary' => 'Hilft Spielern, Heimweh zu ueberwinden oder nach schweren Verletzungen mental zurueckzukehren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 16],
            ['key' => 'performance-bonus-for-staff', 'title' => 'Erfolgsprämien fuer den Stab', 'summary' => 'Steigerung der Arbeitsmotivation und Effektivität deiner Mitarbeiter durch finanzielle Anreize.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 17],
            ['key' => 'headhunter-scouting-staff', 'title' => 'Stab-Headhunter-Auftraege', 'summary' => 'Beauftrage Agenturen, um die besten Trainer der Welt fuer deinen Club zu finden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 18],

            // 10. MEDIZIN & GESUNDHEIT (7)
            ['key' => 'advanced-injury-prevention', 'title' => 'KI-Verletzungs-Fruehwarnung', 'summary' => 'Algorithmus berechnet kritische Ermuedungspunkte und warnt vor imminenten Muskelfaserrissen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 19],
            ['key' => 'medical-equipment-v3', 'title' => 'Kryo-Therapie-Kammern', 'summary' => 'Hochmoderne Ausruestung fuer schnellere Regeneration nach Spielen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 20],
            ['key' => 'rehab-specialists-rework', 'title' => 'Individuelle Reha-Plaene', 'summary' => 'Optimale Wiedereingliederung verletzter Stars zur Vermeidung von Folgeverletzungen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 21],
            ['key' => 'medical-tourism-option', 'title' => 'Spezialklinik-Behandlungen', 'summary' => 'Schicke deine Stars zu Experten weltweit, um Ausfallzeiten bei schweren Verletzungen zu minimieren (teuer).', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 22],
            ['key' => 'fitness-test-standardization', 'title' => 'Standardisierte Belastungstests', 'summary' => 'Regelmaessige Leistungstests geben Aufschluss ueber den aktuellen Fitnesszustand des gesamten Kaders.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 23],
            ['key' => 'pain-killer-ethics-system', 'title' => 'Schmerzmittel-Management', 'summary' => 'Risikoreiche Entscheidung: Spieler fit spritzen fuer wichtige Spiele vs. langfristige Gesundheitsschäden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 4, 'sort_order' => 24],
            ['key' => 'sleep-scientist-hiring', 'title' => 'Schlaf-Optimierungs-Coaching', 'summary' => 'Verbesserung der circadianen Rhythmen zur Steigerung der Konzentration waehrend der Spiele.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 1, 'sort_order' => 25],

            // 11. TAKTIK-VERTIEFUNG & VARIANTEN (7)
            ['key' => 'inverted-fullback-logic', 'title' => 'Inverted Fullback-Rollen', 'summary' => 'Aussenverteidiger ruecken bei eigenem Ballbesitz ins Zentrum ein, um das Mittelfeld zu ueberladen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 4, 'sort_order' => 26],
            ['key' => 'asymmetric-formations', 'title' => 'Asymmetrische Formationen', 'summary' => 'Erlaube das Verschieben einzelner Spielerpositionen abseits der Standard-Rastersysteme.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5, 'sort_order' => 27],
            ['key' => 'pressing-trigger-settings', 'title' => 'Pressing-Auslöser', 'summary' => 'Definiere genau, wann das Team presst (z.B. bei Rueckpass, Ballannahmefehler oder am Fluegel).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 28],
            ['key' => 'counter-pressing-intensity', 'title' => 'Gegenpressing-Stufen', 'summary' => 'Waehle zwischen sofortiger Ballrueckeroberung oder schnellem Rueckzug in die Grundformation.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 29],
            ['key' => 'target-man-pivot-play', 'title' => 'Wandspieler-Ablagen', 'summary' => 'Spezielle taktische Option: Hohe Baelle auf den Stoermer, der sie fuer nachrueckende Mittelfeldspieler ablegt.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 30],
            ['key' => 'zonal-marking-v2', 'title' => 'Raumdeckung-Finessen', 'summary' => 'Unterscheidung zwischen ballorientierter und gegnerorientierter Raumdeckung.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 31],
            ['key' => 'overload-underload-tactics', 'title' => 'Ueberzahl-Szenarien', 'summary' => 'Befehle deinem Team, gezielt eine Fluegel-Seite zu ueberladen, um Raeume auf der anderen Seite zu oeffnen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 32],

            // 12. SOCIAL MEDIA & FANKULTUR (6) 
            ['key' => 'fan-chant-customizer', 'title' => 'Fangesang-Editor', 'summary' => 'Erstelle oder waehle eigene Gesaenge fuer deinen Verein, die bei Toren oder Siegen abgespielt werden.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 33],
            ['key' => 'influencer-marketing-v2', 'title' => 'Influencer-Kooperationen', 'summary' => 'Nutze die Reichweite von Internet-Stars, um mehr junge Fans ins Stadion zu locken.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 34],
            ['key' => 'fan-club-foundation', 'title' => 'Fanclub-Foerderung', 'summary' => 'Unterstuetze die Gruendung offizieller Fanclubs in anderen Staedten zur Steigerung des Prestiges.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 35],
            ['key' => 'ultra-choreography-budget', 'title' => 'Choreographie-Budget', 'summary' => 'Finanziere beeindruckende Kurven-Shows fuer Top-Spiele (steigert die Heimstaerke massiv).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 36],
            ['key' => 'open-training-day', 'title' => 'Tag des offenen Trainings', 'summary' => 'Ein Event fuer die Fans, das die Bindung staerkt, aber die Spielvorbereitung leicht stoeren kann.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1, 'sort_order' => 37],
            ['key' => 'autograph-session-manager', 'title' => 'Autogrammstunden-Planung', 'summary' => 'Manager entscheidet, welche Spieler wann fuer Fans zur Verfuegung stehen (Moral vs. Marketing).', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1, 'sort_order' => 38],

            // 13. SCOUTING & DATEN-REVOLUTION (6)
            ['key' => 'hidden-potential-indicators', 'title' => 'Potenzial-Indikatoren', 'summary' => 'Scouts finden Hinweise auf versteckte Entwicklungsschuetze (z.B. "Spaetzuender"-Tag).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 39],
            ['key' => 'historical-player-database', 'title' => 'Historisches Archiv', 'summary' => 'Vergleiche aktuelle Talente mit Legenden deines Vereins basierend auf deren Leistungsdaten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 3, 'sort_order' => 40],
            ['key' => 'scouting-network-expansion', 'title' => 'Globales Scouting-Netzwerk', 'summary' => 'Eroeffne Scouting-Bueros in Suedamerika oder Afrika, um die Kosten fuer Reisen zu senken.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4, 'sort_order' => 41],
            ['key' => 'under-the-radar-talents', 'title' => 'Geheimtipps-Algorithmus', 'summary' => 'Finde Spieler in unterklassigen Ligen, die statistisch ueberperformen, aber wenig Marktwert haben.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 42],
            ['key' => 'agent-loyalty-trust', 'title' => 'Berater-Vertrauensverhaeltnis', 'summary' => 'Gute Beziehungen zu bestimmten Beratern koennen exklusive Vorab-Infos ueber wechselwillige Spieler bringen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 43],
            ['key' => 'youth-scouting-v2', 'title' => 'Sichtungsturnier-Modul', 'summary' => 'Veranstalte eigene Turniere, um die besten regionalen Talente direkt vor Ort zu testen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 44],

            // 14. FINANZEN & RECHT (6)
            ['key' => 'naming-rights-extension', 'title' => 'Tribuenen-Namensrechte', 'summary' => 'Verkaufe die Namen einzelner Kurven oder Tribuenen an Sponsoren fuer zusaetzliches Budget.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 45],
            ['key' => 'merchandising-onlineshop-v3', 'title' => 'Globaler Onlineshop', 'summary' => 'Upgrade der digitalen Verkaufskanaele zur Erhoehung der weltweiten Absatzzahlen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 46],
            ['key' => 'legal-department-expansion', 'title' => 'Rechtsabteilung-Ausbau', 'summary' => 'Minimiere das Risiko von Formfehlern bei Vertraegen und verhandele bessere Konditionen bei Abgaengen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 47],
            ['key' => 'insurance-for-top-stars', 'title' => 'Versicherungen fuer Top-Stars', 'summary' => 'Sichere den Verein finanziell gegen langwierige Ausfaelle deiner teuersten Spieler ab.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 48],
            ['key' => 'tax-haven-scandals', 'title' => 'Finanzielle Compliance', 'summary' => 'Achte auf saubere Buchfuehrung, um Strafzahlungen und Imageverlust durch Audits zu vermeiden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 49],
            ['key' => 'crowdfunding-new-stadium', 'title' => 'Fan-Investitionsmodelle', 'summary' => 'Lasse die Fans durch Anleihen direkt an Bauprojekten des Vereins partizipieren.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 4, 'sort_order' => 50],

            // 15. INFRASTRUKTUR & STADION-ERLEBNIS (10)
            ['key' => 'smart-stadium-app-integration', 'title' => 'Smart-Stadium Vernetzung', 'summary' => 'Bestellung von Getränken und Speisen direkt am Platz per App zur Erhoehung der Catering-Einnahmen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 51],
            ['key' => 'vip-parking-premium', 'title' => 'Premium-Parkplatz-Service', 'summary' => 'Zusaetzliche Einnahmequelle durch exklusive Parkbereiche direkt am Stadion-Eingang.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 52],
            ['key' => 'stadium-tour-virtual-reality', 'title' => 'VR-Stadionfuehrungen', 'summary' => 'Virtuelle Rundgaenge durch das Stadion fuer globale Fans zur Steigerung der Markenbindung.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 3, 'sort_order' => 53],
            ['key' => 'sustainable-energy-stadium', 'title' => 'Gruenes Stadion-Zertifikat', 'summary' => 'Investition in nachhaltige Techniken senkt langfristig die Betriebskosten und lockt "Eco-Sponsoren" an.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 54],
            ['key' => 'pitch-lighting-v2', 'title' => 'LED-Flutlicht-Upgrade', 'summary' => 'Bessere Lichtverhaeltnisse fuer TV-Uebertragungen und geringerer Stromverbrauch.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 55],
            ['key' => 'training-ground-security', 'title' => 'Abschirmung des Trainingsgelaendes', 'summary' => 'Investiere in Sichtschutz und Security, um Spionage durch gegnerische Scouts zu verhindern.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 56],
            ['key' => 'club-house-renovation', 'title' => 'Vereinsheim-Modernisierung', 'summary' => 'Steigert die Identifikation der Mitarbeiter und die Attraktivitaet fuer potenzielle Neuzugaenge.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 57],
            ['key' => 'giant-screen-interactive', 'title' => 'Interaktive Anzeigetafel', 'summary' => 'Fans koennen per App ueber den "Man of the Match" abstimmen, was live im Stadion gezeigt wird.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 58],
            ['key' => 'stadium-acoustics-optimization', 'title' => 'Akustik-Optimierung', 'summary' => 'Bauliche Massnahmen zur Verstaerkung der Fangesaenge (erhoeht den Home-Advantage-Effekt).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 59],
            ['key' => 'hybrid-event-venue', 'title' => 'Multifunktions-Arena-Modus', 'summary' => 'Nutze das Stadion fuer Konzerte und Messen an spielfreien Tagen zur Umsatzmaximierung.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5, 'sort_order' => 60],

            // 16. PR, MEDIEN & KOMMUNIKATION (10)
            ['key' => 'crisis-comms-playbook', 'title' => 'Krisenkommunikations-Strategie', 'summary' => 'Vorbereitete Statements fuer den Ernstfall minimieren den Moralverlust bei Skandalen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 61],
            ['key' => 'player-social-media-policy', 'title' => 'Social-Media-Richtlinien', 'summary' => 'Schule deine Spieler im Umgang mit Instagram & Co, um unnötige Schlagzeilen zu vermeiden.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 1, 'sort_order' => 62],
            ['key' => 'exclusive-documentary-deal', 'title' => 'Exklusiver Doku-Deal', 'summary' => 'Verkaufe die Rechte an einer "All or Nothing"-Serie an einen Streaming-Anbieter.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4, 'sort_order' => 63],
            ['key' => 'local-radio-partnership', 'title' => 'Lokalradio-Partnerschaft', 'summary' => 'Erhoehe die Praesenz in der Region und locke mehr Gelegenheitszuschauer an.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1, 'sort_order' => 64],
            ['key' => 'international-pr-agency', 'title' => 'Internationale PR-Agentur', 'summary' => 'Verbessere das Ansehen deines Vereins in Schluesselmaerkten wie Asien oder den USA.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 65],
            ['key' => 'fan-forum-moderation', 'title' => 'Offizielles Fan-Forum', 'summary' => 'Direkter Draht zur Basis erlaubt es, Trends und Unmut fruehzeitig zu erkennen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 66],
            ['key' => 'press-center-upgrade', 'title' => 'Modernes Pressezentrum', 'summary' => 'Locke mehr nationale Journalisten an und verbessere die Qualitaet der Berichterstattung.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 67],
            ['key' => 'branded-content-studio', 'title' => 'Hauseigenes Content-Studio', 'summary' => 'Produziere hochwertige Videos fuer Sponsoren und Fans direkt im Verein zu geringen Kosten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 68],
            ['key' => 'charity-ambassador-program', 'title' => 'Charity-Botschafter-Programm', 'summary' => 'Ehemalige Spieler engagieren sich sozial im Namen des Vereins (massiver Image-Boost).', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 69],
            ['key' => 'interactive-pre-match-show', 'title' => 'Interaktive Pre-Match Show', 'summary' => 'Erhoehe die Verweildauer der Fans im Stadion durch Unterhaltungsprogramm vor dem Spiel.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 70],

            // 17. MERCHANDISING & GLOBAL BRANDING (5)
            ['key' => 'lifestyle-merch-line', 'title' => 'Lifestyle-Modekollektion', 'summary' => 'Entwickle Fan-Kleidung, die auch im Alltag tragbar ist (abseits des klassischen Trikots).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 71],
            ['key' => 'limited-edition-kits', 'title' => 'Sondertrikots (Limited Edition)', 'summary' => 'Einmalige Trikot-Designs fuer besondere Anlaesse generieren kurzfristig hohe Umsaetze.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 2, 'sort_order' => 72],
            ['key' => 'global-e-commerce-v2', 'title' => 'KI-Merchandise-Empfehlungen', 'summary' => 'Personalisiertes Shopping-Erlebnis im Fanshop steigert die Conversion-Rate.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 73],
            ['key' => 'museum-interactive-tech', 'title' => 'Interaktive Museums-Exponate', 'summary' => 'Nutze Hologramme und Touchscreens zur Praesentation der Vereinsgeschichte.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 3, 'sort_order' => 74],
            ['key' => 'cobranding-partnerships', 'title' => 'Co-Branding Kooperationen', 'summary' => 'Arbeite mit Modemarken zusammen, um exklusive, hochpreisige Produkte zu kreieren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 75],

            // 18. KADERPLANUNG & VERTRAGSWESEN (10)
            ['key' => 'release-clause-v2', 'title' => 'Erfolgsbasierte Ausstiegsklauseln', 'summary' => 'Klauseln, die nur bei Verpassen des Europapokals oder bei Abstieg aktiviert werden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 2, 'sort_order' => 76],
            ['key' => 'player-retirement-roadmap', 'title' => 'Karriereende-Roadmap', 'summary' => 'Spieler geben fruehzeitig bekannt, wann sie aufhören wollen, was die langfristige Planung erleichtert.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 77],
            ['key' => 'squad-personality-balance', 'title' => 'Kabinen-Hierarchie-Analyse', 'summary' => 'Visualisierung der Gruppendynamik: Wer sind die Anfuehrer, wer die Mitlaeufer, wer die Unruhestifter?', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 78],
            ['key' => 'contract-loyalty-bonus', 'title' => 'Loyalitäts-Staffelung', 'summary' => 'Gehaltsboni, die mit jedem Jahr Vereinszugehoerigkeit automatisch leicht ansteigen.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 1, 'sort_order' => 79],
            ['key' => 'appearance-fee-impact', 'title' => 'Einsatzpraemien-Ökonomie', 'summary' => 'Verhandle Praemien pro Spielminute zur Motivation von Reservisten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 80],
            ['key' => 'squad-rotation-satisfaction', 'title' => 'Rotations-Zufriedenheit', 'summary' => 'Detailliertes Feedback der Spieler, wie sie ihre aktuelle Einsatzzeit im Vergleich zu ihrem Status sehen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 81],
            ['key' => 'player-mentoring-v2', 'title' => 'Gezieltes Mentoring-Duo', 'summary' => 'Bestimme aktiv, welcher Routinier welches Talent unter seine Fittiche nehmen soll.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 82],
            ['key' => 'squad-dna-definition', 'title' => 'Kader-DNA-Profil', 'summary' => 'Definiere Attribute, die alle Spieler deines Vereins haben muessen (z.B. Aggressivitaet), was Scouts priorisieren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 83],
            ['key' => 'emergency-loan-gate', 'title' => 'Notfall-Leih-Fenster', 'summary' => 'Spezielle Regelung bei extremem Verletzungspech, um kurzfristig Ersatz verpflichten zu koennen.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 84],
            ['key' => 'player-exchange-v3', 'title' => 'Mehr-Spieler-Tauschdeals', 'summary' => 'Verhandle komplexe Transfers, bei denen 2-3 Spieler gleichzeitig den Verein wechseln.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 85],

            // 19. UI/UX & DASHBOARD-OPTIMIERUNG (10)
            ['key' => 'customizable-home-widgets', 'title' => 'Personalisierbare Home-Widgets', 'summary' => 'Stelle dir dein Start-Dashboard mit den fuer dich wichtigsten Infos selbst zusammen.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 4, 'sort_order' => 86],
            ['key' => 'dark-mode-high-contrast', 'title' => 'Hochkontrast-Modus', 'summary' => 'Zusaetzliche Barrierefreiheits-Optionen fuer die gesamte Web-Oberflaeche.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 87],
            ['key' => 'batch-player-comparison', 'title' => 'Multi-Spieler-Vergleich', 'summary' => 'Vergleiche bis zu 4 Spieler gleichzeitig in einer uebersichtlichen Radar-Chart-Ansicht.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 88],
            ['key' => 'quick-action-sidebar', 'title' => 'Quick-Action Seitenleiste', 'summary' => 'Schnellzugriff auf Training, Aufstellung und Transfermarkt von jeder Unterseite aus.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 89],
            ['key' => 'bulk-scouting-reports', 'title' => 'Sammelberichte der Scouts', 'summary' => 'Uebersichtliche Zusammenfassung aller wöchentlichen Scouting-Ergebnisse in einer Email.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 90],
            ['key' => 'interactive-manager-timeline', 'title' => 'Manager-Karriere-Zeitstrahl', 'summary' => 'Visuelle Aufarbeitung deiner bisherigen Erfolge, Stationen und groessten Transfers.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 91],
            ['key' => 'drag-and-drop-training', 'title' => 'D&D Trainingsplanung', 'summary' => 'Plane die Trainingswoche intuitiv per Drag & Drop der verschiedenen Module.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 92],
            ['key' => 'notification-center-v2', 'title' => 'Intelligentes Benachrichtigungszentrum', 'summary' => 'Kategorisierung und Priorisierung von Club-News zur besseren Uebersicht.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 93],
            ['key' => 'keyboard-shortcuts-poweruser', 'title' => 'Poweruser Tastaturkuerzel', 'summary' => 'Navigiere blitzschnell durch die Menues mittels Shortcuts (z.B. "T" fuer Training).', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 1, 'sort_order' => 94],
            ['key' => 'mobile-responsive-pitch-v2', 'title' => 'Mobile Aufstellung 2.0', 'summary' => 'Optimierte Touch-Bedienung fuer das Verschieben von Spielern auf dem Spielfeld.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 95],

            // 20. SONSTIGES & IMMERSION (5)
            ['key' => 'stadium-announcer-text', 'title' => 'Stadionsprecher-Texte', 'summary' => 'Anpassbare Texte fuer Durchsagen bei Toren oder Wechseln zur Steigerung der Atmosphaere.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 1, 'sort_order' => 96],
            ['key' => 'weather-forecast-impact-v2', 'title' => 'Detaillierter Wetterbericht', 'summary' => 'Wochenvorhersage erlaubt gezielte Vorbereitung auf Regen- oder Hitzeschlachten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 97],
            ['key' => 'club-history-book', 'title' => 'Digitales Vereins-Jahrbuch', 'summary' => 'Automatisch generierte Zusammenfassung jeder Saison mit allen Statistiken und Highlights.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 98],
            ['key' => 'rivalry-hype-system', 'title' => 'Derby-Hype-Mechanik', 'summary' => 'Besondere Events und Medienberichte vor Duellen gegen Erzrivalen steigern den Druck und die Vorfreude.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 99],
            ['key' => 'legend-match-events', 'title' => 'Legenden-Benefizspiele', 'summary' => 'Organisiere Spiele mit Ex-Stars zur Steigerung des Fan-Gefuehls und fuer den guten Zweck.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 100],

            // 1. MATCH ENGINE & SIMULATION (15)
            ['key' => 'ball-physics-v2', 'title' => 'Ballphysik-Revolution', 'summary' => 'Umfassendes Update der Ballberechnungen: Schnitt, Topspin und Luftwiderstand wirken sich nun direkt auf Flugkurven und Abprallverhalten aus.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 5, 'sort_order' => 101],
            ['key' => 'pitch-conditions-impact', 'title' => 'Platzbeschaffenheit-Effekte', 'summary' => 'Rasenlaenge, Feuchtigkeit und Abnutzung beeinflussen das Rollverhalten des Balls und das Verletzungsrisiko der Spieler unmittelbar.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 102],
            ['key' => 'advanced-goalkeeper-ai', 'title' => 'Intelligente Torwart-Positionierung', 'summary' => 'Torhueter bewerten nun Distanzschuesse, Winkel und die Position der Verteidiger dynamisch, um ihre Stellung im Kasten zu optimieren.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 4, 'sort_order' => 103],
            ['key' => 'defensive-block-logic', 'title' => 'Ketten-Synchronisation', 'summary' => 'Verbesserung der Absprache zwischen Innenverteidigern und Sechsern beim Verschieben gegen den Ball (Zonendeckung).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4, 'sort_order' => 104],
            ['key' => 'emotional-match-swings', 'title' => 'Emotionaler Momentum-Faktor', 'summary' => 'Tore oder Platzverweise loesen sichtbare Phasen von Euphorie oder Verunsicherung aus, die Passgenauigkeit und Zweikampfwerte kurzzeitig beeinflussen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 105],
            ['key' => 'weather-simulation-depth', 'title' => 'Wetter-Dynamik 2.0', 'summary' => 'Ploetzlicher Regen oder Nebel waehrend der Partie zwingt Teams zu taktischen Anpassungen (mehr Distanzschuesse, sichereres Passspiel).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 106],
            ['key' => 'referee-personalities', 'title' => 'Individuelle Schiedsrichter-Linien', 'summary' => 'Schiris haben eigene Toleranzschwellen fuer Karten. Ein strenger Unparteiischer beeinflusst die Aggressivitaet deiner Zweikampf-Anweisungen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 107],
            ['key' => 'animation-blending-matchapp', 'title' => 'Animation-Blending (Visual)', 'summary' => 'Fluessigere Uebergaenge zwischen Lauf-, Schuss- und Grätsch-Animationen im Match-Center fuer eine realistischere Darstellung.', 'status' => 'in_progress', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 5, 'sort_order' => 108],
            ['key' => 'crowd-dynamic-visuals', 'title' => 'Reaktive Zuschauer-Kulisse', 'summary' => 'Die Stimmung im Match-Center spiegelt den Spielverlauf wieder: Pfiffe bei Ballgeschiebe, Ekstase bei Last-Minute-Toren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 109],
            ['key' => 'offside-var-drama', 'title' => 'VAR-Entscheidungen mit Spannung', 'summary' => 'Einbau von VAR-Checks bei knappen Abseitsstellungen oder Elfern, inklusive kurzer Wartezeit und emotionaler Reaktion.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 110],
            ['key' => 'player-collisions-engine', 'title' => 'Physische Spieler-Kollisionen', 'summary' => 'Groesse und Gewicht spielen eine groessere Rolle in Kopfball-Duellen und beim Abschirmen des Balls.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4, 'sort_order' => 111],
            ['key' => 'tactical-foul-ai', 'title' => 'KI-Taktikfouls', 'summary' => 'Verteidiger mit hohem Aggressivitaets-Wert nutzen taktische Fouls, um gefaehrliche Konter im Ansatz zu unterbinden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 112],
            ['key' => 'match-intensity-curves', 'title' => 'Match-Intensitaetskurven', 'summary' => 'Spiele haben nun Schlusssprints oder Leerlaufphasen, basierend auf Tabellensituation und Kraeftehaushalt beider Teams.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 113],
            ['key' => 'injury-animations-rework', 'title' => 'Verletzungs-Visualisierung', 'summary' => 'Spieler hinken oder halten sich betroffene Stellen, bevor sie ausgewechselt werden muessen.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 114],
            ['key' => 'captain-influence-match', 'title' => 'Kapitaenspraesenz auf dem Platz', 'summary' => 'Ein starker Kapitaen verbessert die Standhaftigkeit seiner Mitspieler nach Gegentoren oder in Druckphasen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 115],

            // 2. TAKTIK & COACHING (15)
            ['key' => 'in-game-shouts-expansion', 'title' => 'Erweiterte Coaching-Anweisungen', 'summary' => 'Neue Befehle wie "In die Zweikaempfe werfen" oder "Tempo verschleppen" mit direkter Wirkung auf die Match Engine.', 'status' => 'in_progress', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 2, 'sort_order' => 116],
            ['key' => 'opposition-instructions', 'title' => 'Gegnerspezifische Anweisungen', 'summary' => 'Gezieltes Doppeln von Stars, hartes Angehen bestimmter Spieler oder Bewachen des schwachen Fusses.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 117],
            ['key' => 'asymmetric-formations', 'title' => 'Asymmetrische Formationen', 'summary' => 'Volle Freiheit beim Positionieren der Startelf, um zum Beispiel einen extrem eingerueckten Fluegelspieler oder asymmetrische Aussenverteidiger zu spielen.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5, 'sort_order' => 118],
            ['key' => 'tactical-familiarity', 'title' => 'Taktische Vertrautheit', 'summary' => 'Spieler muessen Formationen und Anweisungen ueber Wochen trainieren, um volle Effizienz und blindes Verstaendnis zu erreichen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 119],
            ['key' => 'player-role-specializations', 'title' => 'Rollen-Spezialisierungen', 'summary' => 'Unterscheidung zwischen klassischen Rollen wie "Raumdeuter", "Inverted Wingback" oder "Ballerobernder Mittelfeldspieler" mit spezifischen Laufwegen.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 5, 'sort_order' => 120],
            ['key' => 'transition-tactics', 'title' => 'Umschaltspiel-Phasen', 'summary' => 'Getrennte Anweisungen fuer "Nach Ballverlust" (Gegenpressing vs. Rückzug) und "Nach Ballgewinn" (Konter vs. Ballbesitz).', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 4, 'sort_order' => 121],
            ['key' => 'set-piece-creator-v2', 'title' => 'Standards-Editor 2.0', 'summary' => 'Detailliertes Planen von Laufwegen bei Ecken und Freistoessen inklusive Blockbildung und Zielzonen-Zuweisung.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5, 'sort_order' => 122],
            ['key' => 'individual-player-instructions', 'title' => 'Individuelle Spieler-Anweisungen', 'summary' => 'Zusatzanweisungen fuer einzelne Akteure wie "Mehr Risiko waegen", "Position halten" oder "Staendig flanken".', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 123],
            ['key' => 'defensive-width-control', 'title' => 'Defensive Breitensteuerung', 'summary' => 'Einstellung, wie tief oder breit die Mannschaft bei gegnerischem Ballbesitz verteidigt, um Fluegel oder Zentrum zu verdichten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 124],
            ['key' => 'low-block-mastery', 'title' => 'Bus parken (Tiefer Block)', 'summary' => 'Gezielte Taktik-Profile fuer extrem defensives Mauern gegen Top-Clubs inklusive Konter-Automatisierung.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 125],
            ['key' => 'tactical-analysis-dashboard', 'title' => 'Live-Taktikanalyse', 'summary' => 'Dashboard waehrend des Spiels, das zeigt, wo der Gegner raeumlich ueberlegen ist und welche Paesse am haeufigsten zum Erfolg fuehren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4, 'sort_order' => 126],
            ['key' => 'coach-style-identity', 'title' => 'Trainer-Identitaet & Stil', 'summary' => 'Dein gewaehlter Stil (Catenaccio, Tiki-Taka, Gegenpressing) beeinflusst die Lernrate bestimmter Taktiken und deinen Ruf.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 127],
            ['key' => 'substitute-impact-prediction', 'title' => 'Einwechsel-Effekt-Prognose', 'summary' => 'Dein Co-Trainer gibt Tipps, welche Einwechslung das aktuelle Spielgeschehen am wahrscheinlichsten positiv veraendert.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 128],
            ['key' => 'game-model-storage', 'title' => 'Taktische Master-Profile', 'summary' => 'Speichere ganze Spielmodelle inklusive Trainingseinheiten, damit ein Systemwechsel keine Neuausrichtung des gesamten Vereins erfordert.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 129],
            ['key' => 'bench-communication', 'title' => 'Kabinenansprache in der Pause', 'summary' => 'Interaktive Pausenansprachen mit Wirkung auf die Moral und Ausdauerwerte fuer die zweite Halbzeit.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 130],

            // 3. TRAINING & ENTWICKLUNG (10)
            ['key' => 'individual-training-paths', 'title' => 'Individuelle Entwicklungspfade', 'summary' => 'Spieler gezielt auf neue Positionen umschulen oder spezifische Schwaechen (z.B. Kopfballspiel) ueber Monate ausmerzen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 131],
            ['key' => 'load-management-v2', 'title' => 'Belastungssteuerung 2.0', 'summary' => 'Detailliertes Reporting ueber Trainingsintensitaet zur Vermeidung von Ermuedungsbruechen und Muskelverletzungen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 132],
            ['key' => 'mental-training-center', 'title' => 'Mentaltraining & Resilienz', 'summary' => 'Fokus auf Nervenstaerke, Konzentration und Fuehrungsqualitaeten als trainierbare Attribute.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 133],
            ['key' => 'youth-bridge-training', 'title' => 'Perspektivspieler-Integration', 'summary' => 'Konzepte wie "Training mit den Profis" steigern die Lernkurve von Talenten massiv, bergen aber Frustrisiko bei etablierten Kraeften.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 134],
            ['key' => 'training-intensity-impact', 'title' => 'Intensitaets-Risiko-Abwaegung', 'summary' => 'Waehle zwischen "Hard Work" (schneller Fortschritt, hohes Verletzungsrisiko) und "Recovery Focus".', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 135],
            ['key' => 'coach-specialization-impact', 'title' => 'Spezialtrainer-Effektivität', 'summary' => 'Fachspezifischer Staff (z.B. Angriffs-Coach) verbessert gezielt die Gruppenleistungen im Training.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 136],
            ['key' => 'learning-rate-factors', 'title' => 'Lernraten-Faktoren', 'summary' => 'Einfluss von Alter, Intelligenz und Ehrgeiz auf die Geschwindigkeit, mit der neue Faehigkeiten erlernt werden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 137],
            ['key' => 'training-camp-manager', 'title' => 'Trainingslager-Strategien', 'summary' => 'Planung von Sommer-/Wintertrainingslagern mit Fokus auf Teambuilding oder taktische Neuausrichtung.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 138],
            ['key' => 'retired-player-staff-path', 'title' => 'Ex-Spieler als Staff-Trainees', 'summary' => 'Ehemalige Leistungstraeger koennen als Junior-Coaches in den Stab integriert werden (Identitaetsbonus).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 139],
            ['key' => 'recovery-science-center', 'title' => 'Sportwissenschaftliche Analytik', 'summary' => 'Hochmoderne Daten zur Regenerationsfaehigkeit nach Spielen als Entscheidungsgrundlage fuer Rotationen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 140],

            // 4. FINANZEN & SPONSORING (15)
            ['key' => 'dynamic-kit-sponsorship', 'title' => 'Dynamisches Trikotsponsoring', 'summary' => 'Sponsorenangebote variieren nun basierend auf deiner Social-Media-Reichweite und sportlichem Erfolg.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 141],
            ['key' => 'crypto-fan-tokens', 'title' => 'Fan-Token & Krypto-Investoren', 'summary' => 'Neue Finanzierungsmodelle durch Fan-Tokens, die kurzfristig Cash bringen, aber das Mitspracherecht der Fans beeinflussen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 142],
            ['key' => 'luxury-tax-system', 'title' => 'Financial Fairplay & Luxury Tax', 'summary' => 'Einfuehrung von Strafzahlungen bei Ueberschreitung des Gehaltsbudgets, die in einen Topf fuer kleinere Vereine fliessen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 4, 'sort_order' => 143],
            ['key' => 'merchandising-expansion', 'title' => 'Globales Merchandising-Netzwerk', 'summary' => 'Eroeffne Fanshops in Asien oder USA, um die Einnahmen aus Trikotverkaeufen deiner auslaendischen Stars zu maximieren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 3, 'sort_order' => 144],
            ['key' => 'stadium-naming-rights', 'title' => 'Stadion-Namensrechte-Auktion', 'summary' => 'Verhandle jaehrlich neu ueber den Stadionnamen mit verschiedenen Marken (Auto-Branche vs. Versicherungen).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 145],
            ['key' => 'investor-relations-v2', 'title' => 'Investoren-Pitch-System', 'summary' => 'Praesentiere deine Vision vor einem Board von Investoren, um Sonderbudgets fuer Transfers oder Bauvorhaben freizuschalten.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 5, 'sort_order' => 146],
            ['key' => 'ticket-pricing-algo', 'title' => 'Dynamische Ticketpreise', 'summary' => 'Algorithmus zur Berechnung der optimalen Ticketpreise basierend auf Gegner-Attraktivitaet und Wettervorhersage.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 147],
            ['key' => 'loan-repayment-plans', 'title' => 'Schulden-Management & Kredite', 'summary' => 'Umschuldung von Altkrediten mit variablen Zinssaetzen als taktisches Finanzmittel in Krisenzeiten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 148],
            ['key' => 'tax-optimization-strategies', 'title' => 'Steuer-Optimierungs-Tools', 'summary' => 'Heuere externe Berater an, um die Steuerlast des Vereins legal zu senken und mehr Netto-Budget zu generieren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 149],
            ['key' => 'broadcasting-rights-negotiation', 'title' => 'TV-Rechte-Verteilung 2.0', 'summary' => 'Neues System zur Verteilung der TV-Gelder basierend auf Einschaltquoten deiner Spiele.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 4, 'sort_order' => 150],
            ['key' => 'financial-audits-impact', 'title' => 'Lizenzierungs-Audits', 'summary' => 'Regelmaessige Checkups durch den Verband; bei Verstößen drohen Punktabzuege oder Transfersperren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 151],
            ['key' => 'player-value-index', 'title' => 'Marktwert-Indikator 2.0', 'summary' => 'Ein realistischerer Marktwert-Algorithmus unter Beruecksichtigung von Vertragslaufzeit und Ligen-Prestige.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 152],
            ['key' => 'secondary-sponsor-slots', 'title' => 'Aermel- & Hosen-Sponsoren', 'summary' => 'Zusaetzliche Werbeflaechen auf der Ausruestung fuer regionale Partner (Baustoffhaendler, Baeckereien).', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 153],
            ['key' => 'bankruptcy-protection-logic', 'title' => 'Insolvenz-Szenarien', 'summary' => 'Spannende Survival-Mechanik: Rette den Club vor dem Bankrott durch Notverkaeufe und Gehaltsverzichte.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 154],
            ['key' => 'signing-bonus-impact', 'title' => 'Handgeld-Ökonomie', 'summary' => 'Handgelder beeinflussen das Gehaltsgefuege innerhalb der Kabine und koennen Neid schueren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 155],

            // 5. INFRASTRUKTUR & STADION (15)
            ['key' => 'stadium-expansion-modular', 'title' => 'Modulare Stadion-Erweiterung', 'summary' => 'Baue einzelne Tribuenen schrittweise aus (Stehplaetze vs. VIP-Logen) statt eines kompletten Neubaus.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5, 'sort_order' => 156],
            ['key' => 'turf-heating-systems', 'title' => 'Rasenheizung-Investition', 'summary' => 'Vermeide Spielabsagen im Winter und schuetze deine Spieler vor Verletzungen auf gefrorenem Boden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 157],
            ['key' => 'training-facility-v3', 'title' => 'High-Performance Center', 'summary' => 'Upgrade des Gelaendes mit Kaeltekammern, Unterwasser-Laufbaendern und VR-Taktik-Raeumen.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4, 'sort_order' => 158],
            ['key' => 'solar-stadium-roof', 'title' => 'Nachhaltige Arena (PV-Anlage)', 'summary' => 'Investition in Solaranlagen auf dem Stadiondach zur Senkung der laufenden Betriebskosten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 3, 'sort_order' => 159],
            ['key' => 'vip-experience-tiers', 'title' => 'VIP-Hospitality-Stufen', 'summary' => 'Unterschiedliche Standards in den Logen locken zahlungskraeftigere Business-Partner an.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 160],
            ['key' => 'public-transport-link', 'title' => 'Oeffentlicher Nahverkehr-Anschluss', 'summary' => 'Kooperation mit Staedten zur besseren Erreichbarkeit des Stadions (hoehere Zuschauerzahlen).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 161],
            ['key' => 'stadium-security-upgrade', 'title' => 'Sicherheits-Infrastruktur', 'summary' => 'Investition in Kameras und Ordner zur Senkung von Strafzahlungen durch Fan-Ausschreitungen.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 162],
            ['key' => 'video-cube-v2', 'title' => 'Interaktiver Videowuerfel', 'summary' => 'Neue Werbeflaechen im Stadion waehrend des Spiels steigern den Sponsoren-Output.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 163],
            ['key' => 'museum-legacy-center', 'title' => 'Vereinsmuseum & Walk of Fame', 'summary' => 'Steigert den Prestigewert des Clubs und generiert zusaetzliche Tourismus-Einnahmen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 164],
            ['key' => 'parking-multistory', 'title' => 'Parkhaus-Komplex', 'summary' => 'Parkgebuehren als neue, stetige Einnahmequelle an spielfreien Tagen (P+R Konzepte).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 165],
            ['key' => 'wifi-stadium-connected', 'title' => 'Hybrides Stadion (Free WiFi)', 'summary' => 'Modernstes Internet im Stadion verbessert die Fan-Zufriedenheit und digitale Marketingdaten.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 166],
            ['key' => 'integrated-hotel-complex', 'title' => 'Stadion-Hotel & Gastro-Meile', 'summary' => 'Maximale Auslastung des Club-Gelaendes auch ausserhalb des Ligabetriebs.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5, 'sort_order' => 167],
            ['key' => 'hybrid-pitch-upgrade', 'title' => 'Hybrid-Rasen-Installation', 'summary' => 'Ein robusterer Platz erlaubt mehr Trainingseinheiten und haehlt hohen Belastungen stand.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 168],
            ['key' => 'drainage-system-rework', 'title' => 'Modernes Drainage-System', 'summary' => 'Verhindert Schlammschlachten bei Starkregen und schont die Gelenke deiner Stars.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 169],
            ['key' => 'fan-zone-plaza', 'title' => 'Fan-Zone & Plaza-Entwicklung', 'summary' => 'Ein Treffpunkt vor dem Stadion steigert den Konsum von Speisen und Getränken vor Anpfiff.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 170],

            // 6. TRANSFERMARKT & SCOUTING (15)
            ['key' => 'scouting-assignment-v2', 'title' => 'Praezise Scouting-Auftraege', 'summary' => 'Schicke Scouts gezielt nach "Linksfuessen mit hoher Schnelligkeit" oder "Ersatz fuer Leistungstraeger X".', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 171],
            ['key' => 'transfer-deadline-day-drama', 'title' => 'Deadline-Day-Event-Ticker', 'summary' => 'Ein interaktiver Last-Minute-Ticker erzeugt Spannung und Zeitdruck bei Transferverhandlungen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 172],
            ['key' => 'buy-back-clauses', 'title' => 'Rueckkauf-Optionen', 'summary' => 'Sichere dir Top-Talente ab, indem du bei einem Verkauf eine feste Rueckkaufsumme vereinbarst.', 'status' => 'quick', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 173],
            ['key' => 'performance-based-bonuses', 'title' => 'Leistungsbezogene Bonuszahlungen', 'summary' => 'Senke das Fixgehalt durch Praemien fuer Tore, Vorlagen oder Weiße Westen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 174],
            ['key' => 'tapping-up-logic', 'title' => 'Spieler-Anlocken (Tapping Up)', 'summary' => 'Bekunde oeffentliches Interesse, um den Spieler zur Unzufriedenheit zu treiben und den Preis zu senken.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 175],
            ['key' => 'transfer-value-volatility', 'title' => 'Transferwert-Volatilitaet', 'summary' => 'Preise fuer Spieler schwanken nun je nach Marktlage (z.B. England-Inflations-Faktor).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 4, 'sort_order' => 176],
            ['key' => 'scouting-knowledge-network', 'title' => 'Länder-Wissen der Scouts', 'summary' => 'Dein Verein sammelt Wissen ueber Nationen (Brasilien, Frankreich); je mehr Wissen, desto genauer die Berichte.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 177],
            ['key' => 'release-clause-inflation', 'title' => 'Dynamische Ausstiegsklauseln', 'summary' => 'Klauseln passen sich nun automatisch an, wenn ein Spieler eine bestimmte Entwicklungsschwelle erreicht.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 178],
            ['key' => 'player-exchange-deals', 'title' => 'Spielertausch-Geschaefte', 'summary' => 'Druecke die Abloesesumme, indem du einen deiner unnoetigen Spieler als Teil des Deals anbietest.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 179],
            ['key' => 'video-scouting-platform', 'title' => 'Video-Scouting-Portal', 'summary' => 'Nutze digitale Daten und Highlights, um Vorabauswahlen zu treffen, ohne einen Scout physisch zu entsenden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 180],
            ['key' => 'medical-check-risks', 'title' => 'Medizincheck-Fehlschlaege', 'summary' => 'Ein Transfer kann im letzten Moment scheitern, wenn unentdeckte Verletzungen diagnostiziert werden.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 181],
            ['key' => 'installment-payment-plans', 'title' => 'Ratenzahlungs-Modelle', 'summary' => 'Finanziere teure Stars ueber 3-4 Jahre, um dein aktuelles Transferbudget nicht sofort zu sprengen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 182],
            ['key' => 'sell-on-percentage-deals', 'title' => 'Weiterverkaufs-Beteiligungen', 'summary' => 'Verhandle Prozentsätze für zukünftige Einnahmen, um langfristig von Talententwicklungen zu profitieren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 2, 'sort_order' => 183],
            ['key' => 'scouting-report-accuracy', 'title' => 'Bericht-Genauigkeit-Stufen', 'summary' => 'Je laenger ein Scout einen Spieler beobachtet, desto mehr "Hidden Attributes" werden sichtbar.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 3, 'sort_order' => 184],
            ['key' => 'free-agent-wars', 'title' => 'Free-Agent Wettbieten', 'summary' => 'Besondere Events fuer vertragslose Spieler mit mehreren Interessenten gleichzeitig.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 185],

            // 7. MEDIEN & PR (15)
            ['key' => 'dynamic-press-conferences', 'title' => 'Dynamische Pressekonferenzen', 'summary' => 'Deine Antworten beeinflussen die Moral des Teams und dein Ansehen bei den Fans nachhaltig.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 3, 'sort_order' => 186],
            ['key' => 'social-media-feed-v2', 'title' => 'Social-Media & Fan-Echo', 'summary' => 'Ein fiktiver Twitter-Feed reagiert in Echtzeit auf Transfergeruechte und deine Taktik-Entscheidungen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 187],
            ['key' => 'crisis-management-pr', 'title' => 'PR-Krisenmanagement', 'summary' => 'Reagiere auf Skandale abseits des Platzes (z.B. Disziplinlosigkeit) und minimiere den Imageschaden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 188],
            ['key' => 'club-documentary-series', 'title' => 'Club-Doku-Dreh (Hype)', 'summary' => 'Lasse Kamerateams hinter die Kulissen, um die globale Markenbekanntheit zu steigern (aber Unruhe-Risiko).', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 189],
            ['key' => 'player-interview-demands', 'title' => 'Spieler-Interview-Forderungen', 'summary' => 'Spieler wollen sich medial profilieren; gibst du ihnen die Plattform oder schuetzt du das Teamgefüge?', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 190],
            ['key' => 'radio-live-broadcasts', 'title' => 'Fan-Radio-Integration', 'summary' => 'Audiovisuelle Kurz-Snippets im Match Center fuer mehr Atmoa-Gefuehl.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 1, 'sort_order' => 191],
            ['key' => 'newspaper-headlines-impact', 'title' => 'Schlagzeilen-Auswirkung', 'summary' => 'Boulevardzeitungen koennen den Marktwert deiner Spieler durch Hypen oder Runtermachen beeinflussen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 192],
            ['key' => 'community-outreach-days', 'title' => 'Charity- & Community-Events', 'summary' => 'Steigere die Sympathiewerte in der Region durch Trikot-Versteigerungen und Park-Einweihungen.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 193],
            ['key' => 'leaked-info-drama', 'title' => 'Maulwurf-Szenarien', 'summary' => 'Interna gelangen an die Presse; finde den Übeltäter oder lebe mit dem Vertrauensverlust.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 194],
            ['key' => 'fan-protest-logic', 'title' => 'Fan-Protest-Mechanik', 'summary' => 'Bei anhaltender Erfolglosigkeit oder unpopulaeren Entscheidungen drohen Banner-Proteste und Stimmungsboykott.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 195],
            ['key' => 'board-ultimatum-pr', 'title' => 'Oeffentliche Vorstands-Garantie', 'summary' => 'Der Vorstand gibt dir eine "Job-Garantie"; meist der Vorbote einer Entlassung, wenn das naechste Spiel verloren geht.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 2, 'sort_order' => 196],
            ['key' => 'kit-reveal-events', 'title' => 'Trikot-Enthuellung-Events', 'summary' => 'Feiere das Design der neuen Saison und maximiere den Erstverkaufstag durch geschicktes Marketing.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2, 'sort_order' => 197],
            ['key' => 'expert-pundit-commentary', 'title' => 'Star-Experten-Meinungen', 'summary' => 'Ehemalige Profis bewerten deinen Club im Fernsehen; ihr Urteil beeinflusst die Marktwert-Stabilitaet.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2, 'sort_order' => 198],
            ['key' => 'club-anniversary-celebration', 'title' => 'Vereinsjubilaeum-Marketing', 'summary' => 'Besondere Sondertrikots und Retro-Merchandising zum Jubilaeum fuer massive Cash-Inflow-Phasen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2, 'sort_order' => 199],
            ['key' => 'official-app-launch', 'title' => 'Eigene Club-App (Digitale Welt)', 'summary' => 'Bessere Bindung junger Fans und neue digitale Werbeflaechen fuer Mobil-Sponsoren.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 3, 'sort_order' => 200],
        ];
    }

    private function uniqueKey(string $title): string
    {
        $base = Str::slug($title);
        $key = $base;
        $counter = 2;

        while (RoadmapItem::query()->where('key', $key)->exists()) {
            $key = $base.'-'.$counter;
            $counter++;
        }

        return $key;
    }
}
