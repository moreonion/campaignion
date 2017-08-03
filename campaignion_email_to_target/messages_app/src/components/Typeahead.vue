<template>
  <div style="position: relative"
       v-bind:class="{
         'typeahead': true,
         'open': showDropdown
       }"
  >
    <input type="text" class="form-control typeahead-input"
      ref="input"
      :placeholder="placeholder"
      autocomplete="off"
      v-model="val"
      @input="update"
      @focus="showCachedOrUpdate"
      @keydown.up="up"
      @keydown.down="down"
      @keydown.enter= "hit"
      @keydown.esc="showDropdown = false"
      @blur="showDropdown = false"
    />
    <ul class="dropdown-menu" ref="dropdown" @scroll="scroll">
      <li v-for="item in items" v-bind:class="{'active': isActive($index)}">
        <a class="dropdown-item" @mousedown.prevent="hit" @mousemove="setActive($index)">
          <partial :name="templateName"></partial>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
import Vue from 'vue'

const _DELAY_ = 200

const coerce = {
  // Convert a string to booleam. Otherwise, return the value without modification, so if is not boolean, Vue throw a warning.
  boolean: val => (typeof val === 'string' ? val === '' || val === 'true' ? true : (val === 'false' || val === 'null' || val === 'undefined' ? false : val) : val),
  // Attempt to convert a string value to a Number. Otherwise, return 0.
  number: (val, alt = null) => (typeof val === 'number' ? val : val === undefined || val === null || isNaN(Number(val)) ? alt : Number(val)),
  // Attempt to convert to string any value, except for null or undefined.
  string: val => (val === undefined || val === null ? '' : val + ''),
  // Pattern accept RegExp, function, or string (converted to RegExp). Otherwise return null.
  pattern: val => (val instanceof Function || val instanceof RegExp ? val : typeof val === 'string' ? new RegExp(val) : null)
}

function paramReadyUrl (url) {
  if (!url.match(/\?[^=]+=[^&]*/)) {
    // there’s no parameter. replace trailing ? or / or /? with ?
    return url.replace(/[/?]$|(?:\/)\?$/, '') + '?'
  } else {
    // parameter present in the string. ensure trailing &
    return url.replace(/[&]$/, '') + '&'
  }
}

