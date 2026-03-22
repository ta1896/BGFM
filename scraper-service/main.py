from fastapi import FastAPI, Query, HTTPException
import os
import re
import time
import requests
from bs4 import BeautifulSoup
from concurrent.futures import ThreadPoolExecutor

app = FastAPI()

@app.get("/")
def read_root():
    return {"message": "NewGen Scraper Service is running (Lightweight)"}

def get_soup(url):
    print(f"DEBUG: Fetching soup for: {url}")
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'}
    try:
        response = requests.get(url, headers=headers, timeout=20)
        print(f"DEBUG: Status Code: {response.status_code} for {url}")
        if response.status_code == 200:
            return BeautifulSoup(response.content, 'html.parser')
    except Exception as e:
        print(f"DEBUG: Error fetching soup: {e}")
    return None

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
        
        # Get player details for positions
        player_info_url = f"https://www.sofascore.com/api/v1/player/{player_id}"
        player_info_resp = requests.get(player_info_url, headers=headers, timeout=10)
        positions = []
        if player_info_resp.status_code == 200:
            player_info = player_info_resp.json()
            positions = player_info.get('player', {}).get('positionsDetailed', [])

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
            "creativity": current.get('creativity', 50),
            "positions": positions
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
            
            # Player info cell (Index 1 in Detailed view)
            player_td = tds[1] 
            name_cell = player_td.find('td', class_='hauptlink')
            if not name_cell: 
                # Fallback to current row find if structure varies
                name_cell = row.find('td', class_='hauptlink')
            
            if not name_cell: continue
            name_link = name_cell.find('a')
            if not name_link: continue
            player_name = name_link.get_text(strip=True)
            player_url = "https://www.transfermarkt.us" + name_link['href']
            
            # Position extraction (In the same player info cell table)
            position = ""
            alt_positions = ""
            pos_table = player_td.find('table', class_='inline-table')
            if pos_table:
                pos_rows = pos_table.find_all('tr')
                if len(pos_rows) > 1:
                    position = pos_rows[1].get_text(strip=True)
            
            # Clean position (e.g. "Right Winger" or "RW")
            if position:
                position = position.strip()
            
            if not position:
                # Fallback to old posrela logic
                pos_td = row.find('td', class_='posrela')
                if pos_td:
                    pos_element = pos_td.find('div', title=True) or pos_td.find('a', title=True)
                    if pos_element: alt_positions = pos_element['title']
                    pos_table_fallback = pos_td.find('table')
                    if pos_table_fallback:
                        pos_rows_f = pos_table_fallback.find_all('tr')
                        if len(pos_rows_f) > 1:
                            position = pos_rows_f[1].get_text(strip=True)

            # Date of Birth / Age (Index 2 in Detailed view)
            dob_cell = tds[2]
            dob_text = dob_cell.get_text(strip=True)
            # Format: "Sep 5, 2001 (23)"
            birthday = ""
            age = 0
            
            age_match = re.search(r'\((\d+)\)', dob_text)
            if age_match:
                age = int(age_match.group(1))
            
            # Extract date part
            # Support: "Sep 5, 2001" (English) or "05/09/2001" (Numerical)
            from datetime import datetime
            
            # Try English: "Sep 5, 2001"
            date_match_en = re.search(r'([A-Za-z]{3}\s\d+,\s\d{4})', dob_text)
            if date_match_en:
                try:
                    dt = datetime.strptime(date_match_en.group(1), '%b %d, %Y')
                    birthday = dt.strftime('%Y-%m-%d')
                except: pass
            
            # Try Numerical: "05/09/2001" or "05.09.2001"
            if not birthday:
                date_match_num = re.search(r'(\d{2}[/.]\d{2}[/.]\d{4})', dob_text)
                if date_match_num:
                    raw_date = date_match_num.group(1).replace('.', '/')
                    try:
                        dt = datetime.strptime(raw_date, '%d/%m/%Y')
                        birthday = dt.strftime('%Y-%m-%d')
                    except: pass

            # Nationality (Index 3 in Detailed view)
            nat_cell = tds[3]
            nat_imgs = nat_cell.find_all('img')
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
                "Birthday": birthday,
                "Age": age,
                "Market Value": val_int,
                "Nationality": nationality,
                "URL": player_url,
                "Club URL": club_url,
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

import httpx
from datetime import datetime

