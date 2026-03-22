import re

def parse_market_value(market_value_text):
    print(f"Parsing: '{market_value_text}'")
    val_int = 0
    # Find the number, allowing for dots and commas
    val_match = re.search(r'(\d+[.,]?\d*)', market_value_text)
    if val_match:
        val_str = val_match.group(1).replace(',', '.')
        val_float = float(val_str)
        text_lower = market_value_text.lower()
        if 'bn' in text_lower:
            val_int = int(val_float * 1000000000)
        elif 'm' in text_lower:
            val_int = int(val_float * 1000000)
        elif 'k' in text_lower:
            val_int = int(val_float * 1000)
        else:
            val_int = int(val_float)
    return val_int

test_cases = [
    "€25.00m",
    "€1.31bn",
    "€600k",
    "$25.00m",
    "25,00m",
    "€ 25.00 m",
    "-",
    "0",
    "€0.20m",
    "€200k"
]

for tc in test_cases:
    print(f"'{tc}' -> {parse_market_value(tc)}")
