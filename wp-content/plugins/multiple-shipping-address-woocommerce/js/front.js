jQuery(document).ready(function () {
  jQuery('body').on('click', '.form_option_billing', function () {
    jQuery('body').addClass('ocwma_billing_popup_body');
    jQuery('body').append(
      '<div class="ocma_loading"><img src="' +
        object_name +
        '/images/loader.gif" class="ocma_loader"></div>'
    );
    var loading = jQuery('.ocma_loading');
    loading.show();

    var id = jQuery(this).data('id');
    var current = jQuery(this);
    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      data: 'action=productscommentsbilling&popup_id_pro=' + id,
      success: function (response) {
        var loading = jQuery('.ocma_loading');
        loading.remove();
        jQuery('#ocwma_billing_popup').css('display', 'block');
        jQuery('#ocwma_billing_popup').html(response);
      },
      error: function () {
        alert('Error occured');
      },
    });
    return false;
  });
  var modal = document.getElementById('ocwma_billing_popup');
  var span = document.getElementsByClassName('ocwma_close')[0];
  jQuery(document).on('click', '.ocwma_close', function () {
    jQuery('#ocwma_billing_popup').css('display', 'none');
    jQuery('body').removeClass('ocwma_billing_popup_body');
  });
  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = 'none';
      jQuery('body').removeClass('ocwma_billing_popup_body');
    }
  };

  jQuery(document).ready(function () {
    jQuery('body').on('click', '.form_option_edit', function () {
      jQuery('body').addClass('ocwma_billing_popup_body');
      jQuery('body').append(
        '<div class="ocwqv_loading"><img src="' +
          object_name +
          '/images/loader.gif" class="ocwqv_loader"></div>'
      );
      var loading = jQuery('.ocwqv_loading');
      loading.show();

      var id = jQuery(this).data('id');
      var eid = jQuery(this).data('eid-bil');
      var current = jQuery(this);
      jQuery.ajax({
        url: ajax_url,
        type: 'POST',
        data:
          'action=productscommentsbilling&popup_id_pro=' +
          id +
          '&eid-bil=' +
          eid,
        dataType: 'JSON',
        success: function (response) {
          var loading = jQuery('.ocwqv_loading');
          var html = response[0].html;
          loading.remove();
          jQuery('#ocwma_billing_popup').css('display', 'block');
          jQuery('#ocwma_billing_popup').html(html);
          jQuery('#billing_country').trigger('change');
          jQuery('#billing_state').trigger('change');
        },
        error: function () {
          alert('Error occured');
        },
      });
      return false;
    });

    var modal = document.getElementById('ocwma_billing_popup');
    var span = document.getElementsByClassName('ocwma_close')[0];
    jQuery(document).on('click', '.ocwma_close', function () {
      jQuery('#ocwma_billing_popup').css('display', 'none');
      jQuery('body').removeClass('ocwma_billing_popup_body');
    });

    window.onclick = function (event) {
      if (event.target == modal) {
        modal.style.display = 'none';
        jQuery('body').removeClass('ocwma_billing_popup_body');
      }
    };
  });

  jQuery(document).ready(function () {
    jQuery('.ocwma_select').change(function () {
      var sid = jQuery(this).val();
      var current = jQuery(this);
      jQuery.ajax({
        url: ajax_url,
        dataType: 'json',
        type: 'POST',
        data: 'action=productscommentsbilling_select&sid=' + sid,
        success: function (response) {
          console.log(response);
          jQuery('#billing_first_name').val(response.billing_first_name);
          jQuery('#billing_last_name').val(response.billing_last_name);
          jQuery('#billing_company').val(response.billing_company);
          jQuery('#billing_country').val(response.billing_country).change();
          jQuery('#billing_address_1').val(response.billing_address_1);
          jQuery('#billing_address_2').val(response.billing_address_2);
          jQuery('#billing_city').val(response.billing_city);
          jQuery('#billing_state').val(response.billing_state).change();
          jQuery('#billing_postcode').val(response.billing_postcode);
          jQuery('#billing_phone').val(response.billing_phone);
          jQuery('#billing_email').val(response.billing_email);
        },
        error: function () {
          alert('Error occured');
        },
      });
    });
  });

  jQuery(document).ready(function () {
    jQuery('.ocwma_select_shipping').change(function () {
      var sid = jQuery(this).val();
      var current = jQuery(this);
      jQuery.ajax({
        url: ajax_url,
        dataType: 'json',
        type: 'POST',
        data: 'action=productscommentsshipping_select&sid=' + sid,
        success: function (response) {
          jQuery('#shipping_first_name').val(response.shipping_first_name);
          jQuery('#shipping_last_name').val(response.shipping_last_name);
          jQuery('#shipping_company').val(response.shipping_company);
          jQuery('#shipping_country').val(response.shipping_country).change();
          jQuery('#shipping_address_1').val(response.shipping_address_1);
          jQuery('#shipping_address_2').val(response.shipping_address_2);
          jQuery('#shipping_city').val(response.shipping_city);
          jQuery('#shipping_state').val(response.shipping_state).change();
          jQuery('#shipping_postcode').val(response.shipping_postcode);
        },
        error: function () {
          alert('Error occured');
        },
      });
    });
  });

  jQuery('body').on('click', '.form_option_shipping', function () {
    jQuery('body').addClass('ocwma_shipping_popup_body');
    jQuery('body').append(
      '<div class="ocwqv_loading"><img src="' +
        object_name +
        '/images/loader.gif" class="ocwqv_loader"></div>'
    );
    var loading = jQuery('.ocwqv_loading');
    loading.show();

    var id = jQuery(this).data('id');

    var current = jQuery(this);
    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      data: 'action=productscommentsshipping&popup_id_pro=' + id,
      success: function (response) {
        var loading = jQuery('.ocwqv_loading');
        loading.remove();
        jQuery('#ocwma_shipping_popup').css('display', 'block');
        jQuery('#ocwma_shipping_popup').html(response);
      },
      error: function () {
        alert('Error occured');
      },
    });
    return false;
  });
  var modal = document.getElementById('ocwma_shipping_popup');
  var span = document.getElementsByClassName('ocwma_close')[0];
  jQuery(document).on('click', '.ocwma_close', function () {
    jQuery('#ocwma_shipping_popup').css('display', 'none');
    jQuery('body').removeClass('ocwma_shipping_popup_body');
  });
  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = 'none';
      jQuery('body').removeClass('ocwma_shipping_popup_body');
    }
  };
});

jQuery(document).ready(function () {
  jQuery('body').on('click', '.form_option_ship_edit', function () {
    jQuery('body').addClass('ocwma_shipping_popup_body');
    jQuery('body').append(
      '<div class="ocma_loading"><img src="' +
        object_name +
        '/images/loader.gif" class="ocma_loader"></div>'
    );
    var loading = jQuery('.ocma_loading');
    loading.show();
    var id = jQuery(this).data('id');
    var eid = jQuery(this).data('eid-ship');
    var current = jQuery(this);
    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      data:
        'action=productscommentsshipping&popup_id_pro=' +
        id +
        '&eid-ship=' +
        eid,
      success: function (response) {
        var loading = jQuery('.ocma_loading');
        loading.remove();
        jQuery('#ocwma_shipping_popup').css('display', 'block');
        jQuery('#ocwma_shipping_popup').html(response);
        jQuery('#shipping_country').trigger('change');
        jQuery('#shipping_state').trigger('change');
      },
      error: function () {
        alert('Error occured');
      },
    });
    return false;
  });
  var modal = document.getElementById('ocwma_shipping_popup');
  var span = document.getElementsByClassName('ocwma_close')[0];
  jQuery(document).on('click', '.ocwma_close', function () {
    jQuery('#ocwma_shipping_popup').css('display', 'none');
    jQuery('body').removeClass('ocwma_shipping_popup_body');
  });
  window.onclick = function (event) {
    if (event.target == modal) {
      modal.style.display = 'none';
      jQuery('body').removeClass('ocwma_shipping_popup_body');
    }
  };

  jQuery('body').on('click', '#oc_add_billing_form_submit', function () {
    jQuery('#oc_add_billing_form').attr('onsubmit', 'return false;');
    jQuery('#oc_add_billing_form input').removeClass('ocwma_inerror');
    jQuery('#oc_add_billing_form select').removeClass('ocwma_inerror');

    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      data:
        jQuery('#oc_add_billing_form').serialize() +
        '&action=ocwma_validate_billing_form_fields',
      dataType: 'JSON',
      success: function (response) {
        var added = response['added'];
        var field_errors = response.field_errors;
        if (added == 'false') {
          jQuery.each(field_errors, function (i, item) {
            jQuery('#oc_add_billing_form #' + i).addClass('ocwma_inerror');
          });
        } else {
          location.reload();
        }
      },
      error: function () {
        alert('Error occured');
      },
    });
  });

  jQuery('body').on('click', '#oc_add_shipping_form_submit', function () {
    jQuery('#oc_add_shipping_form').attr('onsubmit', 'return false;');
    jQuery('#oc_add_shipping_form input').removeClass('ocwma_inerror');
    jQuery('#oc_add_shipping_form select').removeClass('ocwma_inerror');

    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      data:
        jQuery('#oc_add_shipping_form').serialize() +
        '&action=ocwma_validate_shipping_form_fields',
      dataType: 'JSON',
      success: function (response) {
        var added = response['added'];
        var field_errors = response.field_errors;
        if (added == 'false') {
          jQuery.each(field_errors, function (i, item) {
            jQuery('#oc_add_shipping_form #' + i).addClass('ocwma_inerror');
          });
        } else {
          location.reload();
        }
      },
      error: function () {
        alert('Error occured');
      },
    });
  });

  jQuery('body').on('click', '#oc_edit_billing_form_submit', function () {
    jQuery('#oc_edit_billing_form').attr('onsubmit', 'return false;');
    jQuery('#oc_edit_billing_form input').removeClass('ocwma_inerror');
    jQuery('#oc_edit_billing_form select').removeClass('ocwma_inerror');

    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      data:
        jQuery('#oc_edit_billing_form').serialize() +
        '&action=ocwma_validate_edit_billing_form_fields',
      dataType: 'JSON',
      success: function (response) {
        var added = response['added'];
        var field_errors = response.field_errors;

        if (added == 'false') {
          jQuery.each(field_errors, function (i, item) {
            jQuery('#oc_edit_billing_form #' + i).addClass('ocwma_inerror');
          });
        } else {
          location.reload();
        }
      },
      error: function () {
        alert('Error occured');
      },
    });
  });

  jQuery('body').on('click', '#oc_edit_shipping_form_submit', function () {
    jQuery('#oc_edit_shipping_form').attr('onsubmit', 'return false;');
    jQuery('#oc_edit_shipping_form input').removeClass('ocwma_inerror');
    jQuery('#oc_edit_shipping_form select').removeClass('ocwma_inerror');

    jQuery.ajax({
      url: ajax_url,
      type: 'POST',
      data:
        jQuery('#oc_edit_shipping_form').serialize() +
        '&action=ocwma_validate_edit_shipping_form_fields',
      dataType: 'JSON',
      success: function (response) {
        var added = response['added'];
        var field_errors = response.field_errors;

        if (added == 'false') {
          jQuery.each(field_errors, function (i, item) {
            jQuery('#oc_edit_shipping_form #' + i).addClass('ocwma_inerror');
          });
        } else {
          location.reload();
        }
      },
      error: function () {
        alert('Error occured');
      },
    });
  });
});
