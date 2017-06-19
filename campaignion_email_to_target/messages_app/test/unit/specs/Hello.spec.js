import Vue from 'vue'
import Hello from '@/components/Hello'
import store from '@/store'

describe('Hello.vue', () => {
  it('should render correct contents', () => {
    const Constructor = Vue.extend({...Hello, store})
    const vm = new Constructor().$mount()
    expect(vm.$el.querySelector('.hello h1').textContent)
      .to.equal('Welcome to Your Vue.js App')
  })
})
