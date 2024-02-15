# campaignion_vue

A bundle of libraries shared by the Vue apps in Campaignion.

As all Vue apps include their own dependencies now, since v 1.5.0, the campaignion_vue module only does two things when a Vue app is detected:

- Load `interrupt-submit.js`
- Provide localized strings for Element UI under `Drupal.settings.campaignion_vue.element_ui_strings`

## Update Element UI strings

To extract the stings from Element UI and collect them under `campaignion_vue/locale/`, run the following:

```bash
# install element-ui
yarn

# run string extraction script
yarn run build
```
