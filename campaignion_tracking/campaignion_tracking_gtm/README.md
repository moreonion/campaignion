# campaignion_tracking_gtm

Campaignion tracking implementation for Google Tag Manager.

## Setup

If the variable `campaignion_tracking_gtm_id` is set to a tag ID, the GTM JS
snippet will be added to every page and listen to the default tracking event
(`campaignion_tracking_default_event`). Alternatively the JS snippet can be
included in a different way, e.g. via the profile.

## Events

### Custom events

```json
{
  "event": "actionBegin",       // triggered by draftBegin
  "webform": {
    "nid": "135",               // node id
    "title": "Example",         // node public title
    "step": 1                   // previous form step
  }
},
{
  "event": "actionContinue",    // triggered by draftContinue
  "webform": {
    "nid": "135",               // node id
    "title": "Example",         // node public title
    "step": 2                   // previous form step
  }
},
{
  "event": "submission",        // triggered by submission
  "webform": {
    "nid": "135",               // node id
    "sid": "117",               // submission id
    "title": "Example"          // node public title
  }
},
{
  "event": "optin",             // triggered by submission
  "webform": {
    "nid": "135",               // node id
    "sid": "117",               // submission id
    "title": "Example",         // node public title
  },
  "email": "opt-in",            // opt-in status per channel
  "phone": "opt-out",           // opt-in status per channel
  "post": "no-change"           // opt-in status per channel
}
```

### E-commerce events

```json
{
  "event": "addToCart",         // triggered by setDonationProduct
  "ecommerce": {
    "add": {
      "products": [
        {
          "name": "Example Donation", // node public title + line item description
          "id": "106-11",       // node id + component id of the payment method selector
          "price": "10",        // donation amount
          "variant": "m",       // donation interval
          "quantity": 1,
          "currencyCode": "EUR"
        }
      ]
    }
  }
},
{
  "event": "removeFromCart",    // triggered by setDonationProduct
  "ecommerce": {
    "remove": {
      "products": [
        {
          "name": "Example Donation", // node public title + line item description
          "id": "106-11",       // node id + component id of the payment method selector
          "price": "10",        // donation amount
          "variant": "m",       // donation interval
          "quantity": 1,
          "currencyCode": "EUR"
        }
      ]
    }
  }
},
{
  "event": "checkoutBegin",     // triggered by checkoutBegin
  "ecommerce": {
    "checkout": {
      "products": [
        {
          "name": "Example Donation", // node public title + line item description
          "id": "106-11",       // node id + component id of the payment method selector
          "price": "10",        // donation amount
          "variant": "m",       // donation interval
          "quantity": 1,
          "currencyCode": "EUR"
        }
      ],
      "actionField": {
        "step": 1               // hardcoded checkout funnel step
      }
    }
  }
},
{
  "event": "checkoutEnd",       // triggered by checkoutEnd
  "ecommerce": {
    "checkout": {
      "products": [
        {
          "name": "Example Donation", // node public title + line item description
          "id": "106-11",       // node id + component id of the payment method selector
          "price": "10",        // donation amount
          "variant": "m",       // donation interval
          "quantity": 1,
          "currencyCode": "EUR"
        }
      ],
      "actionField": {
        "step": 2               // hardcoded checkout funnel step
      }
    }
  }
},
{
  "event": "purchase",          // triggered by donationSuccess
  "ecommerce": {
    "purchase": {
      "products": [
        {
          "name": "Example Donation", // node public title + line item description
          "id": "106-11",       // node id + component id of the payment method selector
          "price": "10",        // donation amount
          "variant": "m",       // donation interval
          "quantity": 1,
          "currencyCode": "EUR"
        }
      ],
      "actionField": {
        "id": 117,              // submission id
        "revenue": 10           // donation sum
      }
    }
  }
}
```
