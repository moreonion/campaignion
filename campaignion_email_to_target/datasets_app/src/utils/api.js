import axios from 'axios'

const url = Drupal.settings.campaignion_email_to_target.endpoints['e2t-api'].url + '/jwt/'
const headers = {
  Authorization: 'JWT ' + Drupal.settings.campaignion_email_to_target.endpoints['e2t-api'].token
}

export default {
  getDatasets () {
    return axios.get(url, {headers})
  },

  getContacts (datasetKey) {
    return axios.get(url + datasetKey + '/contact', {headers})
  },

  saveDataset (dataset, createNew) {
    return axios({
      method: createNew ? 'post' : 'put',
      url: createNew ? url : url + dataset.key + '/',
      data: JSON.stringify(dataset),
      headers,
      transformRequest: [function (data, headers) {
        headers.post = {'Content-Type': 'application/json'}
        headers.put = {'Content-Type': 'application/json'}
        return data
      }]
    })
  },

  saveContacts (datasetKey, contacts, createNew) {
    return axios({
      method: createNew ? 'post' : 'put',
      url: url + datasetKey + '/contact',
      data: JSON.stringify(contacts),
      headers,
      transformRequest: [function (data, headers) {
        headers.post = {'Content-Type': 'application/json'}
        headers.put = {'Content-Type': 'application/json'}
        return data
      }]
    })
  }
}
