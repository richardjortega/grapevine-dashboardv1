 <div id="box-competition-ledger" class="box">
    <?php echo View::factory(
        '_partials/box/header', 
        array(
            'caption' => __('Competition Ledger'),
            'buttons' => array('dashboard-pin', 'move'),
            )
        ); 
    
    ?>
    <div class="box-content">
        <div class="data-grid-holder" style="display: none;">
            <form action="">
                <table class="wide data-grid no-outer-border" style="padding: 5px;">
                    <tbody>
                        <!--<tr>
                            <td class="col-checkbox"></td>
                            <td class="col-rating"></td>
                            <td class="col-submitted a-center"></td>
                            <td class="col-title"></td>
                            <td class="col-site"></td>
                            <td class="col-status a-right"></td>
                        </tr>-->
                        <!--<tr style="display: none;">
                            <td colspan="6">
                                <div>
                                    
                                </div>
                            </td>
                        </tr>-->
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>