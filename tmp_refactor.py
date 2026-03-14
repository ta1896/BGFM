import os
import re

directory = r'C:\Users\akden\Documents\NewGen\resources\js'

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    original_content = content

    # Replace specific hardcoded tailwind classes with the CSS variables that support theme switching!
    # These replacements ensure we keep the standard Tailwind utility approach but map them to the themes.
    
    # Backgrounds
    content = re.sub(r'\bbg-slate-950\b', 'bg-[var(--sim-shell-bg)]', content)
    content = re.sub(r'\bbg-slate-900/40\b', 'bg-[var(--bg-pillar)]/40', content)
    content = re.sub(r'\bbg-slate-900/50\b', 'bg-[var(--bg-pillar)]/50', content)
    content = re.sub(r'\bbg-slate-900/80\b', 'bg-[var(--bg-pillar)]/80', content)
    content = re.sub(r'\bbg-slate-900/90\b', 'bg-[var(--bg-pillar)]/90', content)
    content = re.sub(r'\bbg-slate-900\b', 'bg-[var(--bg-pillar)]', content)

    # Secondary Backgrounds
    content = re.sub(r'\bbg-slate-800/30\b', 'bg-[var(--bg-content)]/30', content)
    content = re.sub(r'\bbg-slate-800/50\b', 'bg-[var(--bg-content)]/50', content)
    content = re.sub(r'\bbg-slate-800/80\b', 'bg-[var(--bg-content)]/80', content)
    content = re.sub(r'\bbg-slate-800\b', 'bg-[var(--bg-content)]', content)
    
    # Borders
    content = re.sub(r'\bborder-slate-800/50\b', 'border-[var(--border-muted)]', content)
    content = re.sub(r'\bborder-slate-800\b', 'border-[var(--border-pillar)]', content)
    content = re.sub(r'\bborder-slate-700/50\b', 'border-[var(--border-muted)]', content)
    content = re.sub(r'\bborder-slate-700\b', 'border-[var(--border-pillar)]', content)

    # Text Colors that shouldn't be permanently white if a light theme is used
    # Wait, text-white is risky to replace globally without context (could be on buttons). Let's stick to text-slate-400 / 500
    content = re.sub(r'\btext-slate-400\b', 'text-[var(--text-muted)]', content)
    content = re.sub(r'\btext-slate-500\b', 'text-[var(--text-muted)]', content)

    # Optional: micro-animations! Let's find buttons and interactive cards
    # We add hover:scale-[1.02] active:scale-[0.98] to some basic buttons that miss it
    # This is tricky with regex, we leave animations to a separate step or just do some easy ones
    
    if content != original_content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

for root, dirs, files in os.walk(directory):
    for filename in files:
        if filename.endswith(".jsx") or filename.endswith(".js"):
            filepath = os.path.join(root, filename)
            process_file(filepath)

print("Done.")
