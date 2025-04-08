# test_update.py

import pytest
import time
import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC

class TestUpdate():
    def setup_method(self, method):
        # Disable proxy
        os.environ['http_proxy'] = ''
        os.environ['https_proxy'] = ''
        os.environ['HTTP_PROXY'] = ''
        os.environ['HTTPS_PROXY'] = ''

        chrome_options = Options()
        chrome_options.add_argument("--no-proxy-server")
        chrome_options.add_argument("--proxy-server='direct://'")
        chrome_options.add_argument("--proxy-bypass-list=*")
        chrome_options.add_experimental_option('excludeSwitches', ['enable-logging'])

        self.driver = webdriver.Chrome(service=Service(), options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)

    def teardown_method(self, method):
        self.driver.quit()

    def update_order_status(self, status_text):
        # Wait for modal and dropdown
        self.wait.until(EC.visibility_of_element_located((By.ID, "modal-order-status")))
        dropdown = Select(self.driver.find_element(By.ID, "modal-order-status"))
        dropdown.select_by_visible_text(status_text)

        update_button = self.wait.until(EC.element_to_be_clickable((By.ID, "modal-update-status")))
        self.driver.execute_script("arguments[0].scrollIntoView(true);", update_button)
        time.sleep(0.3)
        self.driver.execute_script("arguments[0].click();", update_button)
        time.sleep(1)

    def test_update(self):
        self.driver.get("http://localhost/aromiq/login.php")
        self.driver.set_window_size(1050, 700)

        # Scroll to top and disable smooth scroll
        self.driver.execute_script("window.scrollTo(0, 0);")
        self.driver.execute_script("document.body.style.scrollBehavior = 'auto';")

        # Login
        self.driver.find_element(By.ID, "uname").send_keys("admin")
        self.driver.find_element(By.ID, "pswd").send_keys("1234")

        login_button = self.wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".w-100")))
        self.driver.execute_script("arguments[0].scrollIntoView(true);", login_button)
        time.sleep(0.5)
        self.driver.execute_script("arguments[0].click();", login_button)

        # Wait for dashboard
        self.wait.until(EC.presence_of_element_located((By.LINK_TEXT, "📦 View Orders")))

        view_orders_link = self.wait.until(EC.element_to_be_clickable((By.LINK_TEXT, "📦 View Orders")))
        self.driver.execute_script("arguments[0].scrollIntoView(true);", view_orders_link)
        time.sleep(0.3)
        self.driver.execute_script("arguments[0].click();", view_orders_link)

        # Wait for orders page to load
        view_btn = self.wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, "tr:nth-child(1) .view-order")))
        self.driver.execute_script("arguments[0].scrollIntoView(true);", view_btn)
        time.sleep(0.3)
        self.driver.execute_script("arguments[0].click();", view_btn)

        # Wait for modal
        self.wait.until(EC.visibility_of_element_located((By.ID, "modal-order-status")))

        for status in ["Preparing", "Ready", "Plating", "Served"]:
            self.update_order_status(status)

        # Close modal
        close_btn = self.wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".btn-secondary")))
        self.driver.execute_script("arguments[0].scrollIntoView(true);", close_btn)
        time.sleep(0.3)
        self.driver.execute_script("arguments[0].click();", close_btn)

        time.sleep(1)