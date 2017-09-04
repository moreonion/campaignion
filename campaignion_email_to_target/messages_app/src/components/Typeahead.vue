<template>
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
      @focus="showCachedOrUpdate"
      @keydown.up="up"
      @keydown.down="down"
      @keyup.enter= "hit"
      @keydown.esc="esc"
      @blur="showDropdown = false"
    />
    <ul v-if="showDropdown" @scroll="scroll" ref="dropdown" class="dropdown-menu">
      <li v-for="(item, index) in items" :class="{'active': isActive(index)}">
        <a class="dropdown-item" @mousedown.prevent="hit" @mousemove="setActive(index)">
          <component :is="templateComp" :item="item" :value="val"></component>
        </a>
      </li>
    </ul>
  </div>
</template>

<script>
const _DELAY_ = 200

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

  props: {
    value: {
      type: String,
      default: ''
    },
    data: Array,
    count: {
      type: Number,
      default: 8
    },
    async: String,
    headers: {
      type: Object,
      default: {}
    },
    template: String,
    dataKey: {
      type: String,
      default: null
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
    showDropdownOnFocus: { // display the dropdown immediately when focusing the input
      type: Boolean,
      default: false
    },
    lazyLoad: { // allow the user to request more items from the server
      type: Boolean,
      default: false
    },
    pageMode: { // whether the API uses paging or offset. allowed: 'page'  or 'offset'
      type: String,
      default: 'page'
    },
    pageParam: { // query parameter for page or offset
      type: String,
      default: 'p'
    },
    firstPage: { // the page that pagination starts with. allowed: 0 or 1
      type: Number,
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
      cachedQuery: null,
      showDropdown: false,
      current: 0,
      items: [],
      lastLoadedPage: 0,
      moreItemsLoadable: true,
      queryOnTheWay: false
    }
  },

  computed: {
    templateComp () {
      return {
        template: typeof this.template === 'string' ? '<span v-html="this.template"></span>' : '<span v-html="highlight(item, value)"></span>',
        props: {
          item: {default: null},
          value: String
        },
        methods: {
          highlight (string, phrase) {
            return (string && phrase && string.replace(new RegExp('(' + phrase + ')', 'gi'), '<strong>$1</strong>')) || string
          }
        }
      }
    },
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
    },
    url () {
      return paramReadyUrl(this.async)
    },
    coercedPageMode () {
      return (this.pageMode === 'page' || this.pageMode === 'offset') ? this.pageMode : 'page'
    },
    coercedFirstPage () {
      return (this.firstPage === 0 || this.firstPage === '0') ? 0 : 1
    }
  },

  watch: {
    val (val, old) {
      this.$emit('input', val)
    },
    value (val) {
      if (this.val !== val) { this.val = val }
    }
  },

  methods: {
    update () {
      if (!this.showDropdownOnFocus && !this.val) {
        this.reset()
        return false
      }
      if (this.data) {
        this.items = this.primitiveData
        this.showDropdown = this.items.length > 0
      }
      if (this.async) {
        this.reset()
        var lastVal = this.val
        setTimeout(() => {
          // only query if the value didn’t change during the delay period
          if (this.val === lastVal) this.query()
        }, this.delay)
      }
    },
    query () {
      var url = this.url + this.searchParam + '=' + fixedEncodeURIComponent(this.val) + '&' + this.countParam + '=' + this.count
      if (this.lazyLoad) url += '&' + this.pageParam + '=' + (this.coercedPageMode === 'page' ? this.lastLoadedPage + this.coercedFirstPage : this.lastLoadedPage * this.count)
      this.$http.get(url, {
        headers: this.headers
      }).then(response => {
        // get the search term from the url
        const re = new RegExp('[&|?]search=([^&]*)')
        const searchVal = response.config.url.match(re)[1]
        // throw the response away if the typeahead value has changed in the meantime
        if (fixedEncodeURIComponent(this.val) !== searchVal) return

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
        this.cachedQuery = searchVal
        this.showDropdown = this.items.length > 0
      })
    },
    showCachedOrUpdate () {
      if (!this.showDropdownOnFocus) {
        return
      }
      if (this.items.length && this.val === this.cachedQuery) {
        this.showDropdown = true
      } else {
        this.update()
      }
    },
    reset () {
      this.items = []
      this.cachedQuery = null
      this.current = 0
      this.lastLoadedPage = 0
      this.showDropdown = false
      this.moreItemsLoadable = true
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
    },
    scroll () {
      // lazy-load more items
      if (!this.lazyLoad || !this.moreItemsLoadable) return
      if (this.$refs.dropdown.scrollHeight - this.$refs.dropdown.scrollTop - 30 < this.$refs.dropdown.clientHeight) {
        if (!this.queryOnTheWay && this.items.length) this.query()
        this.queryOnTheWay = true
      }
    }
  }
}
</script>

<style lang="scss">
.typeahead {
  display: inline-block;

  .dropdown-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    min-width: 100%;
    max-height: 12rem;
    overflow-y: auto;
    list-style: none;
    margin: 0;
    padding: 0;
    z-index: 2000;

    & > li {
      width: 100%;

      &.active {
        color: #fff;
        background-color: #aaa;
      }
    }

    & > li > a {
      display: inline-block;
      width: 100%;
      cursor: pointer;
    }
  }

  &.open .dropdown-menu {
    display: block;
  }
}
</style>
