
/**
 * Implementation of Drupal behavior.
 */
(function($) {
Drupal.behaviors.campaignion_manage_filter = {};
Drupal.behaviors.campaignion_manage_filter.attach = function(context) {
  var $wrapper = $('.manage-filter-form', context);
  var $addables = $wrapper.find('.form-checkboxes.filter-add input');

  // generate filter list
  var $ul = $('<ul class="manage-filter-dropdown listdropdown-menu"></ul>');
  $addables.each(function () {
    var $addToggle = $(this);
    $('<li class="filter-toggle filter-disabled">' + $addToggle.siblings('.option').text() + '</li>')
    .click(function() {
      $addToggle.prop('checked', true).change();
    }).appendTo($ul);
  }).parents('.form-type-checkboxes').hide();
  $wrapper.prepend($ul);

  // initialize hidden/shown state + add close button
  $filterFieldsets = $wrapper.find('.filter-fieldsets').children();
  $filterFieldsets.filter('.filter-removable').each(function () {
    var $filterFieldset = $(this);
    $filterFieldset.append('<span class="manage-filter-remove">remove</span>');
  });

  // close button handler
  $('.manage-filter-remove', $wrapper).click(function() {
    $(this).closest('fieldset').hide()
    .find('input.filter-active-toggle').prop('checked', false).change();
  });

  // filter add handler
  // get fieldset, show it and enable filter active checkbox
  $('.filter-toggle', $wrapper).click(function() {
    var $self = $(this);
    var filterFieldsetId = $self.attr('data-filter-for');
    var $filterFieldset = $('#' + filterFieldsetId);
    $('input.filter-active-toggle', $filterFieldset).attr('checked', true);
  });

  $('ul.manage-filter-dropdown', context).listdropdown({
    defaultText: Drupal.t('Add filter')
  });

  $wrapper.find('.ctools-auto-submit-click').click(function() {
    $(this).mousedown();
  }).hide();


  var UpdateLiveFilters = function (event) {
    var $checkbox = $('input[name="filter[0][live_filters]"]');
    var $input = $('input[name="filter[filter][0][values][name]"]');
    var $submit = $('.manage-filter-form .form-submit');

    if ($checkbox.attr('checked') === 'checked') {
      $input.removeClass('ctools-auto-submit-exclude');
      $submit.attr('style', 'display: none;');
      Drupal.behaviors.CToolsAutoSubmit.attach(context);
    } else {
      $input.addClass('ctools-auto-submit-exclude');
      $input.removeClass('ctools-auto-submit-processed');
      $input.unbind();
      $submit.attr('style', '');
    }
  }
  // toggle live filters.
  $('input[name="filter[0][live_filters]"]').click(UpdateLiveFilters);
  $(document).ready(UpdateLiveFilters);
};

})(jQuery);
