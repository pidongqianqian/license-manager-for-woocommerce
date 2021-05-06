<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc">
    <?php
        if ($action === 'list'
            || $action === 'delete'
        ) {
            include_once('nodefyOperationLogs/page-list.php');
        }  elseif ($action === 'view') {
            include_once('nodefyOperationLogs/page-view.php');
        } 
    ?>
</div>