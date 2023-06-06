/* global elementId */

(function ($) {
  'use strict';

  // Put here your custom JavaScript code.
    // radio button to select service_typology
    $(document).on('change', "input[type='radio']", function () {
      var number = $("[name='service_typology']:checked").val();
      //var letter = $("[name='service_typology']:checked").val();

      var $items = $(".view-nova-workflow .views-row");
      if (number === "none") {
          $items.show();
      } else {
          $items.hide();
          $items.filter(function (index) {
              var $this = $(this);
              return $this.hasClass('typology-' + number);
          }).show();
      }
    });

})(jQuery);

    
