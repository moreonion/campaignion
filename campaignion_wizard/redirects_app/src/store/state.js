import {emptyRedirect} from '@/utils/defaults'

export function createState () {
  return {
    redirects: [],
    defaultRedirect: emptyRedirect(),
    currentRedirectIndex: null, // the item in the redirects array that is currently edited or -1 for a new item
    initialData: {
      redirects: [],
      defaultRedirect: {}
    }
  }
}