function fixedEncodeURIComponent (str) {
  return encodeURIComponent(str).replace(/[!'()*]/g, c => '%' + c.charCodeAt(0).toString(16))
}

export default {
  created () {
    this.items = this.primitiveData
  },
  partials: {
    default: '<span v-html="item | highlight value"></span>'
  },
  props: {
    value: {
      type: String,
      default: ''
    },
    data: {
      type: Array
    },
    count: {
      type: Number,
      default: 8
    },
    async: {
      type: String,
      coerce: paramReadyUrl
    },
    headers: {
      type: Object,
      default: {}
    },
    template: {
      type: String
    },
    templateName: {
      type: String,
      default: 'default'
    },
    dataKey: {
      type: String,
      default: null
    },
    matchCase: {
      type: Boolean,
      coerce: coerce.boolean,
      default: false
    },
    matchStart: {
      type: Boolean,
      coerce: coerce.boolean,
      default: false
    },
    onHit: {
      type: Function,
      default (item) {
        if (item) {
          this.reset()
          this.$emit('input', item)
        }
      }
    },
    placeholder: {
      type: String
    },
    delay: {
      type: Number,
      default: _DELAY_,
      coerce: coerce.number
    },
    showDropdownOnFocus: { // display the dropdown immediately when focusing the input
      type: Boolean,
      coerce: coerce.boolean,
      default: false
    },
    lazyLoad: { // allow the user to request more items from the server
      type: Boolean,
      coerce: coerce.boolean,
      default: false
    },
    pageMode: { // whether the API uses paging or offset. allowed: 'page'  or 'offset'
      type: String,
      coerce: val => {
        return (val === 'page' || val === 'offset') ? val : 'page'
      },
      default: 'page'
    },
    pageParam: { // query parameter for page or offset
      type: String,
      default: 'p'
    },
    firstPage: { // the page that pagination starts with. allowed: 0 or 1
      type: Number,
      coerce: val => {
        return (val === 0 || val === '0') ? 0 : 1
      },
      default: 1
    },
    searchParam: { // query parameter for the search term
      type: String,
      default: 's'
    },
    countParam: { // query parameter for the number of items or page size
      type: String,
      default: 'n'
    }
  },
  data () {
    return {
      val: this.value,
      showDropdown: false,
      current: 0,
      items: [],
      lastLoadedPage: 0,
      moreItemsLoadable: true,
      queryOnTheWay: false
    }
  },
  computed: {
    primitiveData () {
      if (this.data) {
        return this.data.filter(value => {
          value = this.matchCase ? value : value.toLowerCase()
          var query = this.matchCase ? this.value : this.value.toLowerCase()
          return this.matchStart ? value.indexOf(query) === 0 : value.indexOf(query) !== -1
        }).slice(0, this.count)
      } else {
        return []
      }
    }
  },
  ready () {
    // register a partial:
    if (this.templateName && this.templateName !== 'default') {
      Vue.partial(this.templateName, this.template)
    }
  },
  watch: {
    val (val, old) {
      this.$emit('input', val)
      if (val !== old) this.update()
    },
    value (val) {
      if (this.val !== val) { this.val = val }
    }
  },
  methods: {
    update () {
      if (!this.showDropdownOnFocus && !this.value) {
        this.reset()
        return false
      }
      if (this.data) {
        this.items = this.primitiveData
        this.showDropdown = this.items.length > 0
      }
      if (this.async) {
        this.reset()
        var lastVal = this.value
        setTimeout(() => {
          // only query if the value didn’t change during the delay period
          if (this.value === lastVal) this.query()
        }, this.delay)
      }
    },
    query () {
      var url = this.async + '?' + this.searchParam + '=' + fixedEncodeURIComponent(this.value) + '&' + this.countParam + '=' + this.count
      if (this.lazyLoad) url += '&' + this.pageParam + '=' + (this.pageMode === 'page' ? this.lastLoadedPage + this.firstPage : this.lastLoadedPage * this.count)
      console.log(this.headers)
      this.$http.get(url, {
        headers: this.headers
      }).then(response => {
        console.log(response)
        // get the search term from the url
        const re = new RegExp('[&|?]search=([^&]*)')
        const searchVal = response.config.url.match(re)[1]
        // throw the response away if the typeahead value has changed in the meantime
        if (fixedEncodeURIComponent(this.value) !== searchVal) return

        var data = response.data
        if (this.lazyLoad) {
          var newItems = this.dataKey ? data[this.dataKey] : data
          if (newItems.length) {
            this.items.push(...newItems)
            this.lastLoadedPage++
          }
          this.queryOnTheWay = false
          this.moreItemsLoadable = newItems.length === this.count
        } else {
          this.items = (this.dataKey ? data[this.dataKey] : data).slice(0, this.count)
        }
        this.showDropdown = this.items.length > 0
      })
    },
    showCachedOrUpdate () {
      if (!this.showDropdownOnFocus) {
        return
      }
      if (this.items.length) {
        this.showDropdown = true
      } else {
        this.update()
      }
    },
    reset () {
      this.items = []
      this.current = 0
      this.lastLoadedPage = 0
      this.showDropdown = false
      this.moreItemsLoadable = true
      this.$refs.dropdown.scrollTop = 0
    },
    setActive (index) {
      this.current = index
    },
    isActive (index) {
      return this.current === index
    },
    hit (e) {
      e.preventDefault()
      this.onHit(this.items[this.current], this)
    },
    up (e) {
      e.preventDefault()
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
      if (this.current < this.items.length - 1) {
        this.current++
        const d = this.$refs.dropdown
        const i = d.children[this.current]
        if (i.offsetTop + i.clientHeight > d.scrollTop + d.clientHeight) {
          d.scrollTop += i.clientHeight
        }
      }
    },
    scroll () {
      // lazy-load more items
      if (!this.lazyLoad || !this.moreItemsLoadable) return
      if (this.$refs.dropdown.scrollHeight - this.$refs.dropdown.scrollTop - 30 < this.$refs.dropdown.clientHeight) {
        if (!this.queryOnTheWay && this.items.length) this.query()
        this.queryOnTheWay = true
      }
    }
  },
  filters: {
    highlight (value, phrase) {
      return value.replace(new RegExp('(' + phrase + ')', 'gi'), '<strong>$1</strong>')
    }
  }
}
</script>

<style>
.dropdown-menu > li > a {
  cursor: pointer;
}
.typeahead .dropdown-menu {
  max-height: 12rem;
  overflow-y: auto;
}
.typeahead.open .dropdown-menu {
  display: block;
}
</style>
