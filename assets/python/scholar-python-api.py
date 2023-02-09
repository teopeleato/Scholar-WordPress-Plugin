import sys

from scholar_scraper import scholar_scraper

# Check that there is a least one parameter
if len(sys.argv) < 2:
    print('Usage: python3 scholar-python-api.py <query>')
    sys.exit(1)

scholarIds = sys.argv[1:]
print(scholar_scraper.start_scraping(scholarIds))
