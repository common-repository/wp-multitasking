<?php
if (isset($_POST['action']) and 'save' == $_POST['action']) {
  if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'security_nonce')) {
      $fields = ['wpmt_psi_image_lazyload', 'wpmt_psi_image_webp', 'wpmt_psi_score', 'wpmt_psi_wprocket_css_inline'];
      foreach ($fields as $field) {
          if (isset($_REQUEST[$field]) and !empty($_REQUEST[$field])) {
              update_option($field, $_REQUEST[$field]);
          } else {
              delete_option($field);
          }
      }
  }
}
?>
<div class="wrap">
<h2>Google PSI Settings</h2>
<?php
if (isset($_POST['action']) and 'save' == $_POST['action']) {
  echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
}

$image_webp = intval(get_option('wpmt_psi_image_webp'));
$image_lazyload = intval(get_option('wpmt_psi_image_lazyload'));
$psi_score = intval(get_option('wpmt_psi_score'));
$wprocket_css_inline = intval(get_option('wpmt_psi_wprocket_css_inline'));
?>
<form id="frmWpmtPSI" method="post">
  <table class="form-table" width="100%">
      <tr>
          <td>
              <label>Convert Images to WebP:</label>
          </td>
          <td>
              <p><a class="button button-large button-primary" href="<?php echo esc_url('admin.php?page=wpmt_psi&convert=webp') ?>" target="_blank">START</a></p>
              <div>Don't worry! WebP generation is no error occur and does not affect existing images.</div>
          </td>
      </tr>
      <tr>
          <td>
              <label for="wpmt_psi_image_webp">WebP Format:</label>
          </td>
          <td>
              <select name="wpmt_psi_image_webp" id="wpmt_psi_image_webp" class="regular-text">
                  <option value="0" <?php echo ($image_webp != 1) ? "selected" : ""; ?>>Disabled</option>
                  <option value="1" <?php echo ($image_webp == 1) ? "selected" : ""; ?>>Enabled</option>
              </select>
          </td>
      </tr>
      <tr>
          <td style="vertical-align: top;">
              <label for="wpmt_psi_image_lazyload">Lazyload for images:</label>
          </td>
          <td>
              <select name="wpmt_psi_image_lazyload" id="wpmt_psi_image_lazyload" class="regular-text">
                  <option value="0" <?php echo ($image_lazyload != 1) ? "selected" : ""; ?>>Disabled</option>
                  <option value="1" <?php echo ($image_lazyload == 1) ? "selected" : ""; ?>>Enabled</option>
              </select><br/>
              <div>Please disable lazyload images from WP Rocket and other plugins.</div>
          </td>
      </tr>
      <tr>
          <td>
              <label for="wpmt_psi_score">PSI Green Score:</label>
          </td>
          <td>
              <select name="wpmt_psi_score" id="wpmt_psi_score" class="regular-text">
                  <option value="0" <?php echo ($psi_score != 1) ? "selected" : ""; ?>>Disabled</option>
                  <option value="1" <?php echo ($psi_score == 1) ? "selected" : ""; ?>>Enabled</option>
              </select>
          </td>
      </tr>
      <tr>
          <td style="vertical-align: top;">
              <label for="wpmt_psi_wprocket_css_inline">WP Rocket CSS Inline:</label>
          </td>
          <td>
              <select name="wpmt_psi_wprocket_css_inline" id="wpmt_psi_wprocket_css_inline" class="regular-text">
                  <option value="0" <?php echo ($wprocket_css_inline != 1) ? "selected" : ""; ?>>Disabled</option>
                  <option value="1" <?php echo ($wprocket_css_inline == 1) ? "selected" : ""; ?>>Enabled</option>
              </select><br/>
              <p>
                This option required:<br/>
                - CSS minify and combine with WP Rocket<br/>
                - Activate plugin <a href="https://docs.wp-rocket.me/article/61-disable-page-caching" target="_blank">WP Rocket | Disable Page Caching</a>
              </p>
          </td>
      </tr>
      <tr>
          <td style="vertical-align: top;">Recommends:</td>
          <td>
              <p>HTML Minify with plugin <a href="https://wordpress.org/plugins/autoptimize/" target="_blank">Autoptimize</a></p>
              <p>JS minify you can use any plugin</p>
          </td>
      </tr>
  </table>
  <div class="submit">
      <input name="save" type="submit" value="Save changes" class="button button-large button-primary" />
      <input type="hidden" name="action" value="save" />
      <?php wp_nonce_field('security_nonce'); ?>
  </div>
</form>
</div>