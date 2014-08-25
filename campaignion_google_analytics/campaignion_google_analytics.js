(function($) {
    Drupal.behaviors.campaignion_google_analytics = {};
    Drupal.behaviors.campaignion_google_analytics.attach = function(context, settings) {
        var config =  settings.campaignion_google_analytics;
        if (typeof config !== 'undefined' &&
            typeof config.payment !== 'undefined') {
            ga('require', 'ec');
            ga('set', '&cu', config.payment.currency);
            ga('ec:addProduct', config.payment);
            ga('ec:setAction', 'purchase', {id: config.payment.id});
            ga('send', 'event', 'Ecommerce', 'Purchase');
        }
        ga('send', 'event', 'webform', 'submitted');

    }
})(jQuery);
