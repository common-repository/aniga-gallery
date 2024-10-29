<div class="postmetadata alt">
    <table width="100%" align="center" border="0" cellpadding="0" cellspacing="0">
    <tr>
    <td width="50%" align="left" valign="top">
    
    <?php if(isset($prev_id)){ ?>
    
        <a href="<?php echo aniga_get_permalink($this->id, $prev_id, 'pid') ?>" title="<? _e('previous', 'aniga'); ?>">
        <img src="<?php echo $prev_path . 'thumb_' . $prev_filename; ?>" height="<?php echo 0.75*get_option('aniga_thumb_size'); ?>px"  class="gallery_thumb_border" align="middle" alt="<? _e('previous', 'aniga'); ?>" />
        </a>
                
    <?php } else echo '&nbsp;'; ?>
    
    </td>
    <td width="50%" align="right" valign="top">
    
    <?php if(isset($next_id)){ ?>
    
        <a href="<?php echo aniga_get_permalink($this->id, $next_id, 'pid'); ?>" title="<? _e('next', 'aniga'); ?>">
        <img src="<?php echo $next_path . 'thumb_' . $next_filename; ?>" height="<?php echo 0.75*get_option('aniga_thumb_size'); ?>px"  class="gallery_thumb_border" align="middle" alt="<? _e('next', 'aniga'); ?>" />
        </a>
                
    <?php } else echo '&nbsp;'; ?>
    
    </td>
    </tr></table>
</div>