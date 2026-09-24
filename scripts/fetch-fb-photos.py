"""
Downloads the Facebook photos listed in the CSV batches the browser agent produces.

    python scripts/fetch-fb-photos.py "C:/Users/AzzaB/Downloads/marshmallow-image-urls-*.csv"

Facebook's image links expire after about two days, so run this as soon as a batch arrives.
Images are saved as storage/client-photos/facebook/<fbid>.jpg and every row is appended to
storage/client-photos/facebook/manifest.csv, which is what places each photo on the site later.
Already-downloaded fbids are skipped, so re-running is safe.
"""

import csv
import glob
import io
import os
import sys
import urllib.request
from concurrent.futures import ThreadPoolExecutor

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(ROOT, 'storage', 'client-photos', 'facebook')
MANIFEST = os.path.join(OUT, 'manifest.csv')
FIELDS = ['fbid', 'file', 'width', 'height', 'source_album_or_post', 'post_date',
          'event_or_activity', 'class', 'priority', 'is_marketing_frame', 'photo_permalink']
HEADERS = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0 Safari/537.36'}


def existing_manifest():
    if not os.path.exists(MANIFEST):
        return {}
    with io.open(MANIFEST, encoding='utf-8-sig', newline='') as fh:
        return {row['fbid']: row for row in csv.DictReader(fh)}


def download(row):
    fbid = (row.get('fbid') or '').strip()
    url = (row.get('image_url') or '').strip()
    if not fbid or not url:
        return fbid, 'no url', None

    path = os.path.join(OUT, fbid + '.jpg')
    if os.path.exists(path) and os.path.getsize(path) > 1024:
        return fbid, 'already had it', path

    try:
        data = urllib.request.urlopen(urllib.request.Request(url, headers=HEADERS), timeout=60).read()
    except Exception as error:  # expired link, network hiccup, removed photo
        return fbid, 'failed: {}'.format(error), None

    if len(data) < 1024:
        return fbid, 'failed: file too small', None

    with open(path, 'wb') as fh:
        fh.write(data)
    return fbid, 'downloaded', path


def main(patterns):
    os.makedirs(OUT, exist_ok=True)
    manifest = existing_manifest()

    rows = []
    seen = set()
    for pattern in patterns:
        for file in sorted(glob.glob(pattern)):
            with io.open(file, encoding='utf-8-sig', newline='') as fh:
                for row in csv.DictReader(fh):
                    fbid = (row.get('fbid') or '').strip()
                    if fbid and fbid not in seen:
                        seen.add(fbid)
                        rows.append(row)
            print('read', os.path.basename(file))

    todo = [r for r in rows if r['fbid'] not in manifest or not os.path.exists(os.path.join(OUT, r['fbid'] + '.jpg'))]
    print('{} rows, {} already downloaded, fetching {}'.format(len(rows), len(rows) - len(todo), len(todo)))

    failed = []
    with ThreadPoolExecutor(max_workers=8) as pool:
        for i, (fbid, status, path) in enumerate(pool.map(download, todo), 1):
            if path:
                row = next(r for r in todo if r['fbid'] == fbid)
                manifest[fbid] = {
                    'fbid': fbid,
                    'file': os.path.basename(path),
                    'width': row.get('width', ''),
                    'height': row.get('height', ''),
                    'source_album_or_post': row.get('source_album_or_post', ''),
                    'post_date': row.get('post_date', ''),
                    'event_or_activity': row.get('event_or_activity', ''),
                    'class': row.get('class', ''),
                    'priority': row.get('priority', ''),
                    'is_marketing_frame': row.get('is_marketing_frame', ''),
                    'photo_permalink': row.get('photo_permalink', ''),
                }
            else:
                failed.append((fbid, status))
            if i % 25 == 0:
                print('  {}/{}'.format(i, len(todo)))

    with io.open(MANIFEST, 'w', encoding='utf-8', newline='') as fh:
        writer = csv.DictWriter(fh, fieldnames=FIELDS)
        writer.writeheader()
        for row in manifest.values():
            writer.writerow(row)

    print('\nsaved {} photos in total'.format(len(manifest)))
    if failed:
        print('{} failed (expired links can be re-exported from the photo_permalink):'.format(len(failed)))
        for fbid, status in failed[:10]:
            print('  ', fbid, status)


if __name__ == '__main__':
    main(sys.argv[1:] or ['C:/Users/AzzaB/Downloads/marshmallow-image-urls-*.csv'])
