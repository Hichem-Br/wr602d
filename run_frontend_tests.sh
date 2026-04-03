#!/bin/bash
# Script to run frontend tests in Docker container

echo "Installing Playwright..."
docker exec -w /var/www symfony-web-v2 bash -c "pip3 install playwright && playwright install chromium"

echo ""
echo "Running frontend tests..."
docker exec -w /var/www symfony-web-v2 python3 test_frontend.py

echo ""
echo "Done! Check the screenshots/ folder for visual verification."
