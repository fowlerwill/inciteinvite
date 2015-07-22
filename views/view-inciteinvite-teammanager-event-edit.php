<!--The thing to do is to make a column that is 12 wide on a mobile device. col-xs-12 -->
<!-- Then on devices > than md, have the event collapse to a 1 col wide piece of a calendar. col-md-1  -->
<!-- When the event is selected, it can pop up a modal with the below form populated with the below form -->

<div class="col-xs-12 col-md-1">
    <?php if( empty($iievent_id) ): ?>
        <button class="button button-small iievent_button iievent_create_button"
                data-modal="iievent_modal_new"><i class="icon icon-plus"></i> Add Event </button>
    <?php else: ?>
        <div class="alert alert-info" role="alert"><?= $iievent_date; ?></div>
        <button class="button button-small iievent_button iievent_edit_button"
                data-modal="iievent_modal_<?= $iievent_id; ?>" id="iievent_id_<?= $iievent_id; ?>"><?= $iievent_title; ?></button>
    <?php endif; ?>
</div>

<div id="iievent_modal_<?= ( empty($iievent_id) ) ? 'new' : $iievent_id; ?>" class="modal fade">
    <form class="iiteamevent_edit" action="" method="post">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">
                        <?= ( empty($iievent_id) ) ? 'Add New Event' : 'Editing:' . $iievent_title; ?>
                    </h4>
                </div>
                <div class="modal-body">

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


                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <input  class="btn btn-default" type="submit" name="submit" value="Save"/>
                    <?php if( !empty($iievent_id) ): ?>
                        <input  class="btn btn-danger" type="submit" name="remove" value="Remove"/>
                    <?php endif; ?>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </form>
</div><!-- /.modal -->
