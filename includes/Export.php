<?php

namespace LicenseManagerForWooCommerce;

use FPDF;
use LicenseManagerForWooCommerce\Enums\LicenseStatus;
use LicenseManagerForWooCommerce\Models\Resources\License as LicenseResourceModel;
use LicenseManagerForWooCommerce\Repositories\Resources\License as LicenseResourceRepository;
use LicenseManagerForWooCommerce\Repositories\Resources\NodefyOperationLog as NodefyOperationLogResourceRepository;

defined('ABSPATH') || exit;

class Export
{
    /**
     * Export Constructor.
     */
    public function __construct()
    {
        add_action('lmfwc_export_license_keys_pdf', array($this, 'exportLicenseKeysPdf'), 10, 1);
        add_action('lmfwc_export_license_keys_csv', array($this, 'exportLicenseKeysCsv'), 10, 1);
        add_action('lmfwc_export_nodefy_operation_log_lists_csv', array($this, 'exportNodefyOperationLogListsCsv'), 10, 1);
    }

    /**
     * Creates a PDF of license keys by the given array of ID's.
     *
     * @param array $licenseKeyIds
     */
    public function exportLicenseKeysPdf($licenseKeyIds)
    {
        $licenseKeys = array();

        foreach ($licenseKeyIds as $licenseKeyId) {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->find($licenseKeyId);

            if (!$license) {
                continue;
            }

            $licenseKeys[] = array(
                'id' => $license->getId(),
                'order_id' => $license->getOrderId(),
                'product_id' => $license->getProductId(),
                'license_key' => $license->getDecryptedLicenseKey()
            );
        }

        $header = array(
            'id'          => __('ID', 'license-manager-for-woocommerce'),
            'order_id'    => __('Order ID', 'license-manager-for-woocommerce'),
            'product_id'  => __('Product ID', 'license-manager-for-woocommerce'),
            'license_key' => __('License key', 'license-manager-for-woocommerce')
        );

        ob_clean();

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AddPage();
        $pdf->AddFont('Roboto-Bold', '', 'Roboto-Bold.php');
        $pdf->AddFont('Roboto-Regular', '', 'Roboto-Regular.php');
        $pdf->AddFont('RobotoMono-Regular', '', 'RobotoMono-Regular.php');
        $pdf->SetFont('Roboto-Bold', '', 10);

        // Header
        $pdf->Image(LMFWC_IMG_URL . 'lmfwc_logo.jpg', 10, 10, -300);
        $pdf->Ln(25);

        // Table Header
        $pdf->SetDrawColor(200, 200, 200);

        foreach ($header as $columnName => $col) {
            $width = 40;

            if ($columnName == 'id') {
                $width = 12;
            }

            if ($columnName == 'order_id'
                || $columnName == 'product_id'
            ) {
                $width = 20;
            }

            if ($columnName == 'license_key') {
                $width = 0;
            }

            $pdf->Cell($width, 10, $col, 'B');
        }

        // Data
        $pdf->Ln();

        foreach ($licenseKeys as $row) {
            foreach ($row as $columnName => $col) {
                $pdf->SetFont('Roboto-Regular', '', 8);
                $width = 40;

                if ($columnName == 'id') {
                    $width = 12;
                }

                if ($columnName == 'order_id'
                    || $columnName == 'product_id'
                ) {
                    $width = 20;
                }

                if ($columnName == 'license_key') {
                    $pdf->SetFont('RobotoMono-Regular', '', 8);
                    $width = 0;
                }

                $pdf->Cell($width, 6, $col, 'B');
            }

            $pdf->Ln();
        }

        $pdf->Output(date('YmdHis') . '_license_keys_export.pdf', 'D');
    }

    /**
     * Creates a CSV of license keys by the given array of ID's.
     *
     * @param array $licenseKeyIds
     */
    public function exportLicenseKeysCsv($licenseKeyIds)
    {
        $licenseKeys = array();

        // Should no columns be defined, we will export all of them
        if (!$columns = Settings::get('lmfwc_csv_export_columns', Settings::SECTION_TOOLS)) {
            $columns = array(
                'id'                  => true,
                // 'order_id'            => true,
                'order_num'           => true,
                'product_id'          => true,
                'product_name'        => true,
                'user_id'             => true,
                'user_name'           => true,
                'user_email'          => true,
                'license_key'         => true,
                'expires_at'          => true,
                'valid_for'           => true,
                // 'status'              => true,
                'times_activated'     => true,
                'times_activated_max' => true,
                'activated_at'        => true,
                'created_at'          => true,
                'created_by'          => true,
                'updated_at'          => true,
                'updated_by'          => true
            );
        }

        foreach ($licenseKeyIds as $licenseKeyId) {
            /** @var LicenseResourceModel $license */
            $license = LicenseResourceRepository::instance()->find($licenseKeyId);
            $data    = array();

            if (!$license) {
                continue;
            }

            foreach (array_keys($columns) as $exportColumn) {

                $user = get_userdata($license->getUserId());

                switch ($exportColumn) {
                    case 'license_key':
                        $data[$exportColumn] = $license->getDecryptedLicenseKey();
                        break;
                    case 'status':
                        $data[$exportColumn] = LicenseStatus::getExportLabel($license->getStatus());
                        break;
                    case 'order_num':
                        $order = wc_get_order($license->getOrderId());
                        $data[$exportColumn] = $order ? $order->get_order_number() : '';
                        break;
                    case 'product_name':
                        $product = wc_get_product($license->getProductId());
                        $data[$exportColumn] = $product ? $product->get_name() : '';
                        break;
                    case 'user_name':
                        $data[$exportColumn] = $user ? $user->display_name : '';
                        break;
                    case 'user_email':
                        $data[$exportColumn] = $user ? $user->user_email : '';
                        break;
                    default:
                        $getter              = 'get' . lmfwc_camelize($exportColumn);
                        $data[$exportColumn] = null;

                        if (method_exists($license, $getter)) {
                            $data[$exportColumn] = $license->{$getter}();
                        }

                        break;
                }
            }

            $licenseKeys[] = $data;
        }

        $licenseKeys = apply_filters('lmfwc_export_license_csv', $licenseKeys);
        $filename    = date('YmdHis') . '_license_keys_export.csv';

        // Disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // Force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // Disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

        ob_clean();
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys($licenseKeys[0]));

