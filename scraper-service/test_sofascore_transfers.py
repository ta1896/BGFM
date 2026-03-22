import requests

# Rodri Sofascore ID: 792949 (Guessing based on typical search, but let's use a real one from a previous run if possible)
# Actually, let's search for Rodri first.
search_url = "https://www.sofascore.com/api/v1/search/all?q=Rodri"
headers = {'User-Agent': 'Mozilla/5.0'}
resp = requests.get(search_url, headers=headers)
data = resp.json()
player_id = None
for res in data.get('results', []):
    if res.get('type') == 'player' and 'Man City' in res.get('entity', {}).get('team', {}).get('name', ''):
        player_id = res['entity']['id']
        break

if player_id:
    print(f"Testing Sofascore Player ID: {player_id}")
    # Try different potential endpoints for transfers
    endpoints = [
        f"https://www.sofascore.com/api/v1/player/{player_id}/transfers",
        f"https://www.sofascore.com/api/v1/player/{player_id}/transfer-history"
    ]
    for ep in endpoints:
        print(f"Checking endpoint: {ep}")
        r = requests.get(ep, headers=headers)
        print(f"Status: {r.status_code}")
        if r.status_code == 200:
            print("FOUND CONTENT!")
            print(r.text[:500])
else:
    print("Could not find Rodri on Sofascore API search.")
