<div>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#events" aria-controls="events" role="tab" data-toggle="tab">Events</a>
        </li>
        <li role="presentation">
            <a href="#members" aria-controls="members" role="tab" data-toggle="tab">Members</a>
        </li>
        <li role="presentation">
            <a href="#details" aria-controls="details" role="tab" data-toggle="tab">Team Details</a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="events">
            <div class="col-xs-12">
                <?php echo $this->collect_and_echo_event_forms($date); ?>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="members">
            <div class="col-xs-12">
                <?php echo $this->collect_and_echo_member_forms(); ?>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="details">
            <div class="col-xs-12">
                <?php echo $this->build_edit_team_form(); ?>
            </div>
        </div>
    </div>

</div>