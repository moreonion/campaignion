# Campaignion match to target send (campaignion_m2t_send)

This module enables delayed sending for match to target actions. It keeps a send status for each m2t message in the database to guarantee each one is sent only once.

*By default, nothing is sent, each node has to be enabled for sending individually.*


## Limitations

The scope of this module is limited to single-target postcode datasets (UK datasets).

- Refetching based on postcode: This simplifies the code for refetching as it doesn’t need to deal with different selector configs.
- Target matching: With a single target, we can assume that the only target at the time of submissions becomes the only target at the time of sending.


## Configuration

### Enabling nodes

The variable `campaignion_m2t_send_enabled_nodes` contains an array of nids. The script only sends messages from these nodes. The variable can be modified with drush in multiple ways:

```bash
# Set to a specific value.
drush vset --exact campaignion_m2t_send_enabled_nodes --format=json '[123, 456]'
# Append a node.
drush ev '$name = "campaignion_m2t_send_enabled_nodes"; $v = variable_get($name); $v[] = 789; variable_set($name, $v);'
```

### Cron-timings

The cron-job can be configured in the ultimate cron settings. By default, it runs once a minute (or as often the cron-job is called) between 8-23h (server’s local time). See [ultimate cron’s contrab syntax](https://www.drupal.org/docs/7/modules/ultimate-cron/crontab).


## Features

### Trickle sending

During each run of the cron-job the script goes through all the scheduled messages in order. For each message, it does a coin-flip (50:50 chance) to decide whether to send the email, or stop sending emails for this email address (for this cron-run). This means, on average, one email is sent per cron-run and target email address.


### Updating data

Before sending a message, target data is fetched again from the e2t-api and the message is updated accordingly. Certain parts of the message are updated:

- The `To` part of the email (`{title} {first_name} {last_name} <{email}>`).
- `Dear …` in the message content is replaced with `Dear {salutation}`.


### Logging

The DB table `campaignion_m2t_send` table keeps a record of which messages have been sent and to which email address. NB: It keeps entries for all M2T nodes, even when they are not (and never will be) configured to send.
