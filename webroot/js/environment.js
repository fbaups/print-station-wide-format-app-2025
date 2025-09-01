/**
 * This page has global JS functions for the environment
 */

/**
 * @var csrfToken
 */

/**
 * @var homeUrl
 */

/**
 * @var pageUrl
 */

/**
 * @var logoutUrl
 */

/**
 * @var dataObjectsUrl
 */

/**
 * @var sessionTimeout
 */

/**
 * @var userInactivityCounter
 */

/**
 * @var autoLogoutTimestamp
 */

/**
 * @var mode
 */

/**
 * @var modeBanner
 */

/**
 * Update the CSRF Token
 */
function updateCsrfToken() {
    var csrfUrl = dataObjectsUrl + "csrf-token";

    $.getJSON(csrfUrl, function (jsonData) {
        csrfToken = jsonData['csrfToken'];
    }).fail(function () {

    })
}

$(document).ready(function () {
    let activityCheckerIntervalId;

    //automatic logout on User inactivity
    if (sessionTimeout > 0) {
        startUserSessionTimeoutChecker();
    }

    function startUserSessionTimeoutChecker() {
        var runInterval;
        if (mode === 'dev') {
            runInterval = 1;
        } else {
            runInterval = 10;
        }

        //set up the setInterval method to run every second (1000 milliseconds = 1 second)
        activityCheckerIntervalId = setInterval(function () {
            var currentTimestamp = Math.floor(Date.now() / 1000);
            var tillLogout = autoLogoutTimestamp - currentTimestamp;

            //hard logout if exceeded the autoLogoutTimestamp
            if (currentTimestamp >= autoLogoutTimestamp) {
                //console.log(`Exceeded the logout timestamp ${autoLogoutTimestamp}.`)
                clearInterval(activityCheckerIntervalId);
                window.location.href = logoutUrl;
                return;
            }

            //hard logout if they have exceeded their inactivity limit
            userInactivityCounter = userInactivityCounter + runInterval;
            if (userInactivityCounter >= sessionTimeout) {
                //console.log(`Exceeded the inactivity counter of ${userInactivityCounter} seconds.`)
                clearInterval(activityCheckerIntervalId);
                window.location.href = logoutUrl;
                return;
            }

            if (modeBanner && mode === 'dev') {
                $('.auto-logout-countdown').text(tillLogout);
            }

        }, (runInterval * 1000));

        //called whenever a user is active
        function resetUserInactivityCounter() {
            userInactivityCounter = 0;
        }

        //array of DOM events that should be interpreted as user activity.
        var activityEvents = ['mousedown', 'mousemove', 'keydown', 'scroll', 'touchstart',];

        //add these events to the document.
        //register the activity function as the listener parameter.
        activityEvents.forEach(function (eventName) {
            document.addEventListener(eventName, resetUserInactivityCounter, true);
        });
    }

});
