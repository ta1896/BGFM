from fastapi import FastAPI, Query, HTTPException
import re
import time
import requests
from bs4 import BeautifulSoup
from concurrent.futures import ThreadPoolExecutor

app = FastAPI()

@app.get("/")
def read_root():
    return {"message": "NewGen Scraper Service is running (Lightweight)"}

def get_league_club_links(league_id, year):
    # Map L1 -> bundesliga, etc.
    # ScraperFC uses abbreviations, but we can just use the ID in the URL for TM
    # Example: https://www.transfermarkt.us/bundesliga/startseite/wettbewerb/L1/plus/?saison_id=2024
    
    # Extract numeric year from "24/25"
    year_num = "20" + year.split('/')[0]
    url = f"https://www.transfermarkt.us/league/startseite/wettbewerb/{league_id}/plus/?saison_id={year_num}"
    
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'}
    try:
        response = requests.get(url, headers=headers, timeout=20)
        if response.status_code != 200:
            print(f"Failed to fetch league page: {response.status_code}")
            return []
            
        soup = BeautifulSoup(response.content, 'html.parser')
        # Find the standings table or club list
        table = soup.find('table', class_='items')
        if not table:
            # Fallback for some layouts
            table = soup.find('div', id='yw1')
            
        if not table:
            return []
            
        links = []
        # Club links are usually in the first column or hauptlink cells
        for a in table.find_all('a', href=re.compile(r'/startseite/verein/')):
            link = "https://www.transfermarkt.us" + a['href']
            if link not in links:
                links.append(link)
                
        return links
    except Exception as e:
        print(f"Error fetching club links: {e}")
        return []

def get_sofascore_data(player_name, club_name):
    print(f"Searching Sofascore for: {player_name} ({club_name})")
    search_url = f"https://www.sofascore.com/api/v1/search/all?q={player_name}"
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'}
    try:
        resp = requests.get(search_url, headers=headers, timeout=10)
        if resp.status_code != 200: return None
        
        data = resp.json()
        results = data.get('results', [])
        
        player_id = None
        for res in results:
            if res.get('type') == 'player':
                entity = res.get('entity', {})
                # Check for name match or similar
                res_club = entity.get('team', {}).get('name', '').lower()
                # Fuzzy club check
                if club_name.lower() in res_club or res_club in club_name.lower():
                    player_id = entity.get('id')
                    break
        
        if not player_id:
            # Final fallback: if only one player result, take it
            player_results = [r for r in results if r.get('type') == 'player']
            if len(player_results) == 1:
                player_id = player_results[0].get('entity', {}).get('id')

        if not player_id: return None
        
        # Get attributes
        attr_url = f"https://www.sofascore.com/api/v1/player/{player_id}/attribute-overviews"
        attr_resp = requests.get(attr_url, headers=headers, timeout=10)
        if attr_resp.status_code != 200: return None
        
        attr_data = attr_resp.json()
        overviews = attr_data.get('playerAttributeOverviews', [])
        if not overviews: return None
        
        # Take the most recent one (yearShift: 0)
        current = overviews[0] 
        return {
            "sofascore_id": str(player_id),
            "attacking": current.get('attacking', 50),
            "technical": current.get('technical', 50),
            "tactical": current.get('tactical', 50),
            "defending": current.get('defending', 50),
            "creativity": current.get('creativity', 50)
        }
    except Exception as e:
        print(f"Sofascore error for {player_name}: {e}")
        return None

