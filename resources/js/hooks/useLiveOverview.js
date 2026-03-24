import { useEffect, useState } from 'react';
import { subscribeToLiveOverview } from '@/lib/liveOverviewBus';

export default function useLiveOverview({ initialLiveMatches = [], initialOnlineManagers = [], initialOnlineWindowMinutes = 5, enabled = true }) {
    const [liveMatches, setLiveMatches] = useState(initialLiveMatches);
    const [onlineManagers, setOnlineManagers] = useState(initialOnlineManagers);
    const [onlineWindowMinutes, setOnlineWindowMinutes] = useState(initialOnlineWindowMinutes);

    useEffect(() => {
        setLiveMatches(initialLiveMatches);
    }, [initialLiveMatches]);

    useEffect(() => {
        setOnlineManagers(initialOnlineManagers);
    }, [initialOnlineManagers]);

    useEffect(() => {
        setOnlineWindowMinutes(initialOnlineWindowMinutes);
    }, [initialOnlineWindowMinutes]);

    useEffect(() => {
        if (!enabled) {
            return undefined;
        }

        return subscribeToLiveOverview((event) => {
            setLiveMatches(Array.isArray(event.liveMatches) ? event.liveMatches : []);
            setOnlineManagers(Array.isArray(event.onlineManagers) ? event.onlineManagers : []);
            setOnlineWindowMinutes(Number(event.onlineWindowMinutes || 5));
        });
    }, [enabled]);

    return {
        liveMatches,
        liveMatchesCount: liveMatches.length,
        onlineManagers,
        onlineManagersCount: onlineManagers.length,
        onlineWindowMinutes,
    };
}
