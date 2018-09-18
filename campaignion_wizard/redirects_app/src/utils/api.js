import axios from 'axios'

function paramReadyUrl (url) {
  if (!url.match(/\?[^=]+=[^&]*/)) {
    // there’s no parameter. replace trailing ? or / or /? with ?
    return url.replace(/[/?]$|(?:\/)\?$/, '') + '?'
  } else {
    // parameter present in the string. ensure trailing &
    return url.replace(/[&]$/, '') + '&'
  }
}

export default {
  postData: function ({url, data, headers}) {
    // TODO authentification?
    return axios.put(url, data)
  },
  getNodes: function ({url, queryParam, queryString, headers}) {
    // TODO authentification?
    return axios.get(paramReadyUrl(url) + queryParam + '=' + queryString)
  }
}
