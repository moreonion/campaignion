# campaignion_tracking_ga4

Campaignion tracking implementation for Google Analytics 4 (gtag).

## Setup

If the variable `campaignion_tracking_ga4_id` is set to a tag ID, the gtag JS
snippet will be added to every page and listen to the default tracking event
(`campaignion_tracking_default_event`). Alternatively the JS snippet can be
included in a different way, e.g. via the profile.

## Events

### Custom events

```json
{
  "event": "begin_action",      // triggered by draftBegin
  "params": {
    "nid": "135",               // node id
    "action_title": "Example",  // node public title
    "step": 1                   // previous form step
  }
},
{
  "event": "continue_action",   // triggered by draftContinue
  "params": {
    "nid": "135",               // node id
    "action_title": "Example",  // node public title
    "step": 2                   // previous form step
  }
},
{
  "event": "submission",        // triggered by submission
  "params": {
    "nid": "135",               // node id
    "sid": "117",               // submission id
    "action_title": "Example"   // node public title
  }
},
{
  "event": "optin",             // triggered by submission
  "params": {
    "nid": "135",               // node id
    "sid": "117",               // submission id
    "action_title": "Example",  // node public title
    "email": "opt-in",          // opt-in status per channel
    "phone": "opt-out",         // opt-in status per channel
    "post": "no-change"         // opt-in status per channel
  }
}
```

### Recommended events

```json
{
  "event": "add_to_cart",       // triggered by setDonationProduct
  "params": {
    "currency": "EUR",
    "value": 10,                // donation sum
    "items": [
      {
        "item_name": "Example Donation", // node public title + line item description
        "item_id": "106-11",    // node id + component id of the payment method selector
        "price": "10",          // donation amount
        "item_variant": "m",    // donation interval
        "quantity": 1
      }
    ]
  }
},
{
  "event": "remove_from_cart",  // triggered by setDonationProduct
  "params": {
    "currency": "EUR",
    "value": 10,                // donation sum
    "items": [
      {
        "item_name": "Example Donation", // node public title + line item description
        "item_id": "106-11",    // node id + component id of the payment method selector
        "price": "10",          // donation amount
        "item_variant": "m",    // donation interval
        "quantity": 1
      }
    ]
  }
},
{
  "event": "begin_checkout",    // triggered by checkoutBegin
  "params": {
    "currency": "EUR",
    "value": 10,                // donation sum
    "items": [
      {
        "item_name": "Example Donation", // node public title + line item description
        "item_id": "106-11",    // node id + component id of the payment method selector
        "price": "10",          // donation amount
        "item_variant": "m",    // donation interval
        "quantity": 1
      }
    ]
  }
},
{
  "event": "add_shipping_info", // triggered by checkoutEnd
  "params": {
    "currency": "EUR",
    "value": 10,                // donation sum
    "items": [
      {
        "item_name": "Example Donation", // node public title + line item description
        "item_id": "106-11",    // node id + component id of the payment method selector
        "price": "10",          // donation amount
        "item_variant": "m",    // donation interval
        "quantity": 1
      }
    ]
  }
},
{
  "event": "purchase",          // triggered by donationSuccess
  "params": {
    "transaction_id": "117",    // submission id
    "currency": "EUR",
    "value": 10,                // donation sum
    "items": [
      {
        "item_name": "Example Donation", // node public title + line item description
        "item_id": "106-11",    // node id + component id of the payment method selector
        "price": "10",          // donation amount
        "item_variant": "m",    // donation interval
        "quantity": 1
      }
    ]
  }
}
```
