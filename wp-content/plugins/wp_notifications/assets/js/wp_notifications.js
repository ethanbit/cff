jQuery(document).ready(function () {
  jQuery('a.changegroupname').click(function () {
    let groupname = jQuery(this).data('groupname');
    let groupid = jQuery(this).data('groupid');
    jQuery('input#groupname').val(groupname);
    jQuery('input#groupid').val(groupid);
    jQuery('#groupEditModal').modal();
  });

  jQuery('#addnewgroup').click(function () {
    jQuery('input#action').val('addnewgroup');
    jQuery('#groupEditModal').modal();
  });

  jQuery('.sendnotification').click(function () {
    var userid = jQuery(this).data('userid');
    var user = jQuery(this).data('user');
    jQuery('#msgModal #userid').val(userid);
    jQuery('#msgModal #msgtouser').html(user);
    jQuery('#msgModal').modal();
  });
  jQuery('#msgModal').on('hidden.bs.modal', function (e) {
    jQuery('#sendtouser .alert').hide().html('');
  });

  jQuery('#sendtouser').submit(function () {
    var formData = jQuery(this).serialize();
    console.log(formData);
    jQuery.ajax({
      type: 'POST',
      url: ajaxurl,
      data: formData,
      success: function (data) {
        jQuery('#sendtouser .alert').show().html(data);
      },
    });
    return false;
  });
});
