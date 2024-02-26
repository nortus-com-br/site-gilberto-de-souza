<?php

namespace PixelYourSite;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

require_once PYS_PATH.'/includes/offline_events/class-offline-export-file.php';
require_once PYS_PATH.'/includes/offline_events/class-offline-db.php';
class OfflineEvents {

    public static $exportTypes = ['export_last_time','export_by_date','export_all'];
    private $exportedFile;

    public function __construct() {
        $this->exportedFile = new CSVWriterFile(
            array( 'order_id','email', 'phone', 'fn', 'ln', 'ct', 'st', 'country', 'zip','event_name','event_time', 'value','currency','content_ids' )
        );
    }

    /**
     * @param String $exportType
     * @param \DateTime $startDate
     * @param \DateTime$endDate
     * @return String
     */
    static function getFineName($exportType,$startDate,$endDate,$key) {
        if($exportType == "export_all") {
            return date("Y_m_d",time())."-".$exportType."-".$key;
        } else {
            return date("Y_m_d",time())."-".$exportType."-".$startDate->format("Y_m_d")."-".$endDate->format("Y_m_d")."-".$key;
        }
    }

    static function getFilePath($fileName) {
        return trailingslashit( PYS_PATH ).'tmp/'.$fileName.".csv";
    }

    static function getFileUrl($fileName) {
        return trailingslashit( PYS_URL ).'tmp/'.$fileName.".csv";
    }

    static function validateExportType($type) {

        if(!in_array($type,OfflineEvents::$exportTypes)) {
            return  "export_all";
        }
        return $type;
    }

    /**
     * @param String $type
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param int $page
     * @return int
     */
    function wooExportPurchase($type,$startDate,$endDate,$page,$orderStatus,$fileName) {
        $filePath = OfflineEvents::getFilePath($fileName);
        $results = OfflineEventsDb::getPostIds("shop_order",$page,$type,$startDate,$endDate,$orderStatus);
        $this->exportedFile->openFile($filePath,$page);

        $value_option   = PYS()->getOption( 'woo_purchase_value_option' );
        $global_value   = PYS()->getOption( 'woo_purchase_value_global', 0 );
        $percents_value = PYS()->getOption( 'woo_purchase_value_percent', 100 );
        $orderIdPrefix = PYS()->getOption("woo_order_id_prefix");

        foreach ($results as $row) {
            $order_id = $row->ID;

            $order = wc_get_order($order_id);
            if ( $order == null ) {
                continue;
            }
            $total = $order->get_total();
            $args = ["products"=>[]];
            $ids = [];

            foreach($order->get_items() as $line_item) {
                if( !($line_item instanceof \WC_Order_Item_Product)) continue;
                $product_id = empty($line_item['variation_id']) ? $line_item['product_id'] : $line_item['variation_id'];
                $product = wc_get_product($product_id);
                if(!$product) continue;
                $price = getWooProductPriceToDisplay($product->get_id(),1,-1);
                $args["products"][] = [
                    'product_id'    => $product->get_id(),
                    'parent_id'     => $product->get_parent_id(),
                    'type'          => $product->get_type(),
                    'quantity'      => $line_item['qty'],
                    'price'         => $price, // price for single product
                    'total'         => \PixelYourSite\pys_round($line_item['total']),
                    'total_tax'     => pys_round($line_item['total_tax']),
                    'subtotal'      => pys_round($line_item['subtotal']),
                    'subtotal_tax'  => pys_round($line_item['subtotal_tax']),
                ];
                $ids[] =$product->get_id();
            }

            $value = getWooEventValueProducts($value_option,$global_value,$percents_value,$total,$args);

            if(PYS()->getOption("woo_advance_purchase_fb_enabled") ) {//send fb server events
                $data = [
                    $orderIdPrefix.$order->get_id(),
                    $order->get_billing_email(),
                    $order->get_billing_phone(),
                    $order->get_billing_first_name(),
                    $order->get_billing_last_name(),
                    $order->get_billing_city(),
                    $order->get_billing_state(),
                    $order->get_billing_country(),
                    $order->get_billing_postcode(),
                    "Purchase",
                    $order->get_date_created()->date("Y-m-d\\TH:i:s\\Z"),
                    $value,
                    $order->get_currency(),
                    implode(",",$ids),
                ];
                $data = apply_filters("pys_offline_events_data",$data,$order);
                $this->exportedFile->writeLine($data);
            }
        }

        $this->exportedFile->closeFile();
        return count($results);
    }

}