import httpx

url = 'https://www.transfermarkt.com/rodri/transfers/spieler/357565'
user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'

headers = {
    'User-Agent': user_agent,
    'Accept-Language': 'en-GB,en;q=0.9',
}

with httpx.Client(http2=True) as client:
    response = client.get(url, headers=headers)
    with open('/tmp/tm_rodri.html', 'w', encoding='utf-8') as f:
        f.write(response.text)
    print(f"Saved {len(response.text)} characters to /tmp/tm_rodri.html")
