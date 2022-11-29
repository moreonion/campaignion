module.exports = {
  src_folders: ['test/e2e/specs'],
  filter: 'test/e2e/specs/*.js',
  output_folder: false,
  custom_commands_path: ['node_modules/nightwatch-xhr/es5/commands'],
  custom_assertions_path: [
    'test/e2e/custom-assertions',
    'node_modules/nightwatch-xhr/es5/assertions'
  ],
  page_objects_path: ['test/e2e/pages'],

  selenium: {
    start_process: true,
    start_session: false,
    server_path: require('selenium-server').path,
    check_process_delay: 5000,
    host: '127.0.0.1',
    port: 4444,
    cli_args: {
      'webdriver.chrome.driver': require('chromedriver').path
    }
  },

  test_settings: {
    skip_testcases_on_fail: false,
    end_session_on_fail: false,
    default: {
      screenshots: {
        enabled: true,
        on_failure: true,
        on_error: false,
        path: 'test/e2e/screenshots'
      },
      desiredCapabilities: {
        browserName: 'chrome'
      }
    },
    chrome: {
      desiredCapabilities: {
        browserName: 'chrome',
        javascriptEnabled: true,
        acceptSslCerts: true,
        chromeOptions: {
          w3c: false,
          args: ['disable-gpu']
        }
      }
    }
  }
}
