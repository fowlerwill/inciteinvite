<form id="iiteam_edit" action="" method="post">
    <div class="form-group">
        <div class="form-group">
            <label for="iiteam_name">Team Name <strong>*</strong></label>
            <input class="form-control" type="text" name="iiteam_name" value="<?php echo the_title(); ?>">
        </div>

        <div class="form-group">
            <label for="iiteam_description">Team Description <strong>*</strong></label>
            <textarea class="form-control" name="iiteam_description" rows="3"><?php echo get_the_content(); ?></textarea>
        </div>

        <div class="form-group">
            <label for="iiteam_timezone">Timezone</label>

            <select name="iiteam_timezone" id="">
                <?php
                $tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                $teamtz = get_post_meta(get_the_ID(), 'iitimezone', true);
                (empty($teamtz)) ? $teamtz = 'America/Edmonton' : $teamtz;

                $s = '';
                foreach($tzlist as $tz) {
                    if ($tz == $teamtz) {
                        $s = 'selected';
                    } else {
                        $s = '';
                    }
                    printf('<option value="%s" %s>%s</option>', $tz, $s, $tz);
                }
                ?>
            </select>
        </div>
        <input type="hidden" name="teamid" value="<?php the_ID(); ?>"/>
        <?php wp_nonce_field( 'edit_iiteam_'.get_the_ID()); ?>
        <input  class="btn btn-default" type="submit" name="submit" value="Save"/>
    </div>
</form>