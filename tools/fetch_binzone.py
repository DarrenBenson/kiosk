#!/usr/bin/env python3
"""
Fetches bin collection data from Binzone API
Usage: python3 fetch_binzone.py <UPRN> [COUNCIL]
Returns JSON array of collection entries
"""
import sys
import json
import requests

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
    except Exception:
        return None

def parse_binextra(html):
    """Parse binextra elements from HTML"""
    import re
    collections = []

    pattern = r'<div[^>]*class="[^"]*binextra[^"]*"[^>]*>(.*?)</div>'
    matches = re.findall(pattern, html, re.IGNORECASE | re.DOTALL)

    for content in matches:
        # Strip tags and decode
        text = re.sub(r'<[^>]+>', '', content).strip()

        # Parse format: "Friday 23 January -grey bin, small electrical items"
        match = re.match(r'^(.+?)\s*-\s*(.+)$', text, re.IGNORECASE)
        if match:
            date_str = match.group(1).strip()
            bins_str = match.group(2).strip().lower()

            # Remove holiday notice
            date_str = re.sub(r'Your usual collection day is different this week\s*', '', date_str, flags=re.IGNORECASE)

            collections.append({
                'date': date_str,
                'bins': bins_str
            })

    return collections

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'UPRN required'}))
        sys.exit(1)

    uprn = sys.argv[1]
    council = sys.argv[2] if len(sys.argv) > 2 else 'SOUTH'

    html = fetch_binzone(uprn, council)

    if html is None:
        print(json.dumps({'error': 'Failed to fetch data'}))
        sys.exit(1)

    collections = parse_binextra(html)
    print(json.dumps(collections))
