<form class="iiteamevent_edit" action="" method="post">

    <div class="form-group">
        <label for="iievent_title">Event Title</label>
        <input class="form-control" type="text" name="iievent_title" value="<?php echo $iievent_title ?>">
    </div>

    <div class="form-group">
        <label for="iievent_description">Description <strong>*</strong></label>
        <textarea class="form-control" name="iievent_description" rows="1"><?php echo $iievent_description ?></textarea>
    </div>

    <div class="form-group form-inline">
        <label for="iievent_date">Event Date:</label>
        <input id="iievent_date" class="form-control iievent_date" name="iievent_date" type="text"  value="<?= $iievent_date; ?>"/>

        <label for="iievent_duration">Duration (hrs):</label>
        <input class="form-control" name="iievent_duration" type="number" value="<?= $iievent_duration ?>" />
    </div>

    <div class="form-group">
        <input type="hidden" name="iievent_id" value="<?= $iievent_id ?>"/>
        <input type="hidden" name="iiteam_id" value="<?= get_the_ID(); ?>"/>
        <input type="text" name="kill_most_robots" style="position:absolute;left:-10000px;"/>
        <?php wp_nonce_field( 'cu_iievent'); ?>
        <input  class="btn btn-default" type="submit" name="submit" value="Save"/>
        <?php if( !empty($iievent_id) ): ?>
            <input  class="btn btn-danger" type="submit" name="remove" value="Remove"/>
        <?php endif; ?>

    </div>

</form>
<hr/>