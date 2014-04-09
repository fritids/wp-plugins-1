via: http://davidwalsh.name/track-errors-google-analytics


Track JavaScript Errors with Google Analytics

Google Analytics has always been more than a hit counter and demographic tool -- you could build a career out of being a Google Analytics analyst.  You can measure ad campaign effectiveness, track how far into a desired page flow (think advertisement to cart to checkout) users get, and set browser and locale support based your user's information.

But that's all stuff for the suits, not us devs.  What us nerds can use Google Analytics for, however, is error tracking via custom events.  Here's a quick look at how I've implemented error checking in analytics:

// Track basic JavaScript errors
window.addEventListener('error', function(e) {
    _gaq.push([
        '_trackEvent',
        'JavaScript Error',
        e.message,
        e.filename + ':  ' + e.lineno,
        true
    ]);
});

// Track AJAX errors (jQuery API)
$(document).ajaxError(function(e, request, settings) {
    _gaq.push([
        '_trackEvent',
        'Ajax error',
        settings.url,
        e.result,
        true
    ]);
});


Now when you go into Google Analytics, you can view the custom event information along with other site stats.  Of course you'll tell the marketing people those aren't really error, they're features, but that's another story.  Consider using Google Analytics for to track site errors -- you can thank me later.