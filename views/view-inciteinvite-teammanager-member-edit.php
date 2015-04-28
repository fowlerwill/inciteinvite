<form class="iiteammember_edit form-inline" action="" method="post">
    <?php if( empty( $iiteammember_user_login ) ) { ?>

        <div class="form-group">
            <label for="user_login">Member username <strong>(cannot be changed)</strong></label>
            <input class="form-control" placeholder="Jamie Doe" type="text" name="user_login" value="">
        </div>

    <?php } else { ?>

        <h5><?= $iiteammember_user_login ?></h5>

        <input type="hidden" name="user_login" value="<?= $iiteammember_user_login ?>">

        <div class="form-group">
            <label for="display_name">Display Name</label>
            <input class="form-control" type="text" name="display_name" value="<?= $iiteammember_display_name ?>">
        </div>

    <?php } ?>



    <div class="form-group">
        <label for="iiteammember_user_email">Email</label>
        <input class="form-control" placeholder="Jamie@Doe.com" type="text" name="user_email" value="<?php echo ( isset( $iiteammember_user_email) ? $iiteammember_user_email : null ) ?>">
    </div>

    <div class="form-group">
        <input type="hidden" name="iiteam_id" value="<?php the_ID(); ?>"/>
        <input type="hidden" name="iiteam_member_id" value="<?php echo ( isset( $iiteam_member_id) ? $iiteam_member_id : null ) ?>"/>
        <input type="text" name="kill_most_robots" style="position:absolute;left:-10000px;"/>
        <?php wp_nonce_field( 'cu_iiteam_member'); ?>
        <input  class="btn btn-default" type="submit" name="submit" value="Save"/>
        <?php if( !empty($iiteammember_user_login) ): ?>
            <input  class="btn btn-danger" type="submit" name="remove" value="Remove"/>
        <?php endif; ?>


    </div>

</form>
<hr/>