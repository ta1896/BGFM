export function routeMatches(activePattern, currentRoute) {
    if (!activePattern || !currentRoute) {
        return false;
    }

    if (activePattern.endsWith('.*')) {
        return currentRoute.startsWith(activePattern.slice(0, -1));
    }

    return currentRoute === activePattern;
}

export function findActiveMenuLabel(menuGroups, currentRoute, fallbackLabel) {
    for (const group of Object.values(menuGroups)) {
        for (const item of group.items) {
            if (routeMatches(item.active, currentRoute)) {
                return item.label;
            }
        }
    }

    return fallbackLabel;
}

export function mergeMenuGroups(baseGroups, extraGroups = {}) {
    return {
        ...baseGroups,
        ...(extraGroups || {}),
    };
}

export function getManagerMenuGroups({ hasManagedClub }) {
    if (!hasManagedClub) {
        return {
            bg_start: {
                label: 'Start',
                items: [
                    { route: 'dashboard', label: 'Dashboard', active: 'dashboard' },
                    { route: 'clubs.free', label: 'Verein waehlen', active: 'clubs.free' },
                    { route: 'profile.edit', label: 'Profil', active: 'profile.*' },
                ],
            },
        };
    }

    return {
        bg_buro: {
            label: 'Buero',
            items: [
                { route: 'dashboard', label: 'Dashboard', active: 'dashboard' },
                { route: 'notifications.index', label: 'Postfach', active: 'notifications.*' },
                { route: 'finances.index', label: 'Finanzen', active: 'finances.*' },
                { route: 'sponsors.index', label: 'Sponsoren', active: 'sponsors.*' },
                { route: 'stadium.index', label: 'Stadion', active: 'stadium.*' },
            ],
        },
        bg_team: {
            label: 'Team',
            items: [
                { route: 'lineups.index', label: 'Aufstellung', active: 'lineups.*' },
                { route: 'players.index', label: 'Kader', active: 'players.*' },
                { route: 'squad-hierarchy.index', label: 'Hierarchie', active: 'squad-hierarchy.index' },
                { route: 'training.index', label: 'Training', active: 'training.*' },
                { route: 'training-camps.index', label: 'Trainingslager', active: 'training-camps.*' },
            ],
        },
        bg_wettbewerb: {
            label: 'Wettbewerb',
            items: [
                { route: 'league.matches', label: 'Spiele', active: 'league.matches' },
                { route: 'league.table', label: 'Tabelle', active: 'league.table' },
                { route: 'statistics.index', label: 'Statistiken', active: 'statistics.*' },
                { route: 'teams.compare', label: 'Vergleich', active: 'teams.*' },
                { route: 'team-of-the-day.index', label: 'Team der Woche', active: 'team-of-the-day.*' },
                { route: 'friendlies.index', label: 'Freundschaft', active: 'friendlies.*' },
            ],
        },
        bg_markt: {
            label: 'Markt',
            items: [
                { route: 'contracts.index', label: 'Vertraege', active: 'contracts.*' },
                { route: 'clubs.index', label: 'Vereins-Suche', active: 'clubs.*' },
            ],
        },
    };
}

export function getAdminMenuGroups() {
    return {
        bg_main: {
            label: 'System',
            items: [
                { route: 'admin.dashboard', label: 'ACP Uebersicht', active: 'admin.dashboard' },
                { route: 'admin.modules.index', label: 'Module', active: 'admin.modules.*' },
            ],
        },
        bg_data: {
            label: 'Datenpflege',
            items: [
                { route: 'admin.competitions.index', label: 'Wettbewerbe', active: 'admin.competitions.*' },
                { route: 'admin.seasons.index', label: 'Saisons', active: 'admin.seasons.*' },
                { route: 'admin.clubs.index', label: 'Vereine', active: 'admin.clubs.*' },
                { route: 'admin.players.index', label: 'Spieler', active: 'admin.players.*' },
            ],
        },
        bg_engine: {
            label: 'Engine & Tools',
            items: [
                { route: 'admin.ticker-templates.index', label: 'Ticker Vorlagen', active: 'admin.ticker-templates.*' },
                { route: 'admin.match-engine.index', label: 'Match Engine', active: 'admin.match-engine.*' },
                { route: 'admin.monitoring.index', label: 'Monitoring & Debug', active: 'admin.monitoring.*' },
            ],
        },
    };
}