TM_API_BASES = [
    base.strip().rstrip("/")
    for base in os.getenv(
        "TM_API_BASES",
        os.getenv("TM_API_BASE", "https://tmapi-alpha.transfermarkt.technology"),
    ).split(",")
    if base.strip()
]
TM_API_TIMEOUT = float(os.getenv("TM_API_TIMEOUT", "20"))
TM_API_PREFERRED_CONTEXT = os.getenv("TM_API_PREFERRED_CONTEXT", "com").strip()
TM_API_PROXY = os.getenv("TM_API_PROXY", "").strip() or None
TM_LANGUAGE_BY_CONTEXT = {
    "at": "de-AT,de;q=0.9,en;q=0.8",
    "be": "nl-BE,nl;q=0.9,en;q=0.8",
    "br": "pt-BR,pt;q=0.9,en;q=0.8",
    "ch": "de-CH,de;q=0.9,en;q=0.8",
    "co": "es-CO,es;q=0.9,en;q=0.8",
    "com": "en-GB,en;q=0.9",
    "de": "de-DE,de;q=0.9,en;q=0.8",
    "es": "es-ES,es;q=0.9,en;q=0.8",
    "fr": "fr-FR,fr;q=0.9,en;q=0.8",
    "gr": "el-GR,el;q=0.9,en;q=0.8",
    "it": "it-IT,it;q=0.9,en;q=0.8",
    "mx": "es-MX,es;q=0.9,en;q=0.8",
    "nl": "nl-NL,nl;q=0.9,en;q=0.8",
    "pl": "pl-PL,pl;q=0.9,en;q=0.8",
    "pt": "pt-PT,pt;q=0.9,en;q=0.8",
    "ro": "ro-RO,ro;q=0.9,en;q=0.8",
    "tr": "tr-TR,tr;q=0.9,en;q=0.8",
    "uk": "en-GB,en;q=0.9",
    "us": "en-US,en;q=0.9",
    "world": "ru-RU,ru;q=0.9,en;q=0.8",
    "za": "en-ZA,en;q=0.9",
}
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'application/json',
    'Accept-Language': TM_LANGUAGE_BY_CONTEXT.get(TM_API_PREFERRED_CONTEXT, 'en-GB,en;q=0.9'),
    'Origin': 'https://www.transfermarkt.com',
    'Referer': 'https://www.transfermarkt.com/',
}

def build_tm_api_variants(base, endpoint):
    variants = [f"{base}{endpoint}"]
    if TM_API_PREFERRED_CONTEXT:
        separator = "&" if "?" in endpoint else "?"
        variants.insert(0, f"{base}{endpoint}{separator}_x_preferred_context={TM_API_PREFERRED_CONTEXT}")
    return variants

def fetch_tm_api_with_httpx(url, use_http2=True):
    client_kwargs = {
        "http2": use_http2,
        "follow_redirects": True,
        "timeout": TM_API_TIMEOUT,
    }
    if TM_API_PROXY:
        client_kwargs["proxy"] = TM_API_PROXY

    with httpx.Client(**client_kwargs) as client:
        return client.get(url, headers=HEADERS)

def fetch_tm_api_with_requests(url):
    proxies = None
    if TM_API_PROXY:
        proxies = {
            "http": TM_API_PROXY,
            "https": TM_API_PROXY,
        }

    return requests.get(
        url,
        headers=HEADERS,
        timeout=TM_API_TIMEOUT,
        proxies=proxies,
        allow_redirects=True,
    )

def parse_tm_api_response(response, url, transport_name):
    content_type = response.headers.get('content-type', '')
    if response.status_code != 200:
        print(f"DEBUG: TM API {transport_name} error {response.status_code} for {url}")
        return None

    if 'application/json' not in content_type.lower():
        print(f"DEBUG: TM API {transport_name} returned non-JSON content-type '{content_type}' for {url}")
        return None

    try:
        return response.json()
    except Exception as e:
        print(f"DEBUG: TM API {transport_name} JSON decode failed for {url}: {e}")
        return None

def get_tm_api_json(endpoint):
    for base in TM_API_BASES:
        for url in build_tm_api_variants(base, endpoint):
            print(f"DEBUG: Fetching TM API via HTTP/2: {url}")
            try:
                response = fetch_tm_api_with_httpx(url, use_http2=True)
                data = parse_tm_api_response(response, url, "HTTP/2")
                if data is not None:
                    return data
            except Exception as e:
                print(f"DEBUG: HTTP/2 error fetching TM API {url}: {e}")

            print(f"DEBUG: Fetching TM API via HTTP/1.1: {url}")
            try:
                response = fetch_tm_api_with_httpx(url, use_http2=False)
                data = parse_tm_api_response(response, url, "HTTP/1.1")
                if data is not None:
                    return data
            except Exception as e:
                print(f"DEBUG: HTTP/1.1 error fetching TM API {url}: {e}")

            print(f"DEBUG: Fetching TM API via requests: {url}")
            try:
                response = fetch_tm_api_with_requests(url)
                data = parse_tm_api_response(response, url, "requests")
                if data is not None:
                    return data
            except Exception as e:
                print(f"DEBUG: requests error fetching TM API {url}: {e}")

    return None

def get_entity_info(entity_type, entity_id, cache):
    if not entity_id or entity_id == "0":
        return {"name": "Unknown", "logo": None}
    
    cache_key = f"{entity_type}_{entity_id}"
    if cache_key in cache:
        return cache[cache_key]
    
    endpoint = f"/{entity_type}/{entity_id}"
    data = get_tm_api_json(endpoint)
    info = {"name": "Unknown", "logo": None}
    if data and 'data' in data:
        entity_data = data['data']
        info["name"] = entity_data.get('clubName') or entity_data.get('competitionName') or entity_data.get('name') or "Unknown"
        info["logo"] = entity_data.get('crestUrl') or entity_data.get('logoUrl')
        cache[cache_key] = info
    
    return info

