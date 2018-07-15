/**
 * @file
 * Change some labels according to the display setting.
 */

(function ($) {

  "use strict";
  Drupal.behaviors.campaignionOptInLabels = {
    attach: function (context, settings) {
      context = $(context).get(0);
      if (!settings.campaignionOptIn || !settings.campaignionOptIn.labels) {
        return;
      }
      for (var checkbox_id in settings.campaignionOptIn.labels) {
        var s = settings.campaignionOptIn.labels[checkbox_id];
        var checkbox = context.querySelector(checkbox_id);
        if (checkbox) {
          var display = checkbox.form.querySelector(s.display_id);
          var labels = checkbox.form.querySelectorAll('label[for="' + checkbox.id + '"]');
          display.addEventListener('change', function() {
            labels.forEach(function(label) {
              if (!label.classList.contains('itoggle')) {
                label.innerHTML = s.labels[display.value];
              }
            });
          });
        }
      }
    }
  };

})(jQuery);
