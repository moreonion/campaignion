(function($) {
    Drupal.behaviors.campaignion_google_analytics = {};
    Drupal.behaviors.campaignion_google_analytics.attach = function(context, settings) {
        var config =  settings.campaignion_google_analytics;
        if (typeof config !== 'undefined') {
            if (typeof config.thank_you !== "undefined") {
                ga("send", "event", "webform", "submitted");
            }
            if (typeof config.payment !== 'undefined') {
                ga('require', 'ecommerce');
                ga('ecommerce:addTransaction', {id: config.payment.id});
                ga('ecommerce:addItem', config.payment);
                ga('ecommerce:send');
            }
        }
    }
})(jQuery);