def get_player_transfer_history(url: str):
    """Scrapes player transfer history from Transfermarkt using its JSON API."""
    # Extract player ID from URL: .../spieler/357565
    match = re.search(r'spieler/(\d+)', url)
    if not match:
        print(f"DEBUG: Could not extract player ID from {url}")
        return []
    
    player_id = match.group(1)
    endpoint = f"/transfer/history/player/{player_id}"
    api_data = get_tm_api_json(endpoint)
    
    if not api_data or 'data' not in api_data:
        print(f"DEBUG: No API data found for player {player_id}")
        return []

    history = []
    terminated = api_data['data'].get('history', {}).get('terminated', [])
    
    # In-memory cache for club/competition info to reduce API calls
    entity_cache = {}

    print(f"DEBUG: Processing {len(terminated)} transfers from API")
    for transfer in terminated:
        details = transfer.get('details', {})
        source = transfer.get('transferSource', {})
        dest = transfer.get('transferDestination', {})
        fee_info = details.get('fee', {})
        mv_info = details.get('marketValue', {})
        
        # Format date: 2019-07-04T00:00:00+02:00 -> Jul 4, 2019
        raw_date = details.get('date', '')
        formatted_date = "?"
        if raw_date:
            try:
                # Handle ISO format with offset
                dt = datetime.fromisoformat(raw_date.replace('Z', '+00:00'))
                formatted_date = dt.strftime('%b %d, %Y')
            except:
                formatted_date = raw_date

        # Resolve Club Names and Logos
        left_info = get_entity_info("club", source.get('clubId'), entity_cache)
        joined_info = get_entity_info("club", dest.get('clubId'), entity_cache)

        data = {
            'season': details.get('season', {}).get('display', '?'),
            'transfer_date': formatted_date,
            'left_club_name': left_info["name"],
            'left_club_logo': left_info["logo"],
            'left_club_tm_id': source.get('clubId'),
            'joined_club_name': joined_info["name"],
            'joined_club_logo': joined_info["logo"],
            'joined_club_tm_id': dest.get('clubId'),
            'market_value': mv_info.get('compact', {}).get('content', '?') + mv_info.get('compact', {}).get('suffix', ''),
            'fee': fee_info.get('compact', {}).get('content', '?') + fee_info.get('compact', {}).get('suffix', ''),
        }

        
        # Clean up market value / fee display (remove leading ?)
        if data['market_value'].startswith('?'): data['market_value'] = data['market_value'][1:] or '?'
        if data['fee'].startswith('?'): data['fee'] = data['fee'][1:] or '?'
        
        # Add currency symbol if possible (hardcoded to € for TM usually if content starts with digit)
        if data['market_value'] != '?' and data['market_value'][0].isdigit():
            data['market_value'] = "€" + data['market_value']
        if data['fee'] != '?' and data['fee'][0].isdigit():
            data['fee'] = "€" + data['fee']

        data['is_loan'] = 'Leihe' in transfer.get('typeDetails', {}).get('feeDescription', '') or 'Loan' in transfer.get('typeDetails', {}).get('feeDescription', '')

        history.append(data)

    return history

@app.get("/transfermarkt/player-history")
def read_player_history(url: str):
    return get_player_transfer_history(url)

@app.get("/transfermarkt/player-history-by-id")
def read_player_history_by_id(id: str):
    url = f"https://www.transfermarkt.us/profil/spieler/{id}"
    return get_player_transfer_history(url)

@app.get("/transfermarkt/tm-api-health")
def tm_api_health():
    diagnostics = []

    for base in TM_API_BASES:
        for endpoint in ["/club/1", "/transfer/history/player/357565"]:
            for url in build_tm_api_variants(base, endpoint):
                attempt = {
                    "endpoint": endpoint,
                    "url": url,
                    "http2": None,
                    "http1": None,
                    "requests": None,
                }

                try:
                    response = fetch_tm_api_with_httpx(url, use_http2=True)
                    attempt["http2"] = {
                        "status": response.status_code,
                        "content_type": response.headers.get('content-type'),
                    }
                except Exception as e:
                    attempt["http2"] = {"error": str(e)}

                try:
                    response = fetch_tm_api_with_httpx(url, use_http2=False)
                    attempt["http1"] = {
                        "status": response.status_code,
                        "content_type": response.headers.get('content-type'),
                    }
                except Exception as e:
                    attempt["http1"] = {"error": str(e)}

                try:
                    response = fetch_tm_api_with_requests(url)
                    attempt["requests"] = {
                        "status": response.status_code,
                        "content_type": response.headers.get('content-type'),
                    }
                except Exception as e:
                    attempt["requests"] = {"error": str(e)}

                diagnostics.append(attempt)

    return {
        "bases": TM_API_BASES,
        "preferred_context": TM_API_PREFERRED_CONTEXT or None,
        "accept_language": HEADERS.get("Accept-Language"),
        "proxy_configured": bool(TM_API_PROXY),
        "diagnostics": diagnostics,
    }
