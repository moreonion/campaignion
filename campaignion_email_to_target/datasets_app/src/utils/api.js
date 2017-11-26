import axios from 'axios'

const url = Drupal.settings.campaignion_email_to_target.endpoints['e2t-api'].url + '/jwt/'
const headers = {
  Authorization: 'JWT ' + Drupal.settings.campaignion_email_to_target.endpoints['e2t-api'].token
}

export default {
  getDatasets () {
    return axios.get(url, {headers})
  }
}
