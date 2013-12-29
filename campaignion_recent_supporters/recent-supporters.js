(function($) {
  Drupal.behaviors.recent_supporters = {};
  Drupal.behaviors.recent_supporters.attach = function (context, settings) {
    // set locale after plugin is loaded and before the recent-supporters
    // container is initialized
    if ($.fn.timeago) {
      // English (Template)
      $.timeago.settings.strings = {
        prefixAgo: "",
        prefixFromNow: "",
        suffixAgo: "ago",
        suffixFromNow: "from now",
        seconds: "%d seconds",
        minute: "about a minute",
        minutes: "%d minutes",
        hour: "about an hour",
        hours: "about %d hours",
        day: "a day",
        days: "%d days",
        month: "about a month",
        months: "%d months",
        year: "about a year",
        years: "%d years",
        wordSeparator: " ",
        numbers: []
      };
    }

    // start the recent-supporters polling
    if ($.fn.recentSupporters && settings.recentSupporters) {
      $('.block-campaignion-recent-supporters', context).recentSupporters({
        pollingURL: settings.recentSupporters.pollingURL,
        nodeID: settings.recentSupporters.nodeID,
        cycleSupporters: (settings.recentSupporters.cycleSupporters == "1") ? true : false,
        showCountry: (settings.recentSupporters.showCountry == "1") ? true : false,
        maxSupportersVisible: parseInt(settings.recentSupporters.visibleCount, 10),
        cycleEasing: 'easeInQuint'
      });
    }
  };
})(jQuery);

