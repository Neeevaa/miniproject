import os
import time
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions
from selenium.common.exceptions import ElementClickInterceptedException


class TestUpdatebooking:
    def setup_method(self, method):
        # 🔥 Disable proxies from OS-level if they were auto-set
        os.environ["http_proxy"] = ""
        os.environ["https_proxy"] = ""
        os.environ["HTTP_PROXY"] = ""
        os.environ["HTTPS_PROXY"] = ""

        chrome_options = Options()

        # 🍃 Bypass all proxies completely
        chrome_options.add_argument("--no-proxy-server")
        chrome_options.add_argument("--proxy-bypass-list=*")
        chrome_options.add_argument("--proxy-server=direct://")

        # Optional: suppress logging noise
        chrome_options.add_experimental_option("excludeSwitches", ["enable-logging"])

        self.driver = webdriver.Chrome(service=Service(), options=chrome_options)
        self.vars = {}

    def teardown_method(self, method):
        self.driver.quit()

    def scroll_into_view_and_click(self, by, value):
        element = WebDriverWait(self.driver, 10).until(
            expected_conditions.element_to_be_clickable((by, value))
        )
        self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", element)
        WebDriverWait(self.driver, 5).until(
            expected_conditions.visibility_of(element)
        )
        time.sleep(1)  # Short delay for animations/sticky headers to settle

        try:
            element.click()
        except ElementClickInterceptedException:
            # Fallback to JS click if normal click is intercepted
            self.driver.execute_script("arguments[0].click();", element)

    def test_updatebooking(self):
        self.driver.get("http://localhost/aromiq/login.php")
        self.driver.set_window_size(1050, 700)
        self.driver.find_element(By.ID, "uname").send_keys("admin")
        self.driver.find_element(By.ID, "pswd").send_keys("1234")

        self.scroll_into_view_and_click(By.CSS_SELECTOR, ".w-100")
        self.scroll_into_view_and_click(By.LINK_TEXT, "📅 Table Reservations")
        self.scroll_into_view_and_click(By.CSS_SELECTOR, "tr:nth-child(5) .btn-success")

        WebDriverWait(self.driver, 5).until(expected_conditions.alert_is_present())
        alert = self.driver.switch_to.alert
        assert alert.text == "Booking status updated to Confirmed"
        alert.accept()
