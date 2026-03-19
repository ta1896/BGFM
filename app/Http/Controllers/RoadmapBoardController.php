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
            ['key' => 'dynamic-manager-career', 'title' => 'Dynamische Trainerkarriere', 'summary' => 'Die Karriere soll sich wie ein echter Weg mit Ruf, Vereinsfit, Entlassungsdruck und langfristiger Traineridentitaet anfuehlen. Im Spiel bedeutet das: Du startest nicht nur Saison fuer Saison, sondern baust dir ueber Jahre ein klares Trainerprofil auf, das bestimmt, welche Vereine dich wollen, welche Spieler dir folgen und wie viel Fehlleistung du dir ueberhaupt erlauben kannst.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 5],
            ['key' => 'board-media-fan-pressure', 'title' => 'Vorstand, Medien und Fan-Druck', 'summary' => 'Vereine sollen ueber Erwartungen, Narrative und Reaktionen aus Umfeld und Presse unterscheidbar werden. So entsteht das Gefuehl, dass ein Aufstiegskandidat ganz anders bewertet wird als ein Traditionsclub in der Krise, inklusive Schlagzeilen, Forderungen des Vorstands und spuerbarer Stimmung im Umfeld.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 3],
            ['key' => 'dressing-room-hierarchy', 'title' => 'Kabine und Teamhierarchie', 'summary' => 'Fuehrungsspieler, Unzufriedenheit, Gruppenbildung und Chemie sollen echte Auswirkungen auf Kader und Leistung haben. Bildlich heisst das: ein frustrierter Star kann die Stimmung kippen, ein Kapitaen kann eine Krise auffangen und eine schlecht moderierte Ersatzrolle kann sich langsam zu einem echten Kabinenproblem entwickeln.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 3],
            ['key' => 'club-philosophy', 'title' => 'Vereinsphilosophie statt nur Taktik', 'summary' => 'Taktik, Training, Transfers und Kaderbau sollen in eine zusammenhaengende Fussballidentitaet uebergehen. Statt einfach nur Formationen umzustellen, verfolgt der Verein dann erkennbar eine Linie wie Pressing-Fussball, Jugendfoerderung oder Ballbesitzkontrolle und alle Entscheidungen zahlen sichtbar auf dieses Gesamtbild ein.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 3],
            ['key' => 'living-football-world', 'title' => 'Eine lebendige Fussballwelt', 'summary' => 'Storylines, Ueberraschungssaisons, Krisen und Durchbruchsspieler sollen die gesamte Spielwelt lebendig machen. Man soll beim Blick auf andere Ligen sofort Geschichten sehen koennen: einen abgestuerzten Favoriten, einen Aufsteiger im Hype, einen Wunderstuermer oder eine Trainerentlassung, die ploetzlich den ganzen Markt in Bewegung bringt.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5],
            ['key' => 'youth-academy-project', 'title' => 'Jugendakademie als Langzeitprojekt', 'summary' => 'Jugendentwicklung soll strategisch, emotional und ueber mehrere Jahre hinweg planbar werden. Ziel ist eine echte Nachwuchsreise: Talente tauchen frueh auf, entwickeln Staerken und Schwaechen, brauchen die richtigen Trainer und koennen spaeter entweder Vereinsikonen oder Millionenverkaeufe werden.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5],
            ['key' => 'strategic-transfer-market', 'title' => 'Strategischer Transfermarkt', 'summary' => 'Transfers sollen von Timing, Verhandlungsmacht, Agenten, Vertragsstruktur und Marktdruck beeinflusst werden. Ein Wechsel fuehlt sich dann weniger wie ein simpler Klick an und mehr wie ein Poker aus Konkurrenz, Gehaltsstruktur, Beraterdruck, Restvertragslaufzeit und der Frage, ob du jetzt handeln musst oder besser wartest.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 5],
            ['key' => 'matchday-core-loop', 'title' => 'Spieltag als emotionaler Kernloop', 'summary' => 'Vor dem Spiel, waehrenddessen und danach soll jeder Spieltag emotional, wichtig und folgenreich sein. Konkret bedeutet das: Vorberichte, taktische Entscheidungen, ein lebendiges Match-Center und danach eine Phase, in der sich Form, Stimmung, Medien und Tabelle sofort wie echte Konsequenzen anfuehlen.', 'status' => 'in_progress', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 5, 'effort' => 2],
            ['key' => 'season-memory', 'title' => 'Saisonidentitaet und Historie', 'summary' => 'Jede Saison soll Helden, Wendepunkte, Awards und erinnerungswuerdige Geschichten hinterlassen. Am Ende soll man nicht nur die Abschlusstabelle sehen, sondern eine gefuehlte Erinnerung an diese Saison haben: das Derbytor, die Krisenphase, die Ueberraschungsmannschaft und den Spieler, ueber den noch Jahre spaeter gesprochen wird.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'club-infrastructure', 'title' => 'Vereinsinfrastruktur als Strategieebene', 'summary' => 'Stadion, Scouting, Medizin und Anlagen sollen langfristige Hebel fuer den Verein werden. Das soll sich so anfuehlen, als ob du am Fundament des Clubs arbeitest: bessere Anlagen helfen bei Entwicklung und Verletzungsprophylaxe, ein staerkeres Scouting erweitert deinen Markt und ein Stadionprojekt veraendert die Zukunft des Vereins.', 'status' => 'in_progress', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'match-awards-module', 'title' => 'Match-Auszeichnungen ausbauen', 'summary' => 'Spieler des Spiels, Parade des Spiels und Wendepunkt sollen besser erklaert, gewichteter und sichtbarer werden. Nach einem Spiel soll sofort klar werden, wer die Partie gepraegt hat, welche Szene das Momentum gedreht hat und warum genau dieser Moment spaeter noch in der Saisonerinnerung auftaucht.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'assistant-links', 'title' => 'Vorlagengeber verlinken', 'summary' => 'Neben Torschuetzen sollen auch Vorlagengeber ueberall im Match-Center klickbar sein. Dadurch werden Spielzuege greifbarer, weil nicht nur der Abschluss, sondern auch der eigentliche Architekt einer Aktion sichtbar und direkt erkundbar wird.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1],
            ['key' => 'press-conferences', 'title' => 'Pressekonferenzen vor und nach Spielen', 'summary' => 'Kurze Presseevents sollen Einfluss auf Stimmung, Erwartungen und mediale Storylines haben. Vor wichtigen Spielen kannst du Druck rausnehmen oder bewusst Spannung aufbauen, nach Niederlagen erklaerst du Krisen oder schuetzt dein Team und genau daraus entstehen spaeter Schlagzeilen und Erzaehlstraenge.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'dynamic-board-goals', 'title' => 'Dynamische Vorstandsziele', 'summary' => 'Vorstandsziele sollen sich an Tabelle, Finanzen, Kaderalter und Vereinsphilosophie orientieren. Ein Verein im Umbruch fordert dann vielleicht Jugend und Stabilitaet, waehrend ein reicher Titelkandidat sofort Resultate und Prestige verlangt, was deine Planung deutlich anders aussehen laesst.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'fan-mood-system', 'title' => 'Fans und Stimmungslage', 'summary' => 'Fans sollen auf Leistungen, Rivalenspiele, Transfers und Vereinsentscheidungen reagieren. Bildlich heisst das: Nach einer Derbypleite kippt die Stimmung, ein Jugendspieler aus dem eigenen Nachwuchs wird zum Liebling und ein unpopulaerer Verkauf loest sofort Diskussionen im Umfeld aus.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'club-rivalries', 'title' => 'Rivalitaeten und Derbylogik', 'summary' => 'Derbys und Rivalitaeten sollen mehr emotionales Gewicht und besondere Auswirkungen im Saisonverlauf haben. Vor diesen Spielen ist die Stimmung elektrischer, Niederlagen schmerzen laenger und ein Derby-Sieg kann Fans, Presse und Kabine fuer Wochen mitziehen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'captaincy-system', 'title' => 'Kapitaensamt und Mannschaftsrat', 'summary' => 'Kapitaene und Fuehrungsgruppen sollen bei Form, Moral und Konflikten eine Rolle spielen. Ein guter Kapitaen kann eine Krise abfedern, waehrend ein schwacher oder uebergangener Fuehrungsspieler unterschwellig Spannung in den Kader bringt.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'injury-context', 'title' => 'Verletzungen mit Kontext', 'summary' => 'Verletzungen sollen durch Belastung, Training, medizinische Abteilung und Spielstil beeinflusst werden. Statt zufaelliger Ereignisse soll man sehen koennen, warum eine englische Woche, falsche Belastungssteuerung oder ein aggressiver Stil ploetzlich mehrere Ausfaelle ausloest.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'player-form-arcs', 'title' => 'Formkurven fuer Spieler', 'summary' => 'Spieler sollen Formhochs, Krisen und mentale Phasen ueber mehrere Wochen hinweg erleben. Dadurch hat man nicht nur Zahlenwerte, sondern richtige Geschichten wie einen Stuermer im Rausch, einen jungen Spieler im Leistungsloch oder einen Rueckkehrer, der langsam wieder Selbstvertrauen tankt.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'training-focus-profiles', 'title' => 'Trainingsschwerpunkte und Profile', 'summary' => 'Training soll gezielte Entwicklungsprofile wie Tempo, Pressing oder Spielaufbau foerdern. So sieht man nicht nur einen allgemeinen Trainingsbalken, sondern arbeitet bewusst an Spielerrollen und Mannschaftsbausteinen fuer die Idee, die man ueber Monate etablieren will.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'set-piece-planning', 'title' => 'Standardsituationen planen', 'summary' => 'Freistoesse, Ecken und Elfmeter sollen mit Rollen, Routinen und Spezialisten vorbereitet werden koennen. Das fuehlt sich dann an wie ein echter Trainerbereich, in dem man bestimmte Laufwege, Zielspieler und Varianten bewusst trainiert und spaeter im Match wiedererkennt.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'loan-management', 'title' => 'Leihmanagement erweitern', 'summary' => 'Leihspieler sollen ueber Einsatzgarantien, Entwicklungsziele und Rueckmeldungen gesteuert werden. Statt Leihen einfach nur wegzuschicken, verfolgst du dann ihre Minuten, ihre Rolle und bekommst echte Signale, ob ein Aufenthalt gerade hilft oder ein Talent verschwendet wird.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'staff-specialists', 'title' => 'Spezialisierte Mitarbeiterrollen', 'summary' => 'Weitere Staff-Rollen wie Analysten, Standards-Coaches oder Leihkoordinatoren sollen Mehrwert schaffen. Dadurch wirkt der Verein professioneller, weil nicht nur ein generischer Staff existiert, sondern echte Spezialisten mit klaren Verantwortungen und Stärken.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'goalkeeper-identity', 'title' => 'Torwartprofil und Spieleroeffnung', 'summary' => 'Torhueter sollen sich in Spielaufbau, Risikoprofil und Strafraumbeherrschung staerker unterscheiden. Ein Torwart soll sich dann nicht mehr wie der andere anfuehlen, sondern wirklich wie ein Sweeper-Keeper, Linienkeeper oder Luftzweikampf-Monster spielbar sein.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'scouting-assignments-map', 'title' => 'Scoutingkarte und Regionen', 'summary' => 'Scouting soll visuell ueber Regionen, Fokuslaender und Einsatzgebiete steuerbar werden. Bildlich ist das eine Karte, auf der du erkennst, wo dein Verein sucht, welche Regionen vernachlaessigt sind und wo ploetzlich ein neuer Talent-Hotspot entsteht.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'transfer-shortlists', 'title' => 'Transfer-Shortlists mit Regeln', 'summary' => 'Scoutinglisten sollen Prioritaeten, Preisgrenzen und Notfalloptionen abbilden koennen. So sieht man nicht nur Namen, sondern echte Transferplaene mit Erstwahl, guenstiger Alternative und Notfallkandidaten fuer den letzten Tag des Fensters.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1],
            ['key' => 'contract-negotiation-depth', 'title' => 'Vertragsverhandlungen vertiefen', 'summary' => 'Praemien, Rollenversprechen, Ausstiegsklauseln und Bonuspakete sollen relevanter werden. Eine Verlaengerung wird damit zu einem echten Verhandlungstisch, an dem du zwischen Geld, Spielzeit, Zukunftsaussicht und Risiko abwaegst, statt nur einen simplen Gehaltsregler zu verschieben.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 4],
            ['key' => 'agent-relationships', 'title' => 'Spielerberater und Beziehungen', 'summary' => 'Berater sollen Transfers, Verlaengerungen und Erwartungshaltungen beeinflussen. Manche Agenten koennen Deals erleichtern, andere bauen Druck auf, leaken Geruechte in die Medien oder blockieren Verlaengerungen, wenn du ihre Spieler nicht gut genug behandelst.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5],
            ['key' => 'salary-structure', 'title' => 'Gehaltsstruktur und Kaderbalance', 'summary' => 'Unfaire Gehaltsgefaelle sollen Unruhe ausloesen und strategische Auswirkungen haben. Wenn ein Reservist ploetzlich mehr verdient als tragende Kraefte, fuehlt sich das wie ein echter Fehler in der Kaderplanung an und nicht nur wie eine Zahl in einer Tabelle.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'player-promises', 'title' => 'Spielerversprechen und Konsequenzen', 'summary' => 'Versprechen zu Einsatzzeit, Position oder Transfers sollen verbindlicher und riskanter werden. Ein eingehaltenes Versprechen staerkt Vertrauen, ein gebrochenes zieht sich dagegen spaeter durch Moral, Kabine und Vertragsverhandlungen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'club-culture-traits', 'title' => 'Vereinskultur und Identitaetsmerkmale', 'summary' => 'Jeder Verein soll kulturelle Merkmale wie Nachwuchs, Mentalitaet oder Risikoappetit besitzen. Dadurch wirken Clubs nicht wie austauschbare Huelle, sondern wie Organisationen mit eigenem Charakter, der auch Entscheidungen des Trainers formt.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'history-room', 'title' => 'Historienraum fuer Rekorde und Legenden', 'summary' => 'Vereinsrekorde, Legenden und historische Vergleiche sollen sichtbar gemacht werden. Das soll wie ein digitaler Vereinsflur wirken, in dem du Rekorde, grosse Saisons und praegende Namen entdecken und in Beziehung zur aktuellen Mannschaft setzen kannst.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 1],
            ['key' => 'season-recaps', 'title' => 'Saisonrueckblicke', 'summary' => 'Am Saisonende soll es Uebersichten zu Wendepunkten, Helden, Krisen und Awards geben. So fuehlt sich ein Jahr nicht einfach beendet an, sondern wie ein Rueckblick auf eine komplette Geschichte mit einem klaren Bogen.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'club-legends', 'title' => 'Vereinslegenden und Ikonen', 'summary' => 'Besonders praegende Spieler und Trainer sollen als Legenden in der Historie auftauchen. Wenn jemand eine Aera gepraegt hat, soll er spaeter sichtbar Teil der Vereinsidentitaet bleiben und nicht einfach in einer alten Datenzeile verschwinden.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'match-story-generator', 'title' => 'Match-Story-Generator', 'summary' => 'Spiele sollen staerker ueber Narrative, Kontexte und Presselines zusammengefasst werden. Ein 2:1 ist dann nicht nur ein Resultat, sondern vielleicht ein spaetes Comeback, ein verschenkter Vorsprung oder der Moment, in dem ein junger Spieler seinen Durchbruch erlebt.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 4],
            ['key' => 'advanced-match-stats', 'title' => 'Erweiterte Match-Statistiken', 'summary' => 'xG, Zonen, Druckphasen und Chancenqualitaet sollen das Match-Center vertiefen. Statt nur Schuesse und Ballbesitz zu sehen, erkennst du dann, warum ein 1:1 eigentlich ein dominantes Spiel oder eine glueckliche Rettung war.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'shot-map', 'title' => 'Schusskarte im Match-Center', 'summary' => 'Eine visuelle Schusskarte soll Chancenqualitaet und Spielmuster sichtbar machen. Auf einen Blick siehst du dann, ob dein Team nur aus 25 Metern schiesst oder sich wirklich in den Strafraum kombiniert.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'heatmaps', 'title' => 'Heatmaps fuer Teams und Spieler', 'summary' => 'Bewegungsmuster und Schwerpunktzonen sollen als Heatmap analysierbar sein. Dadurch erkennst du bildlich, ob dein Linksverteidiger staendig zu hoch steht, dein Zehner frei zwischen den Linien auftaucht oder dein Pressing eine Seite komplett ueberlaedt.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 4],
            ['key' => 'live-tactical-changes', 'title' => 'Live-Taktikanpassungen im Spiel', 'summary' => 'Waerend des Spiels sollen mehr direkte Eingriffe und deren Folgen moeglich sein. Du sollst das Gefuehl haben, wirklich von der Seitenlinie einzugreifen: Pressing anziehen, tiefer stehen, einen Fluegel ueberladen oder einen Gegenspieler gezielt isolieren und anschliessend sehen, wie das Match kippt.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 5, 'effort' => 5],
            ['key' => 'bench-reactions', 'title' => 'Bankreaktionen und Spieltagsdynamik', 'summary' => 'Die Ersatzbank soll auf Spielverlauf, Einwechslungen und Frust sichtbar reagieren. Damit wirkt der Spieltag lebendiger, weil auch neben dem Ball Emotionen und Spannungen erkennbar werden.', 'status' => 'planned', 'category' => 'quick', 'size_bucket' => 'small', 'priority' => 2, 'effort' => 2],
            ['key' => 'post-match-analysis-room', 'title' => 'Analysebereich nach dem Spiel', 'summary' => 'Nach dem Spiel sollen klare Erkenntnisse, Schluesselszenen und To-dos sichtbar werden. Das ist der Ort, an dem du sofort verstehst, was funktioniert hat, wo dein System gebrochen ist und welche Entscheidungen fuer das naechste Spiel daraus folgen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'medical-risk-dashboard', 'title' => 'Medizinisches Risikodashboard', 'summary' => 'Belastung, Rueckfallrisiko und individuelle Warnsignale sollen auf einen Blick sichtbar sein. Man soll sofort erkennen koennen, welcher Spieler gerade nur noch auf dem Zahnfleisch geht, wer kurz vor einer Muskelverletzung steht und wen du besser aus dem Kader nimmst.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'rehab-paths', 'title' => 'Reha-Pfade fuer verletzte Spieler', 'summary' => 'Rueckkehrplaene sollen je nach Spieler, Verletzung und medizinischem Team unterschiedlich verlaufen. Man soll also nicht einfach nur ein Enddatum sehen, sondern den Weg zur Rueckkehr mit Rueckschlaegen, vorsichtiger Belastung und echten Entscheidungen im Hintergrund.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 4],
            ['key' => 'training-camps', 'title' => 'Trainingslager und Saisonvorbereitung', 'summary' => 'Trainingslager sollen Form, Chemie, Taktik und Fitness zum Saisonstart beeinflussen. Das ist die Phase, in der man bewusst einen Saisoncharakter vorbereitet und nicht nur Tage im Kalender ueberspringt.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'sponsor-negotiations', 'title' => 'Sponsoren und Partnerdeals', 'summary' => 'Sponsoren sollen mit Zielen, Image und finanziellen Paketen verhandelbar werden. Dadurch entsteht eine wirtschaftliche Ebene, in der sportlicher Erfolg, Vereinsimage und strategische Ausrichtung direkt in echte Partnerschaften uebersetzt werden.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 5],
            ['key' => 'merchandising-and-brand', 'title' => 'Merchandising und Markenaufbau', 'summary' => 'Der Verein soll seine Marke ueber Erfolg, Stars und internationale Sichtbarkeit entwickeln. Das fuehlt sich dann so an, als ob dein Club nicht nur sportlich waechst, sondern auch ausserhalb des Platzes an Bedeutung gewinnt.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 2, 'effort' => 5],
            ['key' => 'stadium-expansion-decisions', 'title' => 'Stadionausbau und Standortentscheidungen', 'summary' => 'Stadionprojekte sollen Budget, Fans, Infrastruktur und Langzeitstrategie verbinden. Das soll sich wie eine echte Vereinsentscheidung anfuehlen: baust du aus, modernisierst du, gehst du ein Finanzrisiko ein oder bleibst du kleiner und investierst lieber in Kader und Nachwuchs.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 5],
            ['key' => 'b-team-reserves', 'title' => 'Zweite Mannschaft und Reservebetrieb', 'summary' => 'Eine Reserve oder U23 soll junge Spieler sinnvoll an den Profifussball heranfuehren. Das ergibt eine glaubwuerdigere Entwicklungskette zwischen Akademie und erster Mannschaft, statt Talente abrupt ins kalte Wasser zu werfen.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'academy-staff-tree', 'title' => 'Jugendstaff und Akademiestruktur', 'summary' => 'Die Jugend soll ein eigenes Staff- und Entwicklungsgeruest erhalten. Damit wirkt die Akademie wie ein echter eigener Bereich mit Philosophie, Verantwortlichen und bewusstem Aufbau statt wie ein abstrakter Talentgenerator.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'regens-personality-depth', 'title' => 'Mehr Persoenlichkeit bei Jugendspielern', 'summary' => 'Jugendspieler sollen ueber Charakter, Herkunft und Hintergrundprofil greifbarer werden. So wird ein Talent nicht nur wegen seiner Werte interessant, sondern auch wegen seiner Geschichte und dem, was aus ihm werden koennte.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 4, 'effort' => 2],
            ['key' => 'regional-identity', 'title' => 'Regionale Identitaet von Vereinen', 'summary' => 'Vereine sollen staerker durch Region, Nachwuchsgebiet und lokale Erwartungen gepraegt sein. Ein Klub aus einer Arbeiterregion oder einer Fussballhochburg soll sich spaeter auch in Erwartungshaltung, Nachwuchsprofil und Fanbild anders anfuehlen.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'league-story-hub', 'title' => 'Liga-Story-Hub', 'summary' => 'Eine Hub-Seite soll wichtige Storys, Krisen, Titelkaempfe und Ueberraschungen einer Liga erzaehlen. Das ist der Ort, an dem du beim Oeffnen sofort spuerst, was in dieser Saison gerade wirklich relevant ist.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 4],
            ['key' => 'manager-rumours', 'title' => 'Trainergeruechte und Jobmarkt', 'summary' => 'Der Markt fuer Trainer soll dynamisch werden und auch den eigenen Klub unter Druck setzen. Dadurch entsteht das Gefuehl, dass nicht nur Spieler, sondern auch du selbst Teil eines nervoesen, reaktiven Fussballmarkts bist.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 4],
            ['key' => 'international-breaks', 'title' => 'Laenderspielpausen mit Folgen', 'summary' => 'Abstellungen sollen Fitness, Moral, Verletzungen und Marktwert beeinflussen. Eine Nationalmannschaftsphase soll sich also wie ein echter Einschnitt anfuehlen und nicht nur wie ein leerer Kalenderblock.', 'status' => 'planned', 'category' => 'mid', 'size_bucket' => 'small', 'priority' => 3, 'effort' => 2],
            ['key' => 'player-social-media', 'title' => 'Soziale Medien und Aussenwirkung', 'summary' => 'Spieler und Vereine sollen ueber Statements, Trends und Stimmungen sichtbarer werden. Das wuerde den modernen Fussballkosmos spiegeln, in dem Narrative nicht nur ueber klassische Presse entstehen, sondern auch ueber digitale Dynamiken.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 2, 'effort' => 5],
            ['key' => 'club-crisis-events', 'title' => 'Vereinskrisen und Sonderereignisse', 'summary' => 'Finanzprobleme, Kabinenbrueche oder Medienkrisen sollen echte Sonderlagen ausloesen. Solche Phasen sollen sich wie aussergewoehnliche Saisonsituationen anfuehlen, in denen ploetzlich alles auf dem Spiel steht und gewohnte Planung nicht mehr reicht.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 4, 'effort' => 5],
            ['key' => 'owner-personality', 'title' => 'Eigentuemerprofile und Investorenlogik', 'summary' => 'Eigentuemer sollen unterschiedliche Risikoprofile, Ziele und Eingriffe mitbringen. Ein geduldiger Traditionsbesitzer fuehlt sich dann komplett anders an als ein aggressiver Investor, der schnelle Ergebnisse und spektakulaere Entscheidungen fordert.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 3, 'effort' => 5],
            ['key' => 'women-football-expansion', 'title' => 'Frauenfussball-Ausbau', 'summary' => 'Langfristig koennte der Vereinskosmos um Frauenfussball und parallele Entwicklung erweitert werden. Das oeffnet die Moeglichkeit, einen Club breiter, moderner und ueber mehrere sportliche Bereiche hinweg zu denken.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 2, 'effort' => 5],
            ['key' => 'multiclub-network', 'title' => 'Multi-Club-Netzwerke', 'summary' => 'Vereinsgruppen mit Partnerclubs sollen Transfers, Leihen und Talententwicklung vernetzen. Damit laesst sich ein ganzer Clubverbund spielen, in dem Talente bewusst durch Ebenen entwickelt und strategisch im Netzwerk verschoben werden.', 'status' => 'planned', 'category' => 'big', 'size_bucket' => 'huge', 'priority' => 2, 'effort' => 5],
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
