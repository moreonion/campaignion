(function($) {
  Drupal.behaviors.recent_supporters = {};
  Drupal.behaviors.recent_supporters.attach = function (context, settings) {
    // start the recent-supporters polling
    if ($.fn.recentSupporters && settings.recentSupporters) {
      $('.block-campaignion-recent-supporters', context).recentSupporters({
        pollingURL: settings.recentSupporters.pollingURL,
        nodeID: settings.recentSupporters.nodeID,
        cycleSupporters: (settings.recentSupporters.cycleSupporters == "1") ? true : false,
        showCountry: (settings.recentSupporters.showCountry == "1") ? true : false,
        maxSupportersVisible: parseInt(settings.recentSupporters.visibleCount, 10),
        cycleEasing: 'easeInQuint',
        countries: Drupal.settings.recentSupporters.countries
      });
    }
  };
})(jQuery);

