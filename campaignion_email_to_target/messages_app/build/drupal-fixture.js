// This module is used to provide the Drupal global in development and test mode.

import exampleData from '../test/unit/fixtures/example-data'
import initialData from '../test/unit/fixtures/initial-data'

export default {
  settings: {
    campaignion_email_to_target: (process.env.NODE_ENV === 'development')
      ? exampleData
      : initialData
  },
  t: string => {
    return string
  }
}