def get_squad_players(club_url, year):
    match = re.search(r'verein/(\d+)', club_url)
    if not match: return []
    
    club_id = match.group(1)
    year_num = "20" + year.split('/')[0]
    squad_url = club_url.replace('/startseite/', '/kader/') + f"/plus/1?saison_id={year_num}"
    
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'}
    try:
        response = requests.get(squad_url, headers=headers, timeout=20)
        if response.status_code != 200: return []
            
        soup = BeautifulSoup(response.content, 'html.parser')
        table = soup.find('table', class_='items')
        if not table: return []
            
        players = []
        rows = table.find_all('tr', class_=['odd', 'even'])
        
        club_name_tag = soup.find('h1', class_='data-header__headline-wrapper')
        club_full_name = club_name_tag.get_text(strip=True) if club_name_tag else "Unknown Club"
        club_name = re.sub(r'\d{2}/\d{2}$', '', club_full_name).strip()
        
        for row in rows:
            tds = row.find_all('td')
            if len(tds) < 10: continue
            
            # Player name and URL
            name_cell = row.find('td', class_='hauptlink')
            if not name_cell: continue
            name_link = name_cell.find('a')
            if not name_link: continue
            player_name = name_link.get_text(strip=True)
            player_url = "https://www.transfermarkt.us" + name_link['href']
            
            # photo_url = "" # Removed per user request
            
            # Position and Alternative Positions
            # Structure: <td> <table> <tr> <td class="posrela"> ... <div title="..."> ... </td> </tr> </table> </td>
            # Or many variants. The subagent says the title is in the position element.
            pos_td = row.find('td', class_='posrela')
            position = ""
            alt_positions = ""
            if pos_td:
                # The position string is usually in a div or text within posrela
                pos_element = pos_td.find('div', title=True) or pos_td.find('a', title=True)
                if pos_element:
                    alt_positions = pos_element['title']
                
                # Main position text
                pos_table = pos_td.find('table')
                if pos_table:
                    pos_rows = pos_table.find_all('tr')
                    if len(pos_rows) > 1:
                        position = pos_rows[1].get_text(strip=True)
            
            # Age (Index 5)
            age_cell = tds[5]
            age_match = re.search(r'\((\d+)\)', age_cell.get_text(strip=True))
            age = int(age_match.group(1)) if age_match else 0
            
            # Nationality (Index 6)
            nat_cell = tds[6]
            nat_imgs = nat_cell.find_all('img')
            # Extract title from any image in the nationality cell
            nationalities = [img['title'] for img in nat_imgs if img.has_attr('title')]
            nationality = nationalities[0] if nationalities else "Unknown"
            
            # Market Value (class rechts hauptlink)
            value_cell = row.find('td', class_='rechts hauptlink')
            market_value_text = value_cell.get_text(strip=True) if value_cell else "0"
            
            # Parse market value
            val_int = 0
            # Support both dots and commas, then normalize to dot
            val_match = re.search(r'(\d+[.,]?\d*)', market_value_text)
            if val_match:
                val_str = val_match.group(1).replace(',', '.')
                val_float = float(val_str)
                text_lower = market_value_text.lower()
                if 'bn' in text_lower or 'mrd' in text_lower:
                    val_int = int(val_float * 1000000000)
                elif 'm' in text_lower:
                    val_int = int(val_float * 1000000)
                elif 'k' in text_lower or 'th.' in text_lower:
                    val_int = int(val_float * 1000)
                else:
                    val_int = int(val_float)
            
            # Debug log for value
            if val_int > 0:
                print(f"Scraped {player_name}: {market_value_text} -> {val_int}")
                
            # Store in list, enrich later
            players.append({
                "Player": player_name,
                "Club": club_name,
                "Club ID": club_id,
                "Position": position,
                "Alternative Positions": alt_positions,
                "Age": age,
                "Market Value": val_int,
                "Nationality": nationality,
                "URL": player_url,
                "Photo URL": None
            })
        
        # Enrich with Sofascore data in parallel
        def enrich_player(p):
            p["Sofascore"] = get_sofascore_data(p["Player"], p["Club"])
            return p

        with ThreadPoolExecutor(max_workers=10) as executor:
            players = list(executor.map(enrich_player, players))
            
        return players
    except Exception as e:
        print(f"Error scraping squad {squad_url}: {e}")
        return []

@app.get("/transfermarkt/scrape-league")
def scrape_league_players(
    year: str = Query(..., description="Season year (e.g. 23/24)"),
    league: str = Query(..., description="League name or ID (e.g. L1)")
):
    print(f"Lightweight Scraping league {league} for {year}...")
    try:
        club_links = get_league_club_links(league, year)
        
        if not club_links:
            print(f"No club links found for {league} {year}")
            return {"count": 0, "players": [], "year": year, "league": league}

        all_players = []
        for i, club_url in enumerate(club_links):
            print(f"[{i+1}/{len(club_links)}] Scraping: {club_url}")
            club_players = get_squad_players(club_url, year)
            all_players.extend(club_players)
            time.sleep(0.5)
            
        print(f"Successfully scraped {len(all_players)} players.")
        return {
            "count": len(all_players),
            "players": all_players,
            "year": year,
            "league": league
        }
    except Exception as e:
        print(f"Scraper error: {str(e)}")
        raise HTTPException(status_code=500, detail=str(e))
