<?php defined('ABSPATH') || exit; ?>

<div class="wrap lmfwc">
    <?php
        if ($action === 'list'
            || $action === 'delete'
        ) {
            include_once('nodefyOperationLogs/page-list.php');
        }  elseif ($action === 'view') { // TODO
            // include_once('generators/page-edit.php');
        } 
    ?>
</div>