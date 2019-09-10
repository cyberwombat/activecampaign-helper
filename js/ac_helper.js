/*  globals jQuery, ach_params */

;(function(acs, $) {
  // Skeleton function to send an email back to the AC helper
  acs.storeEmail = function(email, cb = function() {}) {
    // eslint-disable-line no-unused-vars
    $.ajax({
      url: ach_params.ajax_url,
      type: 'post',
      data: {
        action: 'ach_email',
        security: ach_params.security.email,
        email: email
      },
      success: function(response) {
        log('Success store email ' + email)
        cb(null, response)
      },
      error: function(err) {
        log('Error store email ' + email)
        cb(err)
      }
    })
  }
  // Toggle tracking preferences
  acs.setTracking = function(flag, cb = function() {}) {
    // eslint-disable-line no-unused-vars
    $.ajax({
      url: ach_params.ajax_url,
      type: 'post',
      data: {
        action: 'ach_track',
        security: ach_params.security.track,
        tracking: !!flag
      },
      success: function(response) {
        log('Success setting tracking ' + (flag ? 'on' : 'off'))
        cb(null, response)
      },
      error: function(err) {
        log('Error setting tracking')
        cb(err)
      }
    })
  }
  // Send event
  acs.sendEvent = function(name, value, email, cb = function() {}) {
    // eslint-disable-line no-unused-vars
    $.ajax({
      url: ach_params.ajax_url,
      type: 'post',
      data: {
        action: 'ach_event',
        security: ach_params.security.event,
        name: name,
        value: value,
        email: email
      },
      success: function(response) {
        log('Success sending ' + name + ' event')
        cb(null, response)
      },
      error: function(err) {
        log('Error sending ' + name + ' event')
        cb(err)
      }
    })
  }

  // Preset params for tracker
  acs.setIdentity = function() {

  }

  // Set tracker object
  acs.loadTracker = function() {
    // Create base pgo object if not yet created
    window.prismGlobalObjectAlias = 'pgo'
    window.pgo = window.pgo || function() {
      (window.pgo.q = window.pgo.q || []).push(arguments)
    }

    if (ach_params.site_tracking || /(^|; )ach_enable_tracking=([^;]+)/.test(document.cookie)) {
      acs.loadTrackerCode()
    }

    // Load basic params
    window.pgo.l = (new Date()).getTime()
    window.pgo('setAccount', ach_params.trackid)
    window.pgo('setTrackByDefault', true)
    if (ach_params.user_email) {
      log(ach_params.user_email ? 'Set ' + ach_params.user_email + ' as email in tracking' : 'No email set')

      window.pgo('setEmail', ach_params.user_email)
    }

    window.pgo('process')
  }

  // AC tracking when no native AC plugin is present
  acs.loadTrackerCode = function() {
    log('Loading custom tracker from ' + (ach_params.site_tracking ? 'settings' : 'cookie'))

    // Set cookie pref
    var expiration = new Date(new Date().getTime() + 1000 * 60 * 60 * 24 * 30)
    document.cookie = 'ach_enable_tracking=1; expires= ' + expiration + '; path=/'

    // Load script
    var t = document.createElement('script')
    t.async = true
    t.type = 'text/javascript'
    t.src = 'https://prism.app-us1.com/prism.js'
    var ts = document.getElementsByTagName('script')
    if (ts.length) {
      ts[0].parentNode.appendChild(t)
    } else {
      var th = document.getElementsByTagName('head')
      th.length && th[0].appendChild(t)
    }
  }

  // Debug
  function log() {
    if (!ach_params.debug) return
    try {
      console.log.apply(console, arguments)
    } catch (e) {}
  }
})((window.acs = window.acs || {}), jQuery)

window.acs.loadTracker()
