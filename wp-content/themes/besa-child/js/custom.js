jQuery(document).ready(function(){
    var getLogoutLink = jQuery('.woocommerce-MyAccount-navigation-link--customer-logout a').attr('href');
    jQuery('.nav_logout a').attr('href', getLogoutLink);
})