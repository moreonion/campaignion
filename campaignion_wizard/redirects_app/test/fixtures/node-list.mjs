export default function (url) {
  const slices = url.match(/.+\?(.+)=(.+)/)
  const queryString = (slices && slices[2]) || ''

  const values = []
  if (!queryString) {
    for (let i = 1; i <= 300; i++) {
      values.push({
        value: 'node/' + i,
        label: 'Some Node title (' + i + ')'
      })
    }
  } else {
    for (let i = 1; i < 20 / queryString.length; i++) {
      values.push({
        value: 'node/' + i,
        label: 'Some Node title containing ' + queryString + ' (' + i + ')'
      })
    }
  }

  return { values }
}
