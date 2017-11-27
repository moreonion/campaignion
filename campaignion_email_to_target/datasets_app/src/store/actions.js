import api from '@/utils/api'

export default {
  loadDatasets (context) {
    api.getDatasets().then(data => {
      context.commit('setDatasets', data.data)
    }, () => {
      context.commit('setApiError', true)
    })
  }
}
