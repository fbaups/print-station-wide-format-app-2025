/**
 * @var homeUrl
 */
$(window).on('load', function () {
    console.log('Initiating Slideshow.');

    //warm up the page
    SlideshowPlayer.startup();


    var checkForNewDataMs = 5000;
    setInterval(function () {
        SlideshowPlayer.load();
    }, checkForNewDataMs);

});

if (typeof SlideshowPlayer !== "object") {
    SlideshowPlayer = {};
}

(function () {
    SlideshowPlayer.jsonUrl = homeUrl + 'theatre/slideshow-player-sequence/sequence.json?rnd=' + Math.random();
    SlideshowPlayer.jsonData = {};
    SlideshowPlayer.jsonChecksum = '';

    SlideshowPlayer.cacheEventIds = {};
    SlideshowPlayer.cacheMediaClipIds = [];

    SlideshowPlayer.hasUserInteracted = false;

    SlideshowPlayer.audioState = 'off';

    SlideshowPlayer.counter = 0;

    SlideshowPlayer.crossfadeMs = 250;

    SlideshowPlayer.timeOnServer = 0; //will be updated, initial value for fallback;
    SlideshowPlayer.timeOnLocal = 0; //will be updated, initial value for fallback;
    SlideshowPlayer.timerCompensation = 0; //will be updated, initial value for fallback;


    SlideshowPlayer.startup = function () {
        $('#tracks').off('click.interacted touchstart.interacted').on('click.interacted touchstart.interacted', function () {
            SlideshowPlayer.hasUserInteracted = true;
        });

        $('#track-audio')
            .off('mouseenter.audio').on('mouseenter.audio', function () {
            $('#track-audio-icons').show();
        })
            .off('mouseleave.audio').on('mouseleave.audio', function () {
            $('#track-audio-icons').hide();
        });

        $('#track-audio-off').off('click.audio touchstart.audio').on('click.audio touchstart.audio', function () {
            $(this).hide();
            $('#track-audio-on').show();
            SlideshowPlayer.audioState = 'on';
        });

        $('#track-audio-on').off('click.audio touchstart.audio').on('click.audio touchstart.audio', function () {
            $(this).hide();
            $('#track-audio-off').show();
            SlideshowPlayer.audioState = 'off';
        });

    }

    SlideshowPlayer.load = function () {
        $.when(
            SlideshowPlayer.getTrackData()
        ).done(function () {
            //load the tracks
            SlideshowPlayer.loadTracks();

        });
    }

    SlideshowPlayer.getTrackData = function () {
        return $.getJSON(SlideshowPlayer.jsonUrl, function (jsonData) {
            SlideshowPlayer.jsonData = jsonData;
            SlideshowPlayer.timeOnServer = parseInt(SlideshowPlayer.jsonData['created_ts']);
            SlideshowPlayer.timeOnLocal = Date.now();
            SlideshowPlayer.timerCompensation = SlideshowPlayer.timeOnLocal - SlideshowPlayer.timeOnServer;
            //console.log('timeOnServer', SlideshowPlayer.timeOnServer);
            //console.log('timeOnLocal', SlideshowPlayer.timeOnLocal);
            //console.log("timerCompensation", SlideshowPlayer.timerCompensation);
        });
    }

    SlideshowPlayer.loadTracks = function () {
        //decision needs to be made if to truly load tracks.
        if (SlideshowPlayer.jsonChecksum === SlideshowPlayer.jsonData['checksum']) {
            //track data has not changed so exit.
            //console.log("Nothing new to load");
            return;
        } else {
            //console.log("New data to load");
        }


        //update the jsonChecksum
        SlideshowPlayer.jsonChecksum = SlideshowPlayer.jsonData['checksum'];

        var tracks = SlideshowPlayer.jsonData['tracks'];
        var tmpClipIds = [];
        $.each(tracks, function (trackId, clips) {
            $.each(clips, function (clipNumber, clipProperties) {
                //only add new clips
                if (!_.includes(SlideshowPlayer.cacheMediaClipIds, clipProperties['id'])) {
                    //cache for event timeoutIds
                    SlideshowPlayer.cacheEventIds[clipProperties['id']] = {};
                    SlideshowPlayer.cacheEventIds[clipProperties['id']]['start'] = clipProperties['start'];
                    SlideshowPlayer.cacheEventIds[clipProperties['id']]['stop'] = clipProperties['stop'];
                    //simple pointer cache
                    SlideshowPlayer.cacheMediaClipIds.push(clipProperties['id'])
                    SlideshowPlayer.queueMediaClip(trackId, clipProperties)
                }
                tmpClipIds.push(clipProperties['id']);
            });
        });

        //clean out cacheMediaClipIds
        SlideshowPlayer.cacheMediaClipIds = $.grep(SlideshowPlayer.cacheMediaClipIds, function(value) {
            return $.inArray(value, tmpClipIds) !== -1;
        });

        //clean out dom holder of clips
        $('#media-clips-holder ').children().each(function () {
                var clipId = $(this).attr('id');
                if (!_.includes(SlideshowPlayer.cacheMediaClipIds, clipId)) {
                    $(this).remove();
                }
            }
        );

        //clean out queued clip events if the clip has been removed
        $.each(SlideshowPlayer.cacheEventIds, function (clipId, clipData) {
            if (!_.includes(SlideshowPlayer.cacheMediaClipIds, clipId)) {
                //remove the events
                var trulyNow = Date.now() - SlideshowPlayer.timerCompensation;
                var doRemove = false;
                if (trulyNow < clipData['start']) {
                    //console.log("will remove as in the future", clipId);
                    doRemove = true;
                } else if (trulyNow > clipData['stop']) {
                    //console.log("will remove as should have completed", clipId);
                    doRemove = true;
                } else {
                    //console.log("cant remove as already started", clipId);
                }
                //remove the references in the cache
                if (doRemove) {
                    $.each(clipData['timeoutIds'], function (key, timeoutId) {
                        //console.log(timeoutId);
                        clearTimeout(timeoutId);
                    });
                    delete SlideshowPlayer.cacheEventIds[clipId];
                }
            } else {
                //console.log("keep", clipId);
            }
        });

        //start counter for debugging purposes
        if (SlideshowPlayer.counter === 0) {
            $('#track-timecode').text('00:00:00');
            setInterval(function () {
                SlideshowPlayer.counter++;
                var timecode = new Date(SlideshowPlayer.counter * 1000).toISOString().substr(11, 8);
                $('#track-timecode').text(timecode);
            }, 1000);
        }
    }

    SlideshowPlayer.queueMediaClip = function (trackId, clipProperties) {
        var unixTS = SlideshowPlayer.timeOnLocal;
        var clipStartTS = parseInt(clipProperties['start']);
        var clipStopTS = parseInt(clipProperties['stop']);

        var countdownStart = clipStartTS - unixTS + SlideshowPlayer.timerCompensation;
        var movePlayheadTo = 0;
        if (countdownStart < 0) {
            movePlayheadTo = (countdownStart / 1000) * (-1);
            countdownStart = 0;
        }

        var countdownStop = clipStopTS - unixTS + SlideshowPlayer.timerCompensation;
        if (countdownStop < 0) {
            //media clip has expired so no need to queue clip
            return;
        }

        var dataAttributes = {
            'data-trackId': trackId,
            'data-movePlayheadTo': movePlayheadTo,
            'data-countdownStart': countdownStart,
            'data-countdownStop': countdownStop,
            'data-format': clipProperties['format']
        }

        //store the media clip in the dom holder
        if (clipProperties['format'].toLowerCase() === 'video') {
            SlideshowPlayer.snippetVideo(clipProperties, dataAttributes);
        } else if (clipProperties['format'].toLowerCase() === 'image') {
            SlideshowPlayer.snippetImage(clipProperties, dataAttributes);
        } else {
            return;
        }

        SlideshowPlayer.eventsStartAndStopClip(clipProperties['id']);
    }

    SlideshowPlayer.eventsStartAndStopClip = function (clipId) {
        var mediaClip = $('#' + clipId);

        if (!_.has(SlideshowPlayer.cacheEventIds, clipId)) {
            SlideshowPlayer.cacheEventIds[clipId] = {};
        }

        var trackId = mediaClip.attr('data-trackId');
        var clipFormat = mediaClip.attr('data-format');
        var movePlayheadTo = parseFloat(mediaClip.attr('data-movePlayheadTo'));

        var countdownStart = parseFloat(mediaClip.attr('data-countdownStart'));
        var countdownStop = parseFloat(mediaClip.attr('data-countdownStop'));

        SlideshowPlayer.cacheEventIds[clipId]['timeoutIds'] = [];

        mediaClip.attr('data-crossfadein-start', countdownStart);
        mediaClip.attr('data-crossfadeout-stop', countdownStop);

        /*
        ===== 4 events =====
        1) Fade clip in
        2) Start playing clip (videos)
        3) Stop playing clip (videos)
        4) Fade clip out (immediately after stopping)
         */

        //cross-fade time (extends both ends of the clip by the fade time)
        var crossFadeInStart = countdownStart - SlideshowPlayer.crossfadeMs;
        var crossFadeInStop = countdownStart;
        var crossFadeOutStart = countdownStop;
        var crossFadeOutStop = countdownStop + SlideshowPlayer.crossfadeMs;

        //need to reduce cross-fade-in if cross-fade starts in the negative
        var crossFadeInDurationMs;
        var crossFadeOutDurationMs;
        if (crossFadeInStart < 0 && countdownStart >= 0) {
            crossFadeInStart = 0;
            crossFadeInDurationMs = countdownStart;
        } else if (crossFadeInStart < 0 && countdownStart < 0) {
            crossFadeInStart = 0;
            crossFadeInDurationMs = 0;
        } else {
            crossFadeInDurationMs = SlideshowPlayer.crossfadeMs;
        }

        //no need to check cross-fade-out
        crossFadeOutDurationMs = SlideshowPlayer.crossfadeMs;

        let timeoutId;

        //1) Fade clip in
        timeoutId = setTimeout(function () {
            mediaClip.css('display', 'none').detach();
            $('#track-' + trackId).append(mediaClip);

            if (clipFormat === 'video') {
                mediaClip.prop('currentTime', movePlayheadTo).trigger('pause');
            }

            mediaClip.fadeIn(crossFadeInDurationMs);

        }, crossFadeInStart);
        SlideshowPlayer.cacheEventIds[clipId]['timeoutIds'].push(timeoutId);


        //2) Start playing clip (videos)
        if (clipFormat === 'video') {
            timeoutId = setTimeout(function () {

                //only unmute if user has interacted with dom
                if (SlideshowPlayer.hasUserInteracted) {
                    if (mediaClip.attr('data-muted') === 'false' || mediaClip.attr('data-muted') === false) {
                        mediaClip.prop('muted', false);
                    }
                }

                mediaClip.trigger('play');
            }, countdownStart);
            SlideshowPlayer.cacheEventIds[clipId]['timeoutIds'].push(timeoutId);
        }


        //3) Stop playing clip (videos)
        if (clipFormat === 'video') {
            timeoutId = setTimeout(function () {
                mediaClip.prop('muted', true).trigger('pause');
            }, countdownStop);
            SlideshowPlayer.cacheEventIds[clipId]['timeoutIds'].push(timeoutId);
        }


        //4) Fade clip out (immediately after stopping)
        timeoutId = setTimeout(function () {
            mediaClip.fadeOut(crossFadeOutDurationMs, function () {
                mediaClip.detach();
                $('#media-clips-holder').append(mediaClip);
            });
        }, crossFadeOutStart);
        SlideshowPlayer.cacheEventIds[clipId]['timeoutIds'].push(timeoutId);
    }

    SlideshowPlayer.snippetVideo = function (clipProperties, dataAttributes) {
        var str = '<video class="">' +
            '<source src="" type="video/mp4">' +
            '</video>'

        var fittingClass;
        switch (clipProperties['fitting']) {
            case 'fit':
                fittingClass = 'fitting-fit'
                break;
            case 'fill':
                fittingClass = 'fitting-fill'
                break;
            case 'stretch':
                fittingClass = 'fitting-stretch'
                break;
            case 'scale-down':
                fittingClass = 'fitting-scale-down'
                break;
            default:
                fittingClass = 'fitting-original'
        }

        var video = $(str).addClass(clipProperties['class'])
            .attr('id', clipProperties['id'])
            .addClass(fittingClass)
            .prop('muted', true) //will be unmuted if user has interacted with DOM
            .attr('data-muted', clipProperties['muted'])
            .prop('loop', clipProperties['loop'])
            .prop('autoplay', clipProperties['autoplay'])
            .trigger('pause') //trigger a pause
            .prop('currentTime', 0) //move to start of video so ready to go
            .find('source')
            .attr('src', clipProperties['source'])
            .parent();

        if (typeof dataAttributes === 'object') {
            $.each(dataAttributes, function (attrKey, attrValue) {
                video.attr(attrKey, attrValue);
            });
        }

        //store in the dom holder
        $('#media-clips-holder').append(video);

        return video;
    }

    SlideshowPlayer.snippetImage = function (clipProperties, dataAttributes) {
        var str = '<img src="" alt="" class="">';

        var fittingClass;
        switch (clipProperties['fitting']) {
            case 'fit':
                fittingClass = 'fitting-fit'
                break;
            case 'fill':
                fittingClass = 'fitting-fill'
                break;
            case 'stretch':
                fittingClass = 'fitting-stretch'
                break;
            case 'scale-down':
                fittingClass = 'fitting-scale-down'
                break;
            default:
                fittingClass = 'fitting-original'
        }

        var image = $(str).addClass(clipProperties['class'])
            .attr('id', clipProperties['id'])
            .attr('alt', "static image")
            .addClass(fittingClass)
            .attr('src', clipProperties['source']);

        if (typeof dataAttributes === 'object') {
            $.each(dataAttributes, function (attrKey, attrValue) {
                image.attr(attrKey, attrValue);
            });
        }

        //store in the dom holder
        $('#media-clips-holder').append(image);

        return image;
    }


}());
