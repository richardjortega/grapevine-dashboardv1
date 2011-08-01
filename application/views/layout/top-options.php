<div id="top-options-holder">
    <form id="range-form" action="<?php echo url::site(); ?>" method="post">
        <span><?php echo __('Viewing'); ?>:</span>

        <?php $viewingRange = Session::instance()->get('viewingRange'); ?>
        <?php
        echo form::select(
                'period', array(
            '1m' => '1m',
            '3m' => '3m',
            '6m' => '6m',
            '1y' => '1y',
                ), $viewingRange['period'], array(
            'id' => 'period-selector',
                )
        );
        ?>
        <?php
        echo html::image('images/icons/pickdate.png', array('alt' => 'Choose start date'));
        echo form::input(
                'date', $viewingRange['date'], array(
            'id' => 'date-selector',
            'style' => 'display:none;'));

        echo form::input(
                'date_range', null, array(
            'id' => 'date-range',
            'class' => 'as-select with-icon',
            'maxlength' => '10',
            'disabled' => 'disabled',
            'style' => 'width: 120px; background: #fff;'));
        ?>

        <?php
        echo html::image('images/icons/email.png', array('alt' => ''));
        echo html::anchor('#email', 'Email', array('class' => 'as-select with-icon email'));
        ?>
        <?php
        echo html::image('images/icons/export.png', array('alt' => ''));
        echo html::anchor('#export', 'Export', array('class' => 'as-select with-icon export'));
        ?>
    </form>
</div>

<div id="dialog-export" title="Export to Email" class="hide">
    <p class="validateTips">
<?php echo __('Please provide email addresses you would like to send this report to‭ (‬use commas to send to multiple‭) .'); ?>
    </p>
    <form>
        <fieldset>
            <label for="email">Email</label>
            <input type="text" name="email" id="email" value="" class="text ui-widget-content ui-corner-all" />
        </fieldset>
    </form>
</div>