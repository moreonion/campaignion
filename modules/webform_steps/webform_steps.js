(function($) {

Drupal.behaviors.webform_steps = {};
Drupal.behaviors.webform_steps.attach = function(context, settings) {
  console.log($('.webform-steps-wrapper', context));
  $('.webform-steps-wrapper', context).click(function(event) {
    var $target = $(event.target);
    if ($target.is('span')) {
      $target.find('input').click();
    }
  });
}

})(jQuery);