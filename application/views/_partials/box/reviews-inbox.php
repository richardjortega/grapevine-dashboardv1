 <div id="box-recent-reviews" class="box">
    <?php echo View::factory(
        '_partials/box/header', 
        array(
            'caption' => __('reviews inbox'),
            'buttons' => array('dashboard-pin', 'move'),
            )
        ); 
    ?>
    
    <?php echo View::factory(
        '_partials/box/filters', 
        array(
            'filters' => array('status' => 'Status Filter', 'source' => 'Source Filter'),
            'has_pager' => true
            )
        ); 
    ?>
    
    <div class="box-content">
        <div class="data-grid-holder" style="display: none;">
            <table class="wide data-grid no-outer-border">
                <tbody>
                    <tr>
                        <td class="col-status" style="width:28px;"></td>
                        <td class="col-rating" style="width:110px;"></td>
                        <td class="col-submitted a-center" style="width:76px;"></td>
                        <td class="col-title" style="width:426px;">
                            <div class="wrapper">
                                <div class="in"></div>
                            </div>
                        </td>
                        <td class="col-site a-right" style="width:54px;"></td>
                    </tr>
                    <?php echo View::factory('_partials/box/generic-row'); ?>
                </tbody>
            </table>
        </div>
    </div>
</div>