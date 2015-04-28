<form id="iiuser_register" method="post">
    <div class="form-group">
        <div class="form-group">
            <label for="user_login">Username <strong>*</strong></label>
            <input class="form-control" type="text" name="user_login" value="<?php ( isset( $_POST['username'] ) ? $username : null ) ?>">
        </div>

        <div class="form-group">
            <label for="user_email">Email <strong>*</strong></label>
            <input class="form-control" type="text" name="user_email" value="<?php ( isset( $_POST['email']) ? $email : null ) ?>">
        </div>

        <div class="form-group">
            <label for="iiteam_name"><?php _e('Team Name', 'inciteinvite') ?></label>
            <input class="form-control" type="text" name="iiteam_name" id="iiteam_name" class="input"
                       value="<?php if( isset( $_POST['iiteam_name'])) echo $_POST['iiteam_name']; ?>" size="25"/>
        </div>

        <input type="hidden" name="redirect_to" value="<?php echo home_url(); ?>"/>

        <input type="text" name="kill_most_robots" style="position:absolute;left:-10000px;"/>

        <?php wp_nonce_field( 'cu_iiteam_manager' ); ?>

        <input  class="btn btn-default" type="submit" name="submit" value="Register"/>
    </div>
</form>