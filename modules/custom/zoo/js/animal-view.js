(function ($, Drupal, window) {

  'use strict';

  let createConvertWeightLink = function (weight) {
    let unit = $(weight).find('.weight-unit')[0].innerText;
    let link = $(weight).find('.weight-convert')[0];
    if (unit ==='kg') {
      $(link).html('<a class="weight-convert-link" data-convert-to="lbs" href="#">' + Drupal.t('(Convert to pounds)')) + '</a>';
    }
    else if (unit ==='lbs') {
      $(link).html('<a class="weight-convert-link" data-convert-to="kg" href="#">' + Drupal.t('(Convert to kilograms)')) + '</a>';
    }
  };

  let convertWeight = function (weight, convert_to) {
    let conversion_factor = 2.20462262;
    let amount = $(weight).find('.weight-amount')[0];

    if (convert_to ==='lbs') {
      alert(($(amount).text() * conversion_factor).toFixed(1) + ' lbs');
    }
    else {
      alert(($(amount).text() / conversion_factor).toFixed(1) + ' kgs');
    }
  };

  /**
   * Provide a switcher to change kg to lbs
   */
  Drupal.behaviors.zooAnimalWeightSwitcher = {
    attach: function (context) {
      let weights = $(context).find('.animal .weight');
      weights.each(function(index, weight) {
        createConvertWeightLink(weight);
      });
      $(context).find('.weight-convert-link').each(function (index, link) {
        $(link).bind('click', function() {
          convertWeight($(this).parent().parent(), $(this).attr('data-convert-to'));
          return false;
        });
      });
    }
  };

})(jQuery, Drupal, window);
