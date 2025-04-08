# test_insertitem.py
import os
import pytest
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import ElementClickInterceptedException

class TestInsertitem:
    def setup_method(self, method):
        os.environ['http_proxy'] = ''
        os.environ['https_proxy'] = ''
        self.driver = webdriver.Chrome()
        self.driver.implicitly_wait(5)

    def teardown_method(self, method):
        self.driver.quit()

    def safe_click(self, driver, element):
        try:
            driver.execute_script("arguments[0].scrollIntoView(true);", element)
            WebDriverWait(driver, 5).until(EC.visibility_of(element))
            xpath = self.get_element_xpath(driver, element)
            WebDriverWait(driver, 5).until(EC.element_to_be_clickable((By.XPATH, xpath)))
            element.click()
        except ElementClickInterceptedException:
            driver.execute_script("arguments[0].click();", element)

    def get_element_xpath(self, driver, element):
        return driver.execute_script("""
            function absoluteXPath(element) {
                if (element.nodeType === Node.DOCUMENT_NODE) {
                    return '/';
                }

                var paths = [];

                while (element && element.nodeType === Node.ELEMENT_NODE) {
                    var index = 0;
                    var hasSameTagSiblings = false;
                    var siblings = element.parentNode ? element.parentNode.children : null;

                    if (siblings) {
                        for (var i = 0; i < siblings.length; i++) {
                            var sibling = siblings[i];
                            if (sibling.nodeName === element.nodeName) {
                                if (sibling === element) {
                                    index++;
                                    break;
                                }
                                index++;
                            }
                        }
                    }

                    var tagName = element.nodeName.toLowerCase();
                    var pathIndex = (index > 1) ? `[${index}]` : '';
                    paths.unshift(`${tagName}${pathIndex}`);
                    element = element.parentNode;
                }

                return '/' + paths.join('/');
            }
            return absoluteXPath(arguments[0]);
        """, element)

    def test_insertitem(self):
        driver = self.driver
        wait = WebDriverWait(driver, 10)

        driver.get("http://localhost/aromiq/login.php")
        driver.set_window_size(1382, 736)

        # Login
        wait.until(EC.presence_of_element_located((By.ID, "uname"))).send_keys("admin")
        wait.until(EC.presence_of_element_located((By.ID, "pswd"))).send_keys("1234")
        login_button = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, ".w-100")))
        self.safe_click(driver, login_button)

        # Fill the item form
        wait.until(EC.presence_of_element_located((By.NAME, "itemname"))).send_keys("Cheesy Tuna Wrap")

        category_dropdown = wait.until(EC.presence_of_element_located((By.NAME, "category")))
        category_dropdown.find_element(By.XPATH, "//option[. = 'Main Course']").click()

        wait.until(EC.presence_of_element_located((By.NAME, "price"))).send_keys("150")
        wait.until(EC.presence_of_element_located((By.NAME, "itemdescription"))).send_keys("its filled with cheesy tuna and the wrap is so juicy")
        wait.until(EC.presence_of_element_located((By.NAME, "itemdetailed"))).send_keys("its a must try item!")

        # Use a valid absolute image path
        image_file = "cheesy_tuna_mets.jpg"
        image_path = os.path.abspath(image_file)

        if not os.path.exists(image_path):
            raise FileNotFoundError(f"Image file not found at path: {image_path}")

        wait.until(EC.presence_of_element_located((By.NAME, "itemimage"))).send_keys(image_path)

        # Submit the form
        submit_button = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, ".btn:nth-child(7)")))
        self.safe_click(driver, submit_button)