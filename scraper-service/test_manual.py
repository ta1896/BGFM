import requests
from bs4 import BeautifulSoup
import time

def test_squad_scrape(club_id, season):
    url = f"https://www.transfermarkt.us/borussia-dortmund/kader/verein/{club_id}/plus/1/galerie/0?saison_id={season}"
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'}
    
    start = time.time()
    response = requests.get(url, headers=headers)
    print(f"Time taken for request: {time.time() - start:.2f}s")
    
    soup = BeautifulSoup(response.content, 'html.parser')
    # Find the players table
    table = soup.find('table', class_='items')
    if not table:
        print("Table not found!")
        return
    
    rows = table.find_all('tr', class_=['odd', 'even'])
    print(f"Found {len(rows)} players in squad.")
    for row in rows[:5]:
        name_cell = row.find('td', class_='hauptlink')
        if name_cell:
            print(f"- {name_cell.text.strip()}")

if __name__ == "__main__":
    test_squad_scrape(16, 2024)
