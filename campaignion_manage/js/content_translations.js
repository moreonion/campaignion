(function($) {

$(document).ready(function() {
  $('.campaignion-manage-content-listing .node-translations').hide().each(function() {
    $translations = $(this);
    $tset = $translations.prev();

    $showHideLink = $('<a class="show" href="#">' + Drupal.t('show translations') + '</a>').click(function(event) {
      event.preventDefault();
      if ($translations.is(':visible')) {
        $translations.hide();
        $showHideLink.html(Drupal.t('show translations')).addClass('show').removeClass('hide');
      } else {
        $translations.show();
        $showHideLink.html(Drupal.t('hide translations')).addClass('hide').removeClass('show');
      }
    }).appendTo($tset.find('.content'));
  });
});

})(jQuery);
