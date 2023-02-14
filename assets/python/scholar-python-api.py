import sys

from scholar_scraper import scholar_scraper

# Check that there is at least one parameter
if len(sys.argv) < 2:
    print('Usage: python3 scholar-python-api.py [maxThreads={number}] <query>')
    sys.exit(1)

arguments = sys.argv[1:]

# Check if there is a value in the array that matches "maxThreads=.*"
# If there is none, we set the default value to 10
maxThreads = None
scholarIds = []

# Check if there is a value in the array that matches "maxThreads=[0-9]+"
for argument in arguments:
    if argument.startswith('maxThreads='):
        threadParameter = argument.split('=')[1]
        # Check if the value is a number and > 0
        if threadParameter.isdigit() and int(threadParameter) > 0:
            maxThreads = int(threadParameter)
    else:
        scholarIds.append(argument)

# Check that there is at least one scholarId
if len(scholarIds) == 0:
    print('Usage: python3 scholar-python-api.py [maxThreads={number}] <query>')
    sys.exit(1)

print(scholar_scraper.start_scraping(scholarIds, maxThreads))
