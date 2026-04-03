#!/usr/bin/env python3
"""
Frontend verification script using Playwright.
This script tests the main pages of the Symfony application.
"""

import os
import tempfile
import sys
from datetime import datetime

# Fix for missing HOME environment variable
os.environ["HOME"] = tempfile.gettempdir()

try:
    from playwright.sync_api import sync_playwright, expect
except ImportError:
    print("ERROR: Playwright not installed.")
    print("Install with: pip install playwright && playwright install chromium")
    sys.exit(1)


def test_homepage(page):
    """Test the homepage loads with Bootstrap styling."""
    print("\n[TEST] Homepage...")
    page.goto("http://localhost:8320/")
    
    # Check title
    expect(page).to_have_title("Welcome!")
    
    # Check Bootstrap navbar exists
    navbar = page.locator("nav.navbar")
    expect(navbar).to_be_visible()
    
    # Check hero section
    hero = page.locator("h1.display-5")
    expect(hero).to_contain_text("PDF Generator Service")
    
    # Take screenshot
    page.screenshot(path="screenshots/homepage.png")
    print("✓ Homepage loaded successfully")


def test_registration_page(page):
    """Test the registration page has all required fields."""
    print("\n[TEST] Registration Page...")
    page.goto("http://localhost:8320/register")
    
    # Check form fields exist
    expect(page.locator("#email")).to_be_visible()
    expect(page.locator("#firstname")).to_be_visible()
    expect(page.locator("#lastname")).to_be_visible()
    expect(page.locator("#dob")).to_be_visible()
    expect(page.locator("#password")).to_be_visible()
    
    # Take screenshot
    page.screenshot(path="screenshots/registration.png")
    print("✓ Registration page has all required fields")


def test_login_page(page):
    """Test the login page."""
    print("\n[TEST] Login Page...")
    page.goto("http://localhost:8320/login")
    
    # Check form fields
    expect(page.locator("#username")).to_be_visible()
    expect(page.locator("#password")).to_be_visible()
    
    # Take screenshot
    page.screenshot(path="screenshots/login.png")
    print("✓ Login page loaded successfully")


def test_registration_flow(page):
    """Test creating a new account."""
    print("\n[TEST] Registration Flow...")
    page.goto("http://localhost:8320/register")
    
    # Generate unique email
    timestamp = datetime.now().strftime("%Y%m%d%H%M%S")
    email = f"test_{timestamp}@example.com"
    
    # Fill form
    page.fill("#email", email)
    page.fill("#firstname", "Test")
    page.fill("#lastname", "User")
    page.fill("#dob", "2000-01-01")
    page.fill("#password", "password123")
    
    # Submit
    page.click("button[type='submit']")
    
    # Should redirect to login
    page.wait_for_url("**/login")
    
    # Check success message
    success_alert = page.locator(".alert-success")
    expect(success_alert).to_be_visible()
    
    print(f"✓ Account created successfully: {email}")
    return email


def test_login_flow(page, email="test@example.com", password="password123"):
    """Test logging in."""
    print("\n[TEST] Login Flow...")
    page.goto("http://localhost:8320/login")
    
    page.fill("#username", email)
    page.fill("#password", password)
    page.click("button[type='submit']")
    
    # Should redirect to homepage
    page.wait_for_url("**/")
    
    # Check if logged in (navbar should show logout)
    logout_link = page.locator("text=Logout")
    expect(logout_link).to_be_visible()
    
    print("✓ Login successful")


def test_dashboard_stats(page):
    """Test dashboard displays plan and usage stats."""
    print("\n[TEST] Dashboard Statistics...")
    
    # Should already be logged in from previous test
    page.goto("http://localhost:8320/")
    
    # Check for stats display
    stats_alert = page.locator(".alert-info")
    expect(stats_alert).to_be_visible()
    expect(stats_alert).to_contain_text("Current Plan:")
    expect(stats_alert).to_contain_text("Today's Usage:")
    
    # Take screenshot
    page.screenshot(path="screenshots/dashboard_stats.png")
    print("✓ Dashboard shows plan and usage statistics")


def test_subscription_page(page):
    """Test subscription page displays plans."""
    print("\n[TEST] Subscription Page...")
    page.goto("http://localhost:8320/subscription/change")
    
    # Check pricing cards exist
    pricing_cards = page.locator(".card")
    expect(pricing_cards.first).to_be_visible()
    
    # Check for Subscribe or Current Plan buttons
    buttons = page.locator("button, a.btn")
    expect(buttons.first).to_be_visible()
    
    # Take screenshot
    page.screenshot(path="screenshots/subscription.png")
    print("✓ Subscription page displays plans")


def test_pdf_generation_page(page):
    """Test PDF generation page with tabs."""
    print("\n[TEST] PDF Generation Page...")
    page.goto("http://localhost:8320/pdf/generate")
    
    # Check tabs exist
    url_tab = page.locator("#url-tab")
    file_tab = page.locator("#file-tab")
    wysiwyg_tab = page.locator("#wysiwyg-tab")
    
    expect(url_tab).to_be_visible()
    expect(file_tab).to_be_visible()
    expect(wysiwyg_tab).to_be_visible()
    
    # Take screenshot
    page.screenshot(path="screenshots/pdf_generation.png")
    print("✓ PDF generation page has all three input modes")


def test_history_page(page):
    """Test history page."""
    print("\n[TEST] History Page...")
    page.goto("http://localhost:8320/history")
    
    # Check page loads
    expect(page.locator("h1")).to_contain_text("Generation History")
    
    # Take screenshot
    page.screenshot(path="screenshots/history.png")
    print("✓ History page loaded")


def main():
    """Run all tests."""
    print("=" * 60)
    print("Frontend Verification Tests")
    print("=" * 60)
    
    # Create screenshots directory
    os.makedirs("screenshots", exist_ok=True)
    
    with sync_playwright() as p:
        # Launch browser
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
            viewport={"width": 1280, "height": 720}
        )
        page = context.new_page()
        
        try:
            # Run tests
            test_homepage(page)
            test_registration_page(page)
            test_login_page(page)
            
            # Create account and login
            email = test_registration_flow(page)
            test_login_flow(page, email, "password123")
            
            # Test authenticated pages
            test_dashboard_stats(page)
            test_subscription_page(page)
            test_pdf_generation_page(page)
            test_history_page(page)
            
            print("\n" + "=" * 60)
            print("✓ ALL TESTS PASSED")
            print("=" * 60)
            print(f"\nScreenshots saved in: {os.path.abspath('screenshots')}")
            
        except Exception as e:
            print(f"\n✗ TEST FAILED: {e}")
            page.screenshot(path="screenshots/error.png")
            raise
        finally:
            browser.close()


if __name__ == "__main__":
    main()
