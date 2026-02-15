<?php

namespace Database\Factories;

use App\Models\MatchTickerTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

class MatchTickerTemplateFactory extends Factory
{
    protected $model = MatchTickerTemplate::class;

    public function definition(): array
    {
        return [
            'event_type' => 'goal',
            'text' => 'TOR! {player} trifft!',
            'priority' => 'normal',
            'locale' => 'de',
        ];
    }

    /**
     * Predefined templates for various action types.
     */
    public static function getTemplates(): array
    {
        return [
            'goal' => [
                'TOOOOR! {player} fackelt nicht lange und jagt das Leder in die Maschen!',
                'Was für ein Treffer! {player} lässt dem Keeper keine Chance.',
                'Tor für {club}! {player} steht goldrichtig und staubt ab.',
                'Gänsehaut pur! {player} mit einem Traumtor aus der Distanz!',
                'Da ist das Ding! {player} markiert das {score}.',
                'Eiskalt! {player} verwandelt sicher zur Führung für {club}.',
                'Unglaublich! {player} schlenzt den Ball unhaltbar in den Winkel.',
                'Tor, Tor, Tor! {player} krönt seine starke Leistung mit diesem Treffer.',
                'Der Bann ist gebrochen! {player} trifft zum {score}.',
                'Präzision pur! {player} setzt den Ball flach ins linke Eck.',
                'Kopfball-Ungeheuer! {player} schraubt sich hoch und nickt ein.',
                'Blitzsauberer Konter! {player} schließt eiskalt zum {score} ab.',
                'Ein Hammer! {player} zieht aus 20 Metern ab – drin!',
                'Jubel bei {club}! {player} lässt die Abwehr alt aussehen.',
                'Spielfreude pur: {player} tanzt zwei Gegner aus und versenkt.',
                'Das Stadion bebt! {player} trifft für {club}.',
                'Abstauber-König! {player} steht genau da, wo ein Stürmer stehen muss.',
                'Wunderschön herausgespielt! {player} muss nur noch den Fuß hinhalten.',
                'Direktabnahme! {player} trifft den Ball perfekt – keine Abwehrchance.',
                'Tor! {player} behält im Gewusel die Übersicht und netzt ein.',
                // ... more will be added in the seeder for better organization
            ],
            'chance' => [
                'Fast das Tor! {player} verfehlt das Gehäuse nur um Zentimeter.',
                'Riesen-Möglichkeit für {club}! {player} scheitert am Pfosten.',
                'Was für eine Parade! Der Keeper fischt den Schuss von {player} aus dem Eck.',
                '{player} zieht ab, aber der Ball segelt knapp über die Querlatte.',
                'Fast der Jubel! {player} probiert es mit einem Heber, aber der Verteidiger klärt auf der Linie.',
                'Glanztat! {player} kommt frei zum Abschluss, aber der Torwart ist blitzschnell unten.',
                'Das hätte es sein müssen! {player} setzt den Ball völlig frei am Tor vorbei.',
                'Dicke Chance für {player}! Doch sein Schuss wird im letzten Moment geblockt.',
                'Alutreffer! {player} hämmert das Leder gegen das Lattenkreuz.',
                'Knapp vorbei! {player} zielt auf das lange Eck, aber der Ball streicht am Pfosten vorbei.',
                '{player} hat das Auge für die Lücke, aber sein Abschluss ist zu ungenau.',
                'Wahnsinn! {player} macht eigentlich alles richtig, trifft aber nur das Außennetz.',
                'Gefährlich! {player} mit einem strammen Schuss, der Keeper kann nur zur Seite abwehren.',
                '{club} drückt! {player} kommt zum Kopfball, aber der Ball geht drüber.',
                'Hundertprozentige! {player} läuft allein auf den Torwart zu, zögert aber zu lange.',
            ],
            'yellow_card' => [
                'Gelb für {player} nach einem taktischen Foul.',
                'Der Schiedsrichter zeigt {player} die Gelbe Karte. Zu hart eingestiegen.',
                'Verwarnung für {player}. Er hatte nur die Beine des Gegners im Visier.',
                'Gelbe Karte! {player} unterbindet den Konter mit unfairen Mitteln.',
                'Das hat Folgen: Gelb für {player} nach mehrmaligem Foulspiel.',
                'Gelb wegen Meckerns! {player} sollte sich besser auf das Spiel konzentrieren.',
                'Zu spät gekommen! {player} sieht völlig zurecht Gelb.',
                'Dunkelgelb! {player} kommt mit einer Verwarnung davon.',
            ],
            'red_card' => [
                'PLATZVERWEIS! {player} sieht nach diesem brutalen Foul glatt Rot!',
                'Rote Karte für {player}! {club} muss nun in Unterzahl weiterspielen.',
                'Notbremse! Der Schiedsrichter schickt {player} vorzeitig unter die Dusche.',
                'Unfassbar! {player} lässt sich zu einer Tätlichkeit hinreißen. Rot!',
            ],
            'foul' => [
                'Pfiff des Unparteiischen: Foul von {player}.',
                'Harter Einsatz von {player} gegen {opponent}. Freistoß!',
                '{player} rempelt {opponent} unsanft um. Der Schiri mahnt ihn an.',
                'Kleines Foulspiel von {player} im Mittelfeld.',
                '{player} zieht am Trikot von {opponent}. Das Spiel wird unterbrochen.',
            ],
            'substitution' => [
                'Wechsel bei {club}: {player} kommt für frischen Wind ins Spiel.',
                'Taktischer Wechsel: {player} betritt das Feld.',
                'Dienstschluss für heute. {player} übernimmt jetzt die Position.',
                'Applaus der Fans: {player} kommt neu in die Partie.',
                '{club} wechselt: {player} ist jetzt dabei.',
            ],
            'injury' => [
                'Sorgenfalten bei {club}: {player} hält sich das Knie und muss behandelt werden.',
                '{player} liegt am Boden. Das sieht nach einer Muskelverletzung aus.',
                'Kurze Unterbrechung: {player} scheint sich ohne gegnerische Einwirkung verletzt zu haben.',
                'Oje, {player} signalisiert sofort: Es geht nicht weiter.',
            ]
        ];
    }
}
