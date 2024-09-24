<?php 
function cits_svg_support_admin_notice() {
  $currentDate = date("Y-m-d");
  if (!isset($_COOKIE["notis-exdate"])): ?>
    <div class="notice notice-success is-dismissible qt-notis-content" >
      <h2>Thank you for using the CITS Support SVG, WEBP, ICO Media, and TTF/OTF File Upload Plugin!</h2>
      <p>Thank you for choosing our plugin! We hope it has been a great addition to your toolkit. If you’re enjoying it and would like to support our ongoing development, consider buying us a coffee and leaving a review. Your feedback and support are crucial in helping us create even better tools for you. <br> Thank you for being part of our community!</p>
      <div class="qt-notis-btns">
        <div class="qt-notis-single-btn">
          <a target="_blank" href="https://ko-fi.com/coderitsolution" class="bg-success"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/heart.svg'); ?>" alt="Buy me a coffee">Buy Me a Coffee</a>
        </div>
        <div class="qt-notis-single-btn">
          <a target="_blank" href="https://wordpress.org/support/plugin/cits-support-svg-webp-media-upload/reviews/#new-post" class="bg-worning"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/star.svg'); ?>" alt="Add a Plugin Review">Add a Plugin Review</a>
        </div>
        <div class="qt-notis-single-btn">
          <a target="_blank" href="https://wordpress.org/support/plugin/cits-support-svg-webp-media-upload/" class="bg-greay"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/envelope.svg'); ?>" alt="Support">Support</a>
        </div>
        <form method="POST">
          <?php wp_nonce_field('hideNotis', 'formNotis'); ?>
          <button type="submit" class="bg-info hide-for-6-month" name="notis-hide-btn"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/close.svg'); ?>" alt="img">Hide for 5 months</button>
        </form>
      </div>
    </div>
  <?php 
  elseif ($currentDate > $_COOKIE["notis-exdate"]): ?>
    <div class="notice notice-success is-dismissible qt-notis-content" >
      <h2>Thank you for using the CITS Support SVG, WEBP, ICO Media, and TTF/OTF File Upload Plugin!</h2>
      <p>Thank you for choosing our plugin! We hope it has been a great addition to your toolkit. If you’re enjoying it and would like to support our ongoing development, consider buying us a coffee and leaving a review. Your feedback and support are crucial in helping us create even better tools for you. <br> Thank you for being part of our community!</p>
      <div class="qt-notis-btns">
        <div class="qt-notis-single-btn">
          <a target="_blank" href="https://ko-fi.com/coderitsolution" class="bg-success"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/heart.svg'); ?>" alt="Buy me a coffee">Buy Me a Coffee</a>
        </div>
        <div class="qt-notis-single-btn">
         <a target="_blank" href="https://wordpress.org/support/plugin/cits-support-svg-webp-media-upload/reviews/#new-post" class="bg-worning"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/star.svg'); ?>" alt="Add a Plugin Review">Add a Plugin Review</a>
        </div>
        <div class="qt-notis-single-btn">
          <a target="_blank" href="https://wordpress.org/support/plugin/cits-support-svg-webp-media-upload/" class="bg-greay"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/envelope.svg'); ?>" alt="Support">Support</a>
        </div>
        <form method="POST">
          <?php wp_nonce_field('hideNotis', 'formNotis'); ?>
          <button type="submit" class="bg-info hide-for-6-month" name="notis-hide-btn"><img src="<?php echo esc_url(CITS_PLUGIN_URL . 'admin/images/close.svg'); ?>" alt="Hide">Hide for 5 months</button>
        </form>
      </div>
    </div>
  <?php endif;
}
add_action('admin_notices', 'cits_svg_support_admin_notice');

function cits_notice_hide(){
  if (isset($_POST['formNotis']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['formNotis'])), 'hideNotis')) {
    if (!defined('ABSPATH')) exit;

    $currentDate = date("Y-m-d");
    $exDate = date('Y-m-d', strtotime("+5 months", strtotime($currentDate)));

    if (!isset($_COOKIE["notis-exdate"]) || $currentDate > $_COOKIE["notis-exdate"]) {
      setcookie("notis-exdate", $exDate, time() + (5 * 30 * 24 * 60 * 60), '/');
      echo "<script>setTimeout(function(){ location.reload(); }, 2000);</script>";
    }
  }
}
add_action('init', 'cits_notice_hide');
?>
