<h1 class="content-title">
    <?php echo _('Social Media'); ?>
</h1>
<h2 class="content-section-title"><?php echo _('Facebook'); ?>:</h2>
<div id="account-socials-facebook-section" class="padding-5">
    <?php echo _('To see information posted to your Facebook Wall, you will need to have an unrestricted (public) Facebook Business Page. Click to "Connect to Facebook" button. If you don\'t have an access to or are not an admin for your business Twitter account, please contact your Facebook account administrator (including any responsible 3rd party) on assistance on this step.'); ?>
    <form action="" method="post">
        <p>
            <?php echo html::anchor('#', _('Connect with Facebook')); ?>
        </p>
        <div class="a-right">
            
        </div>
    </form>
</div>
<h2 class="content-section-title"><?php echo _('Twitter'); ?>:</h2>
<div id="account-socials-twitter-section" class="padding-5">
    <?php echo _('For best results, please click on "Connect with Twitter" button. If you don\'t have an access to or are not an admin for your business Twitter account, please contact your Twitter account administrator (including any responsible 3rd party) on assistance on this step.'); ?>
    <p>
        <?php echo html::anchor('#', _('Connect with Twitter')); ?>
    </p>
    <p>
        <?php echo _('You may also add a general Twitter "tweet-search" related to your business. You will need to spacify which words (called "tags") and phrses should be used to identify data that is revelant to you. You may type single tags, or entire phrases. You may type any number and combination of tags and phrases, as long as you separate each term with a comma.'); ?>
    </p>
    <p class="i">
        <?php echo _('Examples for search: Anderson Chevrolet, Anderson Chevy, Anderson Chevy dealer'); ?>
        <br />
        <?php echo _('Examples for account name: @AndersonChevrolet'); ?>
    </p>
    <form action="" method="post">
        <table>
            <tr>
                <td class="a-right"><?php echo _('Twitter Search'); ?>:</td>
                <td><?php echo form::input('twitter[search]', ''); ?></td>
            </tr>
            <tr>
                <td class="a-right"><?php echo _('Twitter Account Name'); ?>:</td>
                <td><?php echo form::input('twitter[account]', ''); ?></td>
            </tr>
        </table>
        <p class="a-right">
            <?php echo form::submit('', _('Save')); ?>
        </p>
    </form>
</div>
<h2 class="content-section-title"><?php echo _('Other Social Sites'); ?>:</h2>
<div id="account-socials-other-section" class="padding-5">
    Description ...
</div>