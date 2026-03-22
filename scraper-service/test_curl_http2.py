import subprocess

url = 'https://www.transfermarkt.com/rodri/transfers/spieler/357565'
user_agent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'

cmd = [
    'curl', '-s', '--http2',
    '-H', f'User-Agent: {user_agent}',
    url
]

print(f"Running command: {' '.join(cmd)}")
result = subprocess.run(cmd, capture_output=True, text=True)

print(f"Response Length: {len(result.stdout)}")
if len(result.stdout) > 0:
    print(f"Snippet: {result.stdout[:500]}")
    if "grid" in result.stdout.lower():
        print("FOUND 'grid' in output!")
    if "history" in result.stdout.lower():
        print("FOUND 'history' in output!")
else:
    print("Empty response.")
    print(f"Error: {result.stderr}")
