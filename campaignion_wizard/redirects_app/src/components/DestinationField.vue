<template lang="html">
  <div style="position: relative"
    :class="{
     'typeahead': true,
     'open': showDropdown
    }"
    >
    <input type="text" class="field-input typeahead-input"
      ref="input"
      :placeholder="placeholder"
      autocomplete="off"
      v-model="val"
      @input="update"
      @focus="update"
      @keydown.up="up"
      @keydown.down="down"
      @keyup.enter= "hit"
      @keydown.esc="esc"
    />
    <!--
    TODO:
    @blur="showDropdown = false"

    -->
    <ul v-if="showDropdown" ref="dropdown" class="dropdown-menu">
      <li v-for="(item, index) in items" :class="{'active': isActive(index)}">
        <a class="dropdown-item" @mousedown.prevent="hit" @mousemove="setActive(index)">
          <component :is="templateComp" :item="item" :value="val"></component>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import api from '@/utils/api'

const _DELAY_ = 200

function fixedEncodeURIComponent (str) {
  return encodeURIComponent(str).replace(/[!'()*]/g, c => '%' + c.charCodeAt(0).toString(16))
}

function escapeRegExp (str) {
  /* eslint-disable no-useless-escape */
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&')
}

export default {
  props: {
    value: { // prettyDestination
      type: String,
      default: ''
    },
    count: {
      type: Number,
      default: 8
    },
    url: String,
    headers: {
      type: Object,
      default () {
        return {}
      }
    },
    template: String,
    dataKey: {
      type: String,
      default: null
    },
    labelKey: {
      type: String,
      default: 'label'
    },
    valueKey: {
      type: String,
      default: 'value'
    },
    matchCase: {
      type: Boolean,
      default: false
    },
    matchStart: {
      type: Boolean,
      default: false
    },
    onHit: {
      type: Function,
      default (item) {
        console.log('onHit: item:', item)
        if (item) {
          this.reset()
          this.$emit('input', item)
        }
      }
    },
    placeholder: String,
    delay: {
      type: Number,
      default: _DELAY_
    },
    searchParam: { // query parameter for the search term
      type: String,
      default: 's'
    },
    showDropdownOnFocus: { // display the dropdown immediately when focusing the input
      type: Boolean,
      default: false
    }
  },

  data () {
    return {
      val: this.value,
      showDropdown: false,
      current: 0,
      items: [],
      queryOnTheWay: false
    }
  },

  computed: {
    templateComp () {
      return {
        template: typeof this.template === 'string' ? '<span v-html="this.template"></span>' : '<span v-html="highlight(item.' + this.labelKey + ', value)"></span>',
        props: {
          item: {default: null},
          value: String
        },
        methods: {
          highlight (string, phrase) {
            return (string && phrase && string.replace(new RegExp('(' + escapeRegExp(phrase) + ')', 'gi'), '<strong>$1</strong>')) || string
          }
        }
      }
    }
  },

  watch: {
//    val (val) {
//      this.$emit('input', val)
//    },
//    value (val) {
//      console.log('value watcher')
//      if (this.val !== val) { this.val = val }
//    }
  },

  methods: {
    update () {
      console.log('update')
      if (!this.showDropdownOnFocus && !this.val) {
        this.reset()
        return false
      }
      if (this.url) {
        this.reset()
        var lastVal = this.val
        setTimeout(() => {
          // only query if the value didn’t change during the delay period
          if (this.val === lastVal) this.query()
        }, this.delay)
      }
    },
    query () {
      console.log('query')
      api.getNodes({
        url: this.url,
        headers: this.headers,
        queryParam: this.searchParam,
        queryString: fixedEncodeURIComponent(this.val)
      }).then(response => {
        console.log('dealing with response')
        // get the search term from the url
        const re = new RegExp('[&|?]' + this.searchParam + '=([^&]*)')
        console.log(response)
        var searchVal
        try {
          searchVal = response.config.url.match(re)[1]
        } catch (error) {
          return
        }
        // throw the response away if the typeahead value has changed in the meantime
        if (fixedEncodeURIComponent(this.val) !== searchVal) return

        var data = response.data
        this.items = (this.dataKey ? data[this.dataKey] : data).slice(0, this.count)
        this.showDropdown = this.items.length > 0
      })
    },
    reset () {
      this.items = []
      this.current = 0
      this.showDropdown = false
    },
    setActive (index) {
      this.current = index
    },
    isActive (index) {
      return this.current === index
    },
    hit (e) {
      if (this.showDropdown) {
        e.preventDefault()
        e.stopPropagation()
        this.val = this.items[this.current][this.labelKey]
        this.onHit(this.items[this.current], this)
      }
    },
    up (e) {
      e.preventDefault()
      if (!this.showDropdown) {
        this.showCachedOrUpdate()
        return
      }
      if (this.current > 0) {
        this.current--
        const d = this.$refs.dropdown
        const i = d.children[this.current]
        if (i.offsetTop < d.scrollTop) {
          d.scrollTop -= i.clientHeight
        }
      }
    },
    down (e) {
      e.preventDefault()
      if (!this.showDropdown) {
        this.showCachedOrUpdate()
        return
      }
      if (this.current < this.items.length - 1) {
        this.current++
        const d = this.$refs.dropdown
        const i = d.children[this.current]
        if (i.offsetTop + i.clientHeight > d.scrollTop + d.clientHeight) {
          d.scrollTop += i.clientHeight
        }
      }
    },
    esc (e) {
      if (this.showDropdown) {
        e.stopPropagation()
        this.showDropdown = false
      }
    }
  }
}
</script>

<style lang="css">
</style>
