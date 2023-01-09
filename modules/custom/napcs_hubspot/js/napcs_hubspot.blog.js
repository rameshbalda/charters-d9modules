(function ($) {
  'use strict';
  // Initialize hubspot form on document ready.
  $(document).ready(function () {
    // See https://developers.hubspot.com/docs/methods/forms/advanced_form_options
    hbspt.forms.create({
      css: '',
      portalId: '560116',
      formId: 'e0e96e97-8a67-4802-b19b-0ce548c32c10',
      target: '.hubspot-blog-signup',
      // Add button and FontAwesome classes to submit.
      //submitButtonClass: 'btn fa',
      translations: {
        en: {
          // Adding fa-arrow-right as a class doesn't work (inputs don't have
          // :before?) so put a unicode literal as the text.
          submitText: 'Subscribe',
        },
      },
      onFormReady: function($form, ctx){   
        $('input[name="email"]').attr('placeholder', 'YOUR EMAIL ADDRESS');
      }
    });
  });
})(jQuery);
