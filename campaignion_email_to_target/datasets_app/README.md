# campaignion_vue_project

Template for Vue apps in Campaignion.
Replace `my_vue_app` with your app’s name.

Webpack compiles js and css into the respective module subdirectories:
```
campaignion_my_module
 │
 ├── my_vue_app (= root of this template)
 │    ├── build
 │    ├── config
 │    ├── src
 │    ...
 │    └── package.json
 │
 ├── js (drupal_add_js from here)
 │    ├── my_vue_app
 │    │    ├── my_vue_app.vue.min.js
 │    │    └── my_vue_app.vue.js.map
 │    └── some_behavior.js
 │
 ├── css (drupal_add_css from here)
 │    ├── my_vue_app
 │    │    └── my_vue_app.css
 │    └── my_module_styles.js
 │
 ├── campaignion_my_module.info
 └── campaignion_my_module.module
```

You don’t have to add the assets campaignion_vue provides, it adds them when it detects Vue apps that have the extensions `.vue.min.js`. So all you have to do is enable campaignion_vue and
```
drupal_add_js(
  drupal_get_path('module', 'campaignion_my_module') . '/js/my_vue_app/app.vue.min.js',
  {'scope' => 'footer', 'preprocess' => FALSE}
);
```
and campaignion_vue will automatically add all the assets you need.

## Dev mode

If you need to preview your app inside Campaignion, you have to run `npm run build` or `yarn build` and reload the page in the browser.
For faster development using hot module replacement you can run `yarn dev` and visit `localhost:8080`. All you need from the `Drupal` global is stubbed via `build/drupal-fixture.js`. API calls to Drupal won’t work though.

## Production mode

In production mode, Webpack is configured to leave certain vendor libraries out of the bundle and take them from the `campaignion_vue` global provided by the `campaignion_vue` module.

## Build Setup

``` bash
# install dependencies
npm install

# serve with hot reload at localhost:8080
npm run dev

# build for production with minification
npm run build

# build for production and view the bundle analyzer report
npm run build --report

# run unit tests
npm run unit

# run e2e tests
npm run e2e

# run all tests
npm test
```

For detailed explanation on how things work, checkout the [guide](http://vuejs-templates.github.io/webpack/) and [docs for vue-loader](http://vuejs.github.io/vue-loader).
