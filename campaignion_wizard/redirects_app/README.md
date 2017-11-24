# Personalized redirect app

## API

### Initial data via Drupal.settings

### Get nodes list
`GET <nodes_endpoint>?q=<search term or nid>`

JSON Response:
``` json
{
  "values": [
    {
      "value": "node/21",
      "label": "My fancy node title (21)"
    },
    ...
  ]
}
```

### Persist data on form submit


## Build Setup

``` bash
# install dependencies
yarn install

# serve with hot reload at localhost:8080
yarn dev

# build for production with minification
yarn build

# run unit tests
yarn unit

# run e2e tests
yarn e2e

# run all tests
yarn test
```

For detailed explanation on how things work, checkout the [guide](http://vuejs-templates.github.io/webpack/) and [docs for vue-loader](http://vuejs.github.io/vue-loader).
