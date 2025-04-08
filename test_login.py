import pytest
import time
import os
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options

class TestLogin():
  def setup_method(self, method):
    # 🧼 Remove environment proxy settings
    os.environ['http_proxy'] = ''
    os.environ['https_proxy'] = ''
    os.environ['HTTP_PROXY'] = ''
    os.environ['HTTPS_PROXY'] = ''

    # 🚫 Tell Chrome to ignore proxies
    chrome_options = Options()
    chrome_options.add_argument('--no-proxy-server')
    chrome_options.add_argument('--proxy-bypass-list=*')

    self.driver = webdriver.Chrome(options=chrome_options)
    self.vars = {}

  def teardown_method(self, method):
    self.driver.quit()

  def test_login(self):
    self.driver.get("http://localhost/aromiq/index.html")
    self.driver.set_window_size(1382, 736)
    self.driver.find_element(By.CSS_SELECTOR, ".py-2:nth-child(2)").click()
    self.driver.find_element(By.ID, "uname").click()
    self.driver.find_element(By.ID, "uname").send_keys("kitchenadmin")
    self.driver.find_element(By.ID, "pswd").send_keys("1234")
    self.driver.find_element(By.CSS_SELECTOR, ".w-100").click()
