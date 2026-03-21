import ScraperFC as sfc

def test_league(year, league):
    print(f"Testing {league} for {year}...")
    try:
        tm = sfc.Transfermarkt()
        df = tm.scrape_players(year, league)
        print(f"Success! Found {len(df)} players.")
        print(df.head())
        return True
    except Exception as e:
        print(f"Failed: {e}")
        return False

if __name__ == "__main__":
    # Test 1: User's mapping
    test_league("24/25", "Germany Bundesliga")
    # Test 2: Potential alternative
    test_league("24/25", "L1")
    # Test 3: EPL (for reference)
    test_league("24/25", "EPL")
