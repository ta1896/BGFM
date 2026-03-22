import httpx
import json

club_id = 13 # Atlético Madrid
url = f'https://tmapi-alpha.transfermarkt.technology/club/{club_id}'
user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'

headers = {
    'User-Agent': user_agent,
}

with httpx.Client(http2=True) as client:
    response = client.get(url, headers=headers)
    if response.status_code == 200:
        print(json.dumps(response.json(), indent=2))
