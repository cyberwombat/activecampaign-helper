/* globals jQuery, ach_params */

// Seleton function to send an email back to the AC helper
jQuery(document).ready(function () {
  // Leave this alone
  if (!global.ach_email) return
  jQuery.ajax({
    url: ach_params.ajax_url,
    type: 'post',
    data: {
      action: 'ach_track',
      security: ach_params.nonce,
      email: global.ach_email
    },
    success: function (response) {
      // WP will return the email on success
      // window.alert(response)
    }
  })
})
