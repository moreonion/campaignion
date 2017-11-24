import axios from 'axios'

// function paramReadyUrl (url) {
//   if (!url.match(/\?[^=]+=[^&]*/)) {
//     // there’s no parameter. replace trailing ? or / or /? with ?
//     return url.replace(/[/?]$|(?:\/)\?$/, '') + '?'
//   } else {
//     // parameter present in the string. ensure trailing &
//     return url.replace(/[&]$/, '') + '&'
//   }
// }
//

export default {
  postData: function ({url, data, headers}) {
    // TODO authentification?
    return axios.put(url, data)
  },
  getNodes: function ({url, queryParam, queryString, headers}) {
    // Mock server response:
    return new Promise(function (resolve, reject) {
      setTimeout(function () {
        var values = []
        if (!queryString) {
          for (let i = 0; i < 300; i++) {
            values.push({
              value: 'node/' + i,
              label: 'Some Node title (' + i + ')'
            })
          }
        } else {
          for (let i = 0; i < 20 / queryString.length; i++) {
            values.push({
              value: 'node/' + i,
              label: 'Some Node title containing ' + queryString + ' (' + i + ')'
            })
          }
        }
        resolve({
          config: {
            url: url + '?' + queryParam + '=' + queryString
          },
          data: {
            values
          }
        })
      }, 500)
    })
  }
}
