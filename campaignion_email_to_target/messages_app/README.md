# messages_app

> Front end for the messages step.

## Installation

``` bash
yarn
```

## Development mode

For working on the Vue app you don’t have to build the bundle each time and have it served by Drupal. When running `yarn dev`, the `Drupal` global is mocked and dummy data from `test/unit/fixtures/example-data.js` is used.
For requests to the e2t API to work you need to pass in a valid token via environment variable (you’ll find a token at `Drupal.settings.campaignion_email_to_target.endpoints['e2t-api'].token` on the messages step page)

``` bash
# serve with hot reload at localhost:8080
export E2T_API_TOKEN="my.token" && yarn dev
```

## Building

``` bash
yarn build
```

The JavaScript is bundled to `campaignion_email_to_target/js/messages_app/app.vue.min.js`, the CSS ends up in `campaignion_email_to_target/css/messages_app/e2t_messages_app.min.css` and can be overridden by the theme layer.

## Testing

``` bash
# run unit tests
yarn test
```
