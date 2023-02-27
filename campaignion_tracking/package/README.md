# campaignion_tracking

Common tracking functions.

These scripts assume that the tracking snippets have already been loaded,
e.g. it expects `dataLayer` to be available in case of GTM.

## Concepts

There is shared functionality for dispatching tracking events onto a PubSub bus.
The tracker specific implementations can then subscribe to events and handle
these according to their upstream API.

There is also a fragment listener provided which checks URL fragments for
some defined tracking signals. If some are found they are consumed and
corresponding tracking events are dispatched onto the PubSub bus.

The Drupal side can provide "tracking contexts" by enriching a data
structure in the `Drupal.settings` object. The common tracking functionality
will read this and provide it as context to dispatched events.
Examples are node id, node title, donation information.

In theme specific code you can implement a callback to alter the events at
the latest possibility (just before they are sent to the upstream API).
Therefor you will need to implement a
`window.campaignion_tracking_change_msg()` function.
Currently only implemented for GTM.

The difference between tracking data and tracking context:
"data" will be sent upstream as-is, a tracking "context" can be used in the
specific implementations to generate or enhance the data sent.

## Channels

Channels implemented so far:

- `code`
- `donation`
- `webform`

## Contexts

- `node`
- `webform`
- `donation`

## Codes

- `t`: tracking event
- `w`: webform data
- `d`: donation data

## Events

Listing internal event names. Depending on the implemented service, the events
might show up under a different name there (i.e. 'setDonationProduct' → 'addToCart').

### Webform

1. 'submission': fired on thank you page
2. 'opt-in': fired on thank you page if the submission contains opt-ins

### Donation

1. 'setDonationProduct': after choosing 'amount', 'interval' and 'currency'
2. 'checkoutBegin': fired on the second (or only) form step
3. 'checkoutEnd': fired on the last form step
4. 'donationSuccess': fired on thank you page

## Development

Install `nodejs` and `yarn`, then install the needed dependencies:

    apt install nodejs yarn
    yarn install

Use the different `yarn` scripts for the development workflow:

    yarn lint
    yarn test
    yarn dev

For building a releaseable artifact (library file) use:

    yarn build

The development files are configured to be created under `build/`, the
releaseable files are created under `dist/`.

For Drupal copy the releaseable files to `../js`:

    yarn drupal

You can enable debug verbosity of the JS by setting
`sessionStorage.setItem('campaignion_debug', 1)` (then reloading).
