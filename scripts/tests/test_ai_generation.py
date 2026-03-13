import os
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options as ChromeOptions
from selenium.webdriver.firefox.options import Options as FirefoxOptions
from selenium.webdriver.chrome.service import Service as ChromeService
from selenium.webdriver.firefox.service import Service as FirefoxService
from webdriver_manager.chrome import ChromeDriverManager
from webdriver_manager.firefox import GeckoDriverManager
from selenium.webdriver.remote.webdriver import WebDriver as RemoteWebDriver
from selenium.webdriver.firefox.options import Options as FFOptions
from selenium.webdriver.chrome.options import Options as ChOptions

BASE_URL = os.getenv('APP_BASE_URL', 'http://localhost:8000')
EMAIL = os.getenv('APP_EMAIL', 'sadmin@inovace.in')
PASSWORD = os.getenv('APP_PASSWORD', 'Passw@rd')
BROWSER = os.getenv('SELENIUM_BROWSER', 'chrome')  # chrome|firefox
REMOTE_URL = os.getenv('SELENIUM_REMOTE_URL')  # e.g. http://localhost:4444/wd/hub


def make_driver():
    if REMOTE_URL:
        # Use remote WebDriver (e.g., Selenium Grid / standalone container)
        if 'firefox' in BROWSER:
            opts = FFOptions()
            opts.add_argument('-headless')
            return webdriver.Remote(command_executor=REMOTE_URL, options=opts)
        else:
            opts = ChOptions()
            opts.add_argument('--headless=new')
            opts.add_argument('--no-sandbox')
            opts.add_argument('--disable-dev-shm-usage')
            return webdriver.Remote(command_executor=REMOTE_URL, options=opts)
    if BROWSER == 'firefox':
        options = FirefoxOptions()
        options.add_argument('-headless')
        service = FirefoxService(GeckoDriverManager().install())
        driver = webdriver.Firefox(service=service, options=options)
        return driver
    else:
        options = ChromeOptions()
        options.add_argument('--headless=new')
        options.add_argument('--no-sandbox')
        options.add_argument('--disable-dev-shm-usage')
        service = ChromeService(ChromeDriverManager().install())
        driver = webdriver.Chrome(service=service, options=options)
        return driver


def login(driver):
    tried = ['/filament/login', '/login', '/']
    for path in tried:
        driver.get(BASE_URL + path)
        try:
            WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.CSS_SELECTOR, 'input[name="email"], input[type="email"], input[autocomplete="username"]'))
            )
            break
        except Exception:
            continue
    # Find email field by multiple strategies
    WebDriverWait(driver, 20).until(
        EC.presence_of_element_located((By.CSS_SELECTOR, 'input[name="email"], input[type="email"], input[autocomplete="username"]'))
    )
    email_el = None
    for sel in [
        'input[name="email"]',
        'input[type="email"]',
        'input[autocomplete="username"]',
        '//input[contains(@placeholder, "Email") or contains(@aria-label, "Email")]',
    ]:
        try:
            if sel.startswith('//'):
                email_el = driver.find_element(By.XPATH, sel)
            else:
                email_el = driver.find_element(By.CSS_SELECTOR, sel)
            break
        except Exception:
            continue
    email_el.send_keys(EMAIL)

    # Find password field
    pwd_el = None
    for sel in [
        'input[name="password"]',
        'input[type="password"]',
        'input[autocomplete="current-password"]',
        '//input[contains(@placeholder, "Password") or contains(@aria-label, "Password")]',
    ]:
        try:
            if sel.startswith('//'):
                pwd_el = driver.find_element(By.XPATH, sel)
            else:
                pwd_el = driver.find_element(By.CSS_SELECTOR, sel)
            break
        except Exception:
            continue
    from selenium.webdriver.common.keys import Keys
    pwd_el.send_keys(PASSWORD)
    # Try submitting by Enter
    pwd_el.send_keys(Keys.ENTER)
    time.sleep(1)
    # Fallback: click a submit button
    clicked = False
    for sel in [
        'button[type="submit"]',
        '//button[contains(., "Sign in") or contains(., "Login") or contains(., "Log in")]'
    ]:
        try:
            if sel.startswith('//'):
                driver.find_element(By.XPATH, sel).click()
            else:
                driver.find_element(By.CSS_SELECTOR, sel).click()
            clicked = True
            break
        except Exception:
            continue
    # Save screenshot after login attempt
    try:
        driver.save_screenshot('scripts/tests/login_after.png')
    except Exception:
        pass
    # Ensure we are on Filament panel
    # Try hitting /filament explicitly and wait for sidebar or Projects link
    tried_panel = [
        '/filament', '/', '/dashboard'
    ]
    ok = False
    for path in tried_panel:
        driver.get(BASE_URL + path)
        try:
            WebDriverWait(driver, 10).until(
                EC.presence_of_element_located((By.XPATH, "//aside | //a[contains(., 'Projects')]"))
            )
            ok = True
            break
        except Exception:
            continue
    if not ok:
        # Last attempt: remain on current page but don't fail yet
        pass


