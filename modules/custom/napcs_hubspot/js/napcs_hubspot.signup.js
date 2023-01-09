(function ($) {
  'use strict';
  // Initialize hubspot form on document ready.
  $(document).ready(function () {
    // See https://developers.hubspot.com/docs/methods/forms/advanced_form_options
    hbspt.forms.create({
      css: '',
      portalId: '560116',
      formId: 'a5213308-e3c8-424d-969c-96a017ded1f4',
      target: '.hbspt-form',
      // Add button and FontAwesome classes to submit.
      submitButtonClass: 'btn fa',
      translations: {
        en: {
          // Adding fa-arrow-right as a class doesn't work (inputs don't have
          // :before?) so put a unicode literal as the text.
          submitText: '\uf061',
        },
      },
      onFormReady: function($form, ctx){   
        $('input[name="email"]').attr('placeholder', 'YOUR EMAIL ADDRESS');
      }
    });
  });
})(jQuery);
