(function ($, drupalSettings) {
  var facebookButtonSelector = '.' + drupalSettings.napcs_share.facebookButtonClass;

  // Gets called once FB sdk is loaded.
  window.fbAsyncInit = function() {
    FB.init({
      appId            : '2039931376219968',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v2.11'
    });

    $(facebookButtonSelector).click(function (e) {
      FB.ui({
        method: 'share',
        mobile_iframe: true,
        href: $(this).attr('href'),
      }, function (response) {});
      e.preventDefault();
    });
  };

  // Inject FB sdk script tag.
  (function(d, s, id){
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) {return;}
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
})(jQuery, drupalSettings);
