import pytest
import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC

class TestLogin():
  def setup_method(self, method):
    # Clear any proxy settings
    os.environ['http_proxy'] = ''
    os.environ['https_proxy'] = ''
    os.environ['HTTP_PROXY'] = ''
    os.environ['HTTPS_PROXY'] = ''

    chrome_options = Options()
    chrome_options.add_argument('--no-proxy-server')
    chrome_options.add_argument('--proxy-bypass-list=*')

    self.driver = webdriver.Chrome(options=chrome_options)
    self.driver.set_window_size(1382, 736)
    self.wait = WebDriverWait(self.driver, 10)

  def teardown_method(self, method):
    self.driver.quit()

  def test_login(self):
    self.driver.get("http://localhost/aromiq/index.html")

    # Click Login button (adjust selector if needed)
    self.wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".py-2:nth-child(2)"))).click()

    # Wait until 'uname' is clickable and scroll into view
    uname = self.wait.until(EC.element_to_be_clickable((By.ID, "uname")))
    self.driver.execute_script("arguments[0].scrollIntoView(true);", uname)
    uname.click()
    uname.send_keys("kitchenadmin")

    pswd = self.driver.find_element(By.ID, "pswd")
    pswd.send_keys("1234")

    # Click login button
    login_btn = self.wait.until(EC.element_to_be_clickable((By.CSS_SELECTOR, ".w-100")))
    self.driver.execute_script("arguments[0].scrollIntoView(true);", login_btn)
    login_btn.click()
