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

  webdriver: {
    start_process: true,
    server_path: 'node_modules/.bin/chromedriver',
    cli_args: [
      '--verbose'
    ],
    port: 9515
  },

  test_settings: {
    default: {
      desiredCapabilities: {
        browserName: 'chrome'
      },
      screenshots: {
        enabled: true,
        on_failure: true,
        on_error: false,
        path: 'test/e2e/screenshots'
      }
    }
  }
}
