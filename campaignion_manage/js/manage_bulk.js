
/**
 * Implementation of Drupal behavior.
 */
(function($) {
Drupal.behaviors.campaignion_manage_bulk = {};
Drupal.behaviors.campaignion_manage_bulk.attach = function(context) {
  $('form.campaignion-manage-bulkops .bulkops', context).each(function() {
    var $wrapper = $(this);
    var $dialogBg = $('.campaignion-dialog-wrapper');
    var defaultZ = $('.campaignion-dialog-wrapper').css('z-index');

    $wrapper.hide().addClass('bulk-dialog');

    // Button for opening the dialog.
    $('<a class="button" id="bulk-edit-button"  href="#">' + Drupal.t('Bulk edit') + '</a>')
    .click(function(e) {
      e.preventDefault();
      e.stopPropagation();

      $wrapper.show();
      $dialogBg.css('z-index', 500).show();
    }).insertBefore($wrapper);

    // Button to hide the dialog.
    $('<div id="bulk-dialog-close">' + Drupal.t('Close') + '</div>')
    .click(function(e) {
      $wrapper.hide();
      $dialogBg.css('z-index', defaultZ).hide();
    }).appendTo($wrapper.children('legend'));

    var $radios = $wrapper.find('.bulkops-radios');
    $radios.find('input[type=radio]').change(function() {
      var $active = $wrapper.find('.bulkops-op-' + $(this).val());

      $wrapper.find('.bulkops-op').hide().find('label').removeClass('active');
      $active.show().find('label').addClass('active');
    }).change();

    $radios.find('label').after('<span class="bulk-question-mark">?</span>');

    if ($.fn.popover) {
      $radios.find('.bulk-question-mark').each(function() {
        var $self = $(this);
        var op = $self.siblings('input').val()
        $self.popover({
          content: $wrapper.find('.bulkops-op-' + op).find('.help-text').html(),
        });
      });
    }

    $radios.on('show.bs.popover', function(e) {
      var $self = $(e.target);
      $radios.find('.bulk-question-mark').not($self).popover('hide');
    });
  });
};

})(jQuery);
