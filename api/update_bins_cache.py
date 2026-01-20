#!/usr/bin/env python3
"""
Updates the bin collection cache file.
Run via cron: 0 * * * * /usr/bin/python3 /path/to/update_bins_cache.py

Reads config from environment variables or config file.
"""
import os
import sys
import json
import requests
import re
from pathlib import Path

def get_config():
    """Get UPRN and council from environment or config file"""
    uprn = os.environ.get('BIN_UPRN', '')
    council = os.environ.get('BIN_COUNCIL', 'SOUTH')

    # Try to read from PHP config if env vars not set
    if not uprn:
        config_path = Path(__file__).parent.parent / 'config.local.php'
        if config_path.exists():
            content = config_path.read_text()
            uprn_match = re.search(r"define\s*\(\s*'BIN_UPRN'\s*,\s*'([^']+)'", content)
            council_match = re.search(r"define\s*\(\s*'BIN_COUNCIL'\s*,\s*'([^']+)'", content)
            if uprn_match:
                uprn = uprn_match.group(1)
            if council_match:
                council = council_match.group(1)

    return uprn, council

def fetch_binzone(uprn, council='SOUTH'):
    """Fetch bin collection data from Binzone API"""
    session = requests.Session()
    session.headers.update({
        'User-Agent': 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Language': 'en-GB,en;q=0.9',
    })

    cookies = {'SVBINZONE': f'{council}%3AUPRN%40{uprn}'}
    params = {'SOVA_TAG': council, 'ebd': '0'}

    try:
        r = session.get(
            'https://eform.southoxon.gov.uk/ebase/BINZONE_DESKTOP.eb',
            params=params,
            cookies=cookies,
            timeout=15
        )

        if r.status_code != 200 or '403 Forbidden' in r.text:
            return None

        return r.text
    except Exception as e:
        print(f"Error fetching data: {e}", file=sys.stderr)
        return None

def parse_binextra(html):
    """Parse binextra elements from HTML"""
    collections = []

    pattern = r'<div[^>]*class="[^"]*binextra[^"]*"[^>]*>(.*?)</div>'
    matches = re.findall(pattern, html, re.IGNORECASE | re.DOTALL)

    for content in matches:
        text = re.sub(r'<[^>]+>', '', content).strip()
        match = re.match(r'^(.+?)\s*-\s*(.+)$', text, re.IGNORECASE)
        if match:
            date_str = match.group(1).strip()
            bins_str = match.group(2).strip().lower()
            date_str = re.sub(r'Your usual collection day is different this week\s*', '', date_str, flags=re.IGNORECASE)

            collections.append({
                'date': date_str,
                'bins': bins_str
            })

    return collections

def main():
    uprn, council = get_config()

    if not uprn:
        print("Error: BIN_UPRN not configured", file=sys.stderr)
        sys.exit(1)

    html = fetch_binzone(uprn, council)

    if html is None:
        print("Error: Failed to fetch data", file=sys.stderr)
        sys.exit(1)

    collections = parse_binextra(html)

    if not collections:
        print("Error: No collections found", file=sys.stderr)
        sys.exit(1)

    # Write cache file
    cache_dir = Path(__file__).parent.parent / 'cache'
    cache_dir.mkdir(exist_ok=True)

    cache_file = cache_dir / 'bins_data.json'
    cache_file.write_text(json.dumps(collections, indent=2))

    print(f"Updated {cache_file} with {len(collections)} collections")

if __name__ == '__main__':
    main()
