/**
 * This page has global JS functions for the environment
 */

/**
 * @var csrfToken
 */

/**
 * @var csrfTokenUpdateInProgress
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
 * @var dataObjectsClosedUrl
 */

/**
 * @var dataObjectsOpenUrl
 */

/**
 * @var sessionTimeout
 */

/**
 * @var inactivityTimeout
 */

/**
 * @var userInactivityCounter
 */

/**
 * @var sessionTimeoutTimestamp
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

let csrfTokenUpdateInProgress = false;

function updateCsrfToken(connector) {
    // Prevent concurrent requests
    if (csrfTokenUpdateInProgress) return;

    csrfTokenUpdateInProgress = true;

    let csrfUrl;

    if (connector == null || connector.toLowerCase() === 'closed') {
        csrfUrl = dataObjectsClosedUrl + "csrf-token";
    } else {
        csrfUrl = dataObjectsOpenUrl + "csrf-token";
    }

    $.getJSON(csrfUrl, function (jsonData) {
        if (jsonData && jsonData['csrfToken']) {
            // Update global vars
            if (jsonData['csrfToken']) {
                csrfToken = jsonData['csrfToken'];
            }
            if (jsonData['session_timeout']) {
                sessionTimeout = jsonData['session_timeout'];
            }
            if (jsonData['session_timeout_timestamp']) {
                sessionTimeoutTimestamp = jsonData['session_timeout_timestamp'];
            }
            if (jsonData['inactivity_timeout']) {
                inactivityTimeout = jsonData['inactivity_timeout'];
            }

            // Update all forms using CSRF
            $('input[name="_csrfToken"]').val(csrfToken);
        }
    }).fail(function () {
        // Optional: log or notify error
    }).always(function () {
        // Always clear the flag after success or failure
        csrfTokenUpdateInProgress = false;
    });
}


/**
 * Function to convert Unix timestamp to the desired format
 *
 * @param timestamp
 * @returns {string}
 */
function formatTimestamp(timestamp) {
    // Create a Date object from the Unix timestamp (multiply by 1000 to convert from seconds to milliseconds)
    var date = new Date(timestamp * 1000);

    // Extract microseconds (we are working with the decimal part of the timestamp)
    var microseconds = timestamp % 1;
    microseconds = Math.floor(microseconds * 1000000); // Convert to microseconds

    // Get components of the date in the desired format
    var year = date.getFullYear();
    var month = date.toLocaleString('default', {month: 'short'}); // Get abbreviated month name
    var day = ('0' + date.getDate()).slice(-2); // Zero-padded day
    var hours = ('0' + date.getHours()).slice(-2); // Zero-padded hours
    var minutes = ('0' + date.getMinutes()).slice(-2); // Zero-padded minutes
    var seconds = ('0' + date.getSeconds()).slice(-2); // Zero-padded seconds

    // Return formatted date and time string
    return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}.${microseconds}`;
}

$(document).ready(function () {
    let sessionTimeoutCheckerIntervalId;
    let inactivityTimeoutCheckerIntervalId;
    let tillSessionTimeoutLogout;
    let tillInactivityTimeoutLogout;

    //automatic logout a User on session timeout or inactivity
    if ((sessionTimeout > 0) || inactivityTimeout > 0) {
        startTimeoutChecker();
    }

    function startTimeoutChecker() {
        var runInterval;
        if (mode === 'dev') {
            runInterval = 1;
        } else {
            runInterval = 10;
        }

        if (sessionTimeout > 0) {
            sessionTimeoutCheckerIntervalId = setInterval(function () {
                var currentTimestamp = Math.floor(Date.now() / 1000);
                tillSessionTimeoutLogout = sessionTimeoutTimestamp - currentTimestamp;

                //hard logout if exceeded the session limit
                if (currentTimestamp >= sessionTimeoutTimestamp) {
                    clearInterval(sessionTimeoutCheckerIntervalId);
                    window.location.href = logoutUrl;
                    return;
                }

                if (mode === 'dev') {
                    $('.auto-logout-countdown').text(tillSessionTimeoutLogout);
                    //console.log("Logging out in " + tillSessionTimeoutLogout)
                }

            }, (runInterval * 1000));
        }

        if (inactivityTimeout > 0) {
            inactivityTimeoutCheckerIntervalId = setInterval(function () {
                tillInactivityTimeoutLogout = inactivityTimeout - userInactivityCounter;

                //hard logout if they have exceeded their inactivity limit
                userInactivityCounter = userInactivityCounter + runInterval;
                if (userInactivityCounter >= inactivityTimeout) {
                    clearInterval(inactivityTimeoutCheckerIntervalId);
                    window.location.href = logoutUrl;
                    return;
                }

                if (mode === 'dev') {
                    $('.inactivity-counter').text(tillInactivityTimeoutLogout);
                    //console.log("Logging out in " + tillInactivityTimeoutLogout)
                }

            }, (runInterval * 1000));
        }

        //called whenever a user is active
        function resetUserInactivityCounter() {
            userInactivityCounter = 0;

            //if a user is still active, update CSRF, which will extend Session
            if (tillInactivityTimeoutLogout <= 30 || tillSessionTimeoutLogout <= 30) {
                updateCsrfToken('closed');
            }

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