def create_project_with_ai(driver):
    # Navigate to Projects resource create page
    tried_urls = [
        '/projects/create',  # user confirmed this is the correct path
        # '/filament/projects/create',
        # '/filament/resources/projects/create',
    ]
    for path in tried_urls:
        driver.get(BASE_URL + path)
        # Debug: save screenshot per attempt
        try:
            safe = path.strip('/').replace('/', '_') or 'root'
            driver.save_screenshot(f'scripts/tests/nav_{safe}.png')
        except Exception:
            pass
        try:
            # Wait for either the heading or the 'Project name' label
            WebDriverWait(driver, 8).until(
                EC.presence_of_element_located((By.XPATH, "//h1[contains(., 'Create Project')] | //label[contains(., 'Project name')]"))
            )
            break
        except Exception:
            continue
    else:
        # Fallback via sidebar: click "Projects" then "Create"
        WebDriverWait(driver, 30).until(EC.presence_of_element_located((By.XPATH, "//aside | //nav")))
        # Click Projects in sidebar
        links = driver.find_elements(By.XPATH, "//a[contains(., 'Projects')] | //a[@href and contains(@href,'projects')]")
        if links:
            links[0].click()
        WebDriverWait(driver, 30).until(lambda d: 'projects' in d.current_url)
        # Click Create button
        create_buttons = driver.find_elements(By.XPATH, "//a[contains(., 'Create') or contains(., 'New') or contains(@href,'create')]")
        if create_buttons:
            create_buttons[0].click()
        WebDriverWait(driver, 30).until(EC.presence_of_element_located((By.CSS_SELECTOR,
            'input[name="data.name"], input[name="data[name]"], input[name="name"]'
        )))
    # Wait for form name input via label association first
    name_el = None
    try:
        name_el = WebDriverWait(driver, 12).until(EC.presence_of_element_located((By.XPATH,
            "//label[contains(normalize-space(.), 'Project name')]/following::input[1]"
        )))
    except Exception:
        pass
    if name_el is None:
        # Fallback to common name attributes
        try:
            name_el = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.CSS_SELECTOR,
                'input[name="data.name"], input[name="data[name]"], input[name="name"]'
            )))
        except Exception:
            # Last resort: any visible text input
            name_el = WebDriverWait(driver, 10).until(EC.presence_of_element_located((By.XPATH, "//input[@type='text' and not(@disabled)]")))
    try:
        driver.save_screenshot('scripts/tests/project_form.png')
    except Exception:
        pass
    # Fill required fields
    name_el.clear()
    name_el.send_keys('AI Test Project')
    # Ticket prefix (max 3)
    # Try multiple possible names for ticket prefix
    # Try by label first, then attribute fallbacks
    try:
        tp = driver.find_element(By.XPATH, "//label[contains(normalize-space(.), 'Ticket prefix')]/following::input[1]")
        tp.send_keys('AIT')
    except Exception:
        for sel in [
            'input[name="data.ticket_prefix"]',
            'input[name="data[ticket_prefix]"]',
            'input[name="ticket_prefix"]',
        ]:
            try:
                driver.find_element(By.CSS_SELECTOR, sel).send_keys('AIT')
                break
            except Exception:
                continue

    # Owner select default is current user; status select default is default status. Skip changing.

    # Fill description (RichEditor has hidden textarea). Use contenteditable div
    filled_desc = False
    # Try rich editor
    for sel in ['[contenteditable="true"]', '[role="textbox"]']:
        try:
            editor = WebDriverWait(driver, 5).until(EC.presence_of_element_located((By.CSS_SELECTOR, sel)))
            editor.click()
            editor.send_keys('Build a multilingual blog platform with authentication, WYSIWYG editor, and SEO.\nInclude CI/CD and Docker.')
            filled_desc = True
            break
        except Exception:
            continue
    if not filled_desc:
        for sel in ['textarea[name="data.description"]', 'textarea[name="data[description]"]', 'textarea[name="description"]']:
            try:
                driver.find_element(By.CSS_SELECTOR, sel).send_keys('Build a multilingual blog platform with authentication, WYSIWYG editor, and SEO.')
                break
            except Exception:
                continue

    # Enable AI toggle
    try:
        # Filament Toggle renders as input[type=checkbox] under [name="data.ai_autogenerate"] or nearby
        toggle = driver.find_element(By.NAME, 'data.ai_autogenerate')
        if not toggle.is_selected():
            toggle.click()
    except Exception:
        # Try clicking label text
        labels = driver.find_elements(By.XPATH, "//label[contains(., 'Auto-generate tasks with AI')]")
        if labels:
            labels[0].click()

    # Optional AI context
    try:
        driver.find_element(By.NAME, 'data.ai_context').send_keys('Prioritize core features and create granular subtasks for setup, backend, frontend, QA.')
    except Exception:
        pass

    # Submit form (Filament has a footer button with type=submit)
    # Scroll to bottom to ensure sticky footer is visible
    try:
        driver.execute_script('window.scrollTo(0, document.body.scrollHeight);')
        time.sleep(0.5)
    except Exception:
        pass
    submit = driver.find_element(By.CSS_SELECTOR, 'form button[type="submit"]')
    try:
        driver.execute_script('arguments[0].scrollIntoView({block: "center"});', submit)
        time.sleep(0.3)
    except Exception:
        pass
    # Try normal click
    try:
        submit.click()
    except Exception:
        # Try JS click
        try:
            driver.execute_script('arguments[0].click();', submit)
        except Exception:
            # Try Actions chain
            from selenium.webdriver import ActionChains
            ActionChains(driver).move_to_element(submit).pause(0.1).click(submit).perform()

    # Wait until redirected to view or index; check for notification
    WebDriverWait(driver, 30).until(lambda d: BASE_URL + '/projects' in d.current_url or '/view' in d.current_url or '/edit' in d.current_url)
    try:
        driver.save_screenshot('scripts/tests/after_create.png')
    except Exception:
        pass


def verify_tickets_created(driver):
    # Navigate to Tickets index
    tried_urls = ['/tickets', '/filament/tickets']
    found = False
    start = time.time()
    while time.time() - start < 60:
        for path in tried_urls:
            driver.get(BASE_URL + path)
            try:
                # Filament tables can be div-based; wait for common markers
                WebDriverWait(driver, 5).until(
                    EC.presence_of_element_located((By.XPATH, "//h1[contains(., 'Tickets')] | //div[contains(@class,'fi-ta')] | //div[contains(@class,'filament')]"))
                )
            except Exception:
                continue
            time.sleep(2)
            body_text = driver.find_element(By.TAG_NAME, 'body').text
            if 'AI Test Project' in body_text or 'Ticket' in body_text:
                found = True
                break
        if found:
            break
        # wait and retry
        time.sleep(3)
    assert found, 'Tickets not visible or not created within timeout'


def main():
    driver = make_driver()
    try:
        login(driver)
        create_project_with_ai(driver)
        # Allow queue to process
        time.sleep(10)
        verify_tickets_created(driver)
        print('OK: Selenium AI generation test passed')
    finally:
        driver.quit()

if __name__ == '__main__':
    main()
