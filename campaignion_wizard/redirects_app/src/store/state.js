export function createState () {
  return {
    redirects: [],
    currentRedirectIndex: null, // the item in the redirects array that is currently edited or -1 for a new item
    initialData: {}
  }
}
