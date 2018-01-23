<template lang="html">
  <div :class="{
     'typeahead': true,
     'open': showDropdown
    }">
    <input type="text" class="field-input typeahead-input"
      ref="input"
      :placeholder="placeholder"
      autocomplete="off"
      v-model="val"
      @input="update"
      @focus="focus"
      @keydown.up="up"
      @keydown.down="down"
      @keyup.enter= "hit"
      @keydown.esc="esc"
      @blur="showDropdown = false"
    />
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
    value: {
      type: Object,
      default () {
        // When the user types a custom value into the field, value and label are
        // identical. They only differ if a suggestion has been selected.
        return {
          value: '',
          label: ''
        }
      }
    },
    count: {
      type: Number,
      default: 8
    },
    url: {
      type: String,
      required: true
    },
    headers: {
      type: Object,
      default () {
        return {}
      }
    },
    template: String,
    dataKey: { // in the http response
      type: String,
      default: null
    },
    labelKey: { // in the list of suggestions
      type: String,
      default: 'label'
    },
    valueKey: { // in the list of suggestions
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
      val: this.value.label,
      showDropdown: false,
      current: 0,
      items: []
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
    },
    urlMode () {
      // true if user entered a url or a path
      return !!this.val.match(/^(ww|ht|\/)/i)
    }
  },

  watch: {
    value: {
      handler (val) {
        if (this.val !== val.label) {
          this.val = val.label
        }
      },
      deep: true
    }
  },

  methods: {
    focus () {
      if (!this.val && this.showDropdownOnFocus) {
        // Show suggestions when field is blank.
        this.update()
      }
    },
    update () {
      // If a suggestion has been selected, value and label are not identical.
      // We clear the field if a suggestion had been selected, so the user gets
      // feedback that they deselected the suggestion by typing something else.
      if (this.value.label !== this.value.value) {
        this.val = ''
      }
      this.$emit('input', {
        value: this.val,
        label: this.val
      })
      this.reset()
      if (this.urlMode) {
        return false
      }
      var lastVal = this.val
      setTimeout(() => {
        // only query if the value didn’t change during the delay period
        if (this.val === lastVal) this.query()
      }, this.delay)
    },
    query () {
      api.getNodes({
        url: this.url,
        headers: this.headers,
        queryParam: this.searchParam,
        queryString: fixedEncodeURIComponent(this.val)
      }).then(response => {
        // get the search term from the url
        const re = new RegExp('[&|?]' + this.searchParam + '=([^&]*)')
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
        this.$emit('input', {
          value: this.items[this.current][this.valueKey],
          label: this.items[this.current][this.labelKey]
        })
        this.reset()
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
.typeahead {
  display: inline-block;
  position: relative;
}

.typeahead .dropdown-menu {
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
}

.typeahead.open .dropdown-menu {
  display: block;
}

.typeahead .dropdown-menu > li {
  width: 100%;
}

.typeahead .dropdown-menu > li.active {
  color: #fff;
  background-color: #aaa;
}

.typeahead .dropdown-menu > li > a {
  display: inline-block;
  width: 100%;
  cursor: pointer;
}
</style>
