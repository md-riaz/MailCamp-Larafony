const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  use: {
    headless: true, // Run all tests in headless mode
    baseURL: 'https://mailcamp.opc.mdriaz.com.bd', // Target base URL
    viewport: { width: 2560, height: 1440 },
  },
});