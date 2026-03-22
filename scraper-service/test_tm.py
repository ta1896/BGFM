from main import get_player_transfer_history

url = 'https://www.transfermarkt.us/andrew-robertson/profil/spieler/234803'
print(f"Testing URL: {url}")
history = get_player_transfer_history(url)
print(f"Result (count: {len(history)}):")
for item in history[:5]:
    print(item)
