/* global Drupal, jQuery */

import regeneratorRuntime from 'regenerator-runtime'
import { Client } from './client'
import { Validator } from './validator'

var $ = jQuery
var client = null
Drupal.behaviors.campaignion_loqate = {}
Drupal.behaviors.campaignion_loqate.attach = function (context, settings) {
  if (!settings.loqate) {
    return
  }
  if (!client) {
    client = new Client(settings.loqate)
  }
  $('.loqate-validate', context).each(function (index, wrapper) {
    (new Validator(client, settings.loqate)).bind(wrapper).validate()
  })
}
