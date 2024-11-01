<?php
$limit = 1;
$images = $this->fetch_image_files_paginated(1, $limit);
$total_left = $images->found_posts;
if ($images->post_count == 0) {
  echo 'Hết';
  exit;
}
$image = $images->posts[0];
$attachment_id = $image->ID;
$file_paths = $this->get_attachment_paths_by_id($attachment_id);
?>
<div class="wrap">
<h2>Convert Images to WebP</h2>
<div style="margin: 1em 0;padding: 0.1em 1em;background: #ffe8dd;border: 1px solid #ff6b6b;border-radius: 4px;box-shadow: 0px 0px 10px -3px rgba(0,0,0,.5);">
  <code>You can stop the process by closing this window.</code>
</div>
<p>Count left: <?php echo $total_left ?></p>
<?php if ($file_paths): ?>
  <h3>ID: #<?php echo esc_html($attachment_id); ?></h3>
    <ul>
        <?php
        $hasWebp = false;
        foreach ($file_paths as $size => $path):
            $finfo = pathinfo($path);
            $result = $this->convert_to_webp($path, $finfo['extension']);
            if ($result) :
              $hasWebp = true;
        ?>
              <li><strong><?php echo esc_html($size); ?>:</strong> <?php echo esc_html($path); ?><?php echo ($finfo['extension'] !== 'webp') ? '.webp' : ''; ?> => <strong style="color: green;">OK</strong></li>
            <?php else: ?>
              <li><strong><?php echo esc_html($size); ?>:</strong> <?php echo esc_html($path); ?><?php echo ($finfo['extension'] !== 'webp') ? '.webp' : ''; ?> => <strong style="color: red;">Fail</strong></li>
            <?php endif; ?>
        <?php
        endforeach;

        if ($hasWebp) {
          update_post_meta( $attachment_id, 'has_webp', 1);
        }
        ?>
    </ul>
<?php else: ?>
    <p>No attachment found with ID <?php echo esc_html($attachment_id); ?></p>
<?php endif; ?>
<p>
  <img alt="Loading..." src="data:image/gif;base64,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" />
</p>
<?php if ($total_left > $limit) : ?>
<div id="countdown"></div>
<script type="text/javascript">
jQuery(function($){
  var timeleft = 1;
  var downloadTimer = setInterval(function(){
      if(timeleft <= 0){
          clearInterval(downloadTimer);
          document.getElementById("countdown").innerHTML = 'Generating...';
          window.location.reload();
      } else {
          document.getElementById("countdown").innerHTML = 'Auto convert after <strong>' + timeleft + "</strong> seconds";
      }
      timeleft -= 1;
  }, 1000);
});
</script>
<?php endif; ?>
</div>