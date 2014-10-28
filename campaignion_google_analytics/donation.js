(function($) {
    Drupal.behaviors.campaignion_google_analytics_donation = {};
    Drupal.behaviors.campaignion_google_analytics_donation.attach = function(context, settings) {
      // TODO guard against missing window.sessionStorage

      var config =  settings.campaignion_google_analytics;

      if (context === document) {
        ga('require', 'ec');
      }
      ga('set', '&cu',  'EUR');

      if (typeof config !== "undefined") {
        if (typeof config.impressions !== "undefined") {
          $.each(config.impressions, function(i, imp) {
            ga('ec:addImpression', {
              'id': imp.nid,
              'name': imp.title + " ["+imp.lang+"]"
            });
          });
          ga('send', 'event', 'track', 'donation');
        }

        if (typeof config.view !== "undefined") {
          var item = config.view;
          ga('ec:addProduct', {
            'id': item.nid,
            'name': item.title + " ["+item.lang+"]"
          });
          var pageNum = $('[name=\"details[page_num]\"]', context);
          var pageMax = $('[name=\"details[page_count]\"]', context);

          // reset purchase state
          sessionStorage.removeItem('sentDonationPurchase-'+item.nid);

          // on the first page we have an detail view
          if (sessionStorage.getItem('sentDonationView-'+item.nid) !== "1" && parseInt(pageNum.val(), 10) === 1) {
            ga('ec:setAction', 'detail');
            sessionStorage.setItem('sentDonationView-'+item.nid, '1');
            ga("send", "event", "donation", "view", item.title+" ["+item.nid+"]");
          }

          // on the second page we added a donation
          if (sessionStorage.getItem('sentDonationAdded-'+item.nid) !== "1" && parseInt(pageNum.val(), 10) === 2) {
            ga('ec:setAction', 'add');
            sessionStorage.setItem('sentDonationAdded-'+item.nid, '1');
            ga("send", "event", "donation", "add to cart", item.title+" ["+item.nid+"]");
          }

          // the third step is our checkout
          if (sessionStorage.getItem('sentDonationCheckoutBegin-'+item.nid) !== "1" && parseInt(pageNum.val(), 10) === 3) {
            ga('ec:setAction', 'checkout',  {
              step: 1
            });
            ga("send", "event", "donation", "checkout", item.title+" ["+item.nid+"]");
          }

          // bind on click of last step if there is an paymethod select form
          // submit is a complex option, as webform_ajax and clientside_validation
          // are involved
          if (sessionStorage.getItem('sentDonationCheckoutLast-'+item.nid) !== "1" && parseInt(pageNum.val(), 10) === parseInt(pageMax.val(), 10)) {

            $form = $('.webform-client-form #payment-method-all-forms', context).closest('form.webform-client-form', document);

            // the current webform page, does not contain a paymethod-selector.
            if ($form.length) {
              var form_id = $form.attr('id');
              var form_num = form_id.split('-')[3];
              var $button = $form.find('#edit-webform-ajax-submit-' + form_num);

              if ($button.length === 0) { // no webform_ajax.
                $button = $form.find('input.form-submit');
              }
              $button.unbind('click');
              $button.click(function() {
                var controller = $('#' + form_id + ' .payment-method-form:visible').attr('id');
                ga('ec:setAction', 'checkout',  {
                  step: 2,
                  option: controller
                });
                sessionStorage.setItem('sentDonationCheckoutLast-'+item.nid, '1');
                ga("send", "event", "donation", "checkout", item.title+" ["+item.nid+"]");
              });
            }

          }
        }
      }
    }
})(jQuery);
