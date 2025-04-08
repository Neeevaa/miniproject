# Improved and Fixed Version
import pytest
import time
import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, ElementNotInteractableException

class TestFoodordering:
    def setup_method(self, method):
        os.environ['http_proxy'] = ''
        os.environ['https_proxy'] = ''
        os.environ['HTTP_PROXY'] = ''
        os.environ['HTTPS_PROXY'] = ''
        os.environ['no_proxy'] = '*'
        os.environ['NO_PROXY'] = '*'
        
        chrome_options = Options()
        chrome_options.add_argument("--no-proxy-server")
        chrome_options.add_argument("--proxy-bypass-list=*")
        chrome_options.add_experimental_option('excludeSwitches', ['enable-logging'])
        chrome_options.add_experimental_option('useAutomationExtension', False)
        chrome_options.add_argument("--proxy-server='direct://'")
        chrome_options.add_argument("--proxy-bypass-list=*")
        
        service = Service()
        self.driver = webdriver.Chrome(options=chrome_options, service=service)
        self.driver.implicitly_wait(10)
        self.vars = {}

    def teardown_method(self, method):
        self.driver.quit()

    def wait_for_window(self, timeout=2):
        time.sleep(round(timeout / 1000))
        wh_now = self.driver.window_handles
        wh_then = self.vars["window_handles"]
        if len(wh_now) > len(wh_then):
            return set(wh_now).difference(set(wh_then)).pop()

    def hide_sticky_headers(self):
        # Hides potential sticky headers that might block elements
        js_hide = """
        const headers = document.querySelectorAll('header, .sticky-top, .fixed-top, .navbar');
        headers.forEach(el => el.style.display = 'none');
        """
        self.driver.execute_script(js_hide)

    def safe_click(self, by, selector, timeout=10):
        try:
            WebDriverWait(self.driver, timeout).until(
                EC.element_to_be_clickable((by, selector))
            )
            element = self.driver.find_element(by, selector)

            # Scroll into view and delay
            self.driver.execute_script("arguments[0].scrollIntoView(true);", element)
            time.sleep(1)  # Allow animation or transition

            # Hide headers that might block the click
            self.hide_sticky_headers()

            # Final check and click
            if element.is_displayed() and element.is_enabled():
                element.click()
            else:
                raise ElementNotInteractableException("Element not interactable even after scroll.")
        except Exception as e:
            print(f"Fallback to JS click due to: {e}")
            element = self.driver.find_element(by, selector)
            self.driver.execute_script("arguments[0].click();", element)

    def safe_send_keys(self, by, selector, text, timeout=10):
        element = WebDriverWait(self.driver, timeout).until(
            EC.element_to_be_clickable((by, selector))
        )
        element.clear()
        element.send_keys(text)

    def test_foodordering(self):
        self.driver.get("http://localhost/aromiq/menu.php")
        self.driver.set_window_size(1382, 736)

        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.CSS_SELECTOR, ".active"))
        )

        elements = self.driver.find_elements(By.CSS_SELECTOR, ".active .mt-n1")
        print(f"Found {len(elements)} elements with '.active .mt-n1'")
        for el in elements:
            print(f"Visible: {el.is_displayed()}, Enabled: {el.is_enabled()}")

        self.safe_click(By.CSS_SELECTOR, ".active .mt-n1")
        self.safe_click(By.CSS_SELECTOR, "#tab-2 .col-lg-6:nth-child(7) .w-100")
        self.safe_click(By.CSS_SELECTOR, ".add-to-platter")
        self.safe_click(By.CSS_SELECTOR, ".tray-icon")
        self.safe_click(By.CSS_SELECTOR, ".py-2")

        self.safe_send_keys(By.ID, "name", "neeva sunish mathew")
        self.safe_send_keys(By.ID, "email", "nevonj2am@gmail.com")
        self.safe_send_keys(By.ID, "mobile", "08111962758")

        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.ID, "table"))
        )
        dropdown = self.driver.find_element(By.ID, "table")
        self.driver.execute_script("arguments[0].scrollIntoView(true);", dropdown)
        dropdown.click()
        option = WebDriverWait(self.driver, 10).until(
            EC.element_to_be_clickable((By.XPATH, "//option[. = 'Table 10']"))
        )
        option.click()

        WebDriverWait(self.driver, 10).until(
            EC.presence_of_element_located((By.ID, "payment"))
        )
        payment_dropdown = self.driver.find_element(By.ID, "payment")
        self.driver.execute_script("arguments[0].scrollIntoView(true);", payment_dropdown)
        payment_dropdown.click()
        payment_option = WebDriverWait(self.driver, 10).until(
            EC.element_to_be_clickable((By.XPATH, "//option[. = 'UPI']"))
        )
        payment_option.click()

        self.safe_click(By.CSS_SELECTOR, ".submit-btn")
        self.safe_send_keys(By.ID, "mobile", "8111962758")
        self.safe_click(By.CSS_SELECTOR, ".submit-btn")
        self.safe_click(By.ID, "pay-button")

        WebDriverWait(self.driver, 10).until(
            EC.frame_to_be_available_and_switch_to_it(0)
        )
        self.safe_send_keys(By.NAME, "card.number", "4111 1111 1111 1111")
        self.safe_send_keys(By.NAME, "card.expiry", "02 / 26")
        self.safe_send_keys(By.NAME, "card.cvv", "123")
        self.safe_click(By.NAME, "save")
        self.safe_click(By.NAME, "button")

        self.vars["window_handles"] = self.driver.window_handles
        time.sleep(1)

        try:
            self.safe_click(By.CSS_SELECTOR, ".only\\3Am-auto:nth-child(1)")
        except Exception as e:
            print(f"Trying JavaScript click due to: {e}")
            element = self.driver.find_element(By.CSS_SELECTOR, ".only\\3Am-auto:nth-child(1)")
            self.driver.execute_script("arguments[0].click();", element)

        try:
            self.vars["win6451"] = self.wait_for_window(2000)
            self.vars["root"] = self.driver.current_window_handle
            self.driver.switch_to.window(self.vars["win6451"])
            self.driver.close()
            self.driver.switch_to.window(self.vars["root"])
        except Exception as e:
            print(f"Window handling error: {e}")

        try:
            WebDriverWait(self.driver, 10).until(
                EC.frame_to_be_available_and_switch_to_it(0)
            )
            self.safe_send_keys(By.CSS_SELECTOR, ".border-2", "11111111")
            self.safe_click(By.CSS_SELECTOR, ".left-0 > .rounded-lg")
            self.driver.switch_to.default_content()
        except Exception as e:
            print(f"Frame handling error: {e}")
            self.driver.switch_to.default_content()

        self.safe_click(By.LINK_TEXT, "Generate Bill")
        self.safe_click(By.CSS_SELECTOR, ".btn:nth-child(1)")
        self.safe_click(By.CSS_SELECTOR, ".btn:nth-child(2)")
        self.safe_click(By.CSS_SELECTOR, "button:nth-child(6)")
        self.safe_click(By.CSS_SELECTOR, ".btn:nth-child(3)")