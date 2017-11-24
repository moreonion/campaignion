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
    return new Promise().resolve()
  },
  getNodes: function ({url, queryParam, queryString, headers}) {
    console.log('api - url: ' + url)
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
            url: 'http://mytest.com/foo/bar?' + queryParam + '=' + queryString
          },
          data: {
            values
          }
        })
      }, 500)
    })
  }
}