        foreach ($licenseKeys as $row) {
            fputcsv($df, $row);
        }

        fclose($df);
        ob_end_flush();

        exit();
    }

    public function exportNodefyOperationLogListsCsv($logIds)
    {
        $logs = array();

          // 'cb'          => '<input type="checkbox" />',
            // 'id'          => __('Id', 'license-manager-for-woocommerce'),
            // 'license_id'  => __('License', 'license-manager-for-woocommerce'),
            // 'order_id'    => __('Order', 'license-manager-for-woocommerce'),
            // 'product_id'  => __('Product', 'license-manager-for-woocommerce'),
            // 'user_id'     => __('Customer', 'license-manager-for-woocommerce'),
            // 'operation'   => __('Operation', 'license-manager-for-woocommerce'),
            // 'users_number'=> __('Users Number', 'license-manager-for-woocommerce'),
            // 'expired_at'  => __('Expired At', 'license-manager-for-woocommerce'),
            // 'note'        => __('Note', 'license-manager-for-woocommerce'),
            // 'created'     => __('Created', 'license-manager-for-woocommerce'),

        $columns = array(
            'id'                  => true,
            'license_key'         => true,
            'order_num'           => true,
            'product_id'          => true,
            'product_name'        => true,
            'customer_name'       => true,
            'customer_email'      => true,
            'operation'           => true,
            'users_number'        => true,
            'expired_at'          => true,
            'note'                => true,
            'created_at'          => true,
            'created_by'          => true,
        );

        foreach ($logIds as $logId) {
            /** @var NodefyOperationLogResourceRepository $license */
            $log     = NodefyOperationLogResourceRepository::instance()->find($logId);
            $data    = array();

            if (!$logId) {
                continue;
            }

            foreach (array_keys($columns) as $exportColumn) {

                $license = LicenseResourceRepository::instance()->findBy(array(
                    'id' => $log->getLicenseId()
                ));
                $user = get_userdata($log->getUserId());
                $order = wc_get_order($log->getOrderId());
                $product = wc_get_product($log->getProductId());

                $return_str = '';
                $backup = json_decode($log->getLicenseBackup(), true);
                $new_value = null;
                if (isset($backup['new_value']) && isset($backup['new_value']['data'])) {
                    $new_value = json_decode($backup['new_value']['data'], true);
                }

                switch ($exportColumn) {
                    case 'license_key':
                        $data[$exportColumn] = $license ? $license->getDecryptedLicenseKey() : '';
                        break;
                    case 'order_num':
                        $data[$exportColumn] = $order ? $order->get_order_number() : '';
                        break;
                    case 'product_name':
                        $data[$exportColumn] = $product ? $product->get_name() : '';
                        break;
                    case 'customer_name':
                        $data[$exportColumn] = $user ? $user->display_name : '';
                        break;
                    case 'customer_email':
                        $data[$exportColumn] = $user ? $user->user_email : '';
                        break;
                    case 'users_number':
                        $data[$exportColumn] = $new_value ? $new_value['users_number'] : '';
                        break;
                    case 'expired_at':
                        $data[$exportColumn] = $new_value ? $new_value['expires_at'] : '';
                        break;
                    default:
                        $getter              = 'get' . lmfwc_camelize($exportColumn);
                        $data[$exportColumn] = null;

                        if (method_exists($log, $getter)) {
                            $data[$exportColumn] = $log->{$getter}();
                        }

                        break;
                }
            }

            $logs[] = $data;
        }

        $filename    = date('YmdHis') . '_export_nodefy_operation_logs_export.csv';

        // Disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // Force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // Disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");

        ob_clean();
        ob_start();
        $df = fopen("php://output", 'w');
        fputcsv($df, array_keys($logs[0]));

        foreach ($logs as $row) {
            fputcsv($df, $row);
        }

        fclose($df);
        ob_end_flush();

        exit();
    }
}
