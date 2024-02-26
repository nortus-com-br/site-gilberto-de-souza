<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
class OrderStatistics {

    static $SYNC_STATUS_START = "START";
    static $SYNC_STATUS_FINISH = "FINISH";
    static $MODEL_FIRST_VISIT = "first_visit";
    static $MODEL_LAST_VISIT = "last_visit";

    private $orderTable;
    private $landingTable;
    private $trafficTable;
    private $utmCampaing;
    private $utmContent;
    private $utmMedium;
    private $utmSource;
    private $utmTerme;
    public $globalPerPage = 5;
    public $perPage = 30;
    private static $_instance;
    static $db_sync_status = 'pys_sync_statistic_db';
    static $woo_stat_order_imported_page = 'pys_woo_stat_order_imported_page';
    static $woo_stat_order_statuses = 'pys_woo_stat_order_statuses';

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;

    }

    static function getSelectedOrderStatus() {
        return get_option(OrderStatistics::$woo_stat_order_statuses,["wc-completed"]);
    }


    public function __construct() {
        global $wpdb;

        $this->orderTable = new StatOrdersTable($wpdb);
        $this->landingTable = new StatLandingTable($wpdb);
        $this->trafficTable = new StatTrafficTable($wpdb);
        $this->utmCampaing = new StatUtmCampaingTable($wpdb);
        $this->utmContent = new StatUtmContentTable($wpdb);
        $this->utmMedium = new StatUtmMediumTable($wpdb);
        $this->utmSource = new StatUtmSourceTable($wpdb);
        $this->utmTerme = new StatUtmTermTable($wpdb);



        add_filter("pys_db_tables",function ($items) {
            return array_merge($items,[
                $this->orderTable, $this->landingTable,
            $this->trafficTable, $this->utmCampaing,
            $this->utmContent, $this->utmMedium,
            $this->utmSource, $this->utmTerme
            ]);
        });


        add_action("wp_ajax_pys_stat_sync",[$this,"runSyncs"]);
        add_action("wp_ajax_pys_stat_change_orders_status",[$this,"changeOrderStatus"]);

        // Load Stat Data
        add_action("wp_ajax_pys_stat_data",[$this,"loadStatData"]);
        add_action("wp_ajax_pys_stat_single_data",[$this,"loadStatSingleData"]);

        // woo
        add_action("woocommerce_payment_complete", [$this, "addNewOrderAfterPayment"]);
        add_action("woocommerce_order_status_changed", [$this, "addNewOrderAfterChangeStatus"], 10, 4);
        add_action("woocommerce_order_refunded", [$this, "updateWooOrderSale"], 10, 2);

    }


    function getSyncStatus() {
        return get_option(OrderStatistics::$db_sync_status,OrderStatistics::$SYNC_STATUS_START);
    }

    function getSyncPage() {
        return get_option(OrderStatistics::$woo_stat_order_imported_page,1);
    }


    function changeOrderStatus() {

        $orders = $_POST['orders'];

        if(count($orders)>0) {
            update_option(OrderStatistics::$woo_stat_order_imported_page, 1);
            update_option(OrderStatistics::$woo_stat_order_statuses, $orders);
            update_option(OrderStatistics::$db_sync_status,OrderStatistics::$SYNC_STATUS_START);
            $this->orderTable->clear();
           // $allCount = $this->getOrdersCount($orders);
            wp_send_json_success([
               // "pages" => ceil($allCount/$this->perPage)
            ]);
        } else {
            wp_send_json_error();
        }
    }


    function getWooOrdersCount($statuses) {
        global $wpdb;

        $sql = [];
        foreach($statuses as $status){
            $sql[] = "post_status ='".$status."'";
        }
        $sql = "SELECT count(ID)  FROM {$wpdb->prefix}posts WHERE ".implode(" OR ", $sql)." AND `post_type` = 'shop_order'";
        return $wpdb->get_var($sql);
    }



    function runSyncs() {
        $page = $_POST['page'];
        $imported = $this->importOrders($page,$this->perPage,OrderStatistics::getSelectedOrderStatus());
        update_option(OrderStatistics::$woo_stat_order_imported_page, $page);
        $isLastPage = $imported != $this->perPage;
        if($isLastPage) {
            update_option(OrderStatistics::$db_sync_status,OrderStatistics::$SYNC_STATUS_FINISH);
        }
        wp_send_json_success([
            "page" => $page,
            "isLastPage" => $isLastPage,
        ]);
    }

    function importOrders($page,$perPage,$status) {
        $args = array(
            'status' => $status,
            'limit' => $perPage,
            'paged' => $page,
        );
        $orders = wc_get_orders( $args );
        foreach ($orders as $order) {
            if($order instanceof \WC_Order) {
                $this->addNewWooOrder($order);
            }
        }
        return count($orders);
    }


    function exportAll($label,$startDate,$endDate,$type,$filter_type,$model) {

        $endDate = date('Y-m-d', strtotime($endDate. ' + 1 days'));


        $typeId = $this->getTypeId($type);
        $filterColName = $this->getCollNameByTag($filter_type,$model);
        $filterTable = $this->getTableByTag($filter_type);

        $rows = $this->orderTable->getDataAll($filterTable->getName(),$filterColName,$startDate,$endDate,$typeId);

        $exportedFile = new CSVWriterFile(
            array( $label,'Orders','Gross sales', 'Net sales')
        );
        $exportedFile->openFile("php://output");
        foreach ($rows as $row) {
            $exportedFile->writeLine([
                $row->item_value,
                $row->count,
                $row->gross,
                $row->net
            ]);
        }

        $exportedFile->closeFile();

    }

    function exportSingle($startDate,$endDate,$filterId,$type,$groupByDate,$filter_type,$model) {

        $typeId = $this->getTypeId($type);
        $filterColName = $this->getCollNameByTag($filter_type,$model);
        $endDate = date('Y-m-d', strtotime($endDate. ' + 1 days'));

        $data = $this->orderTable->getDataForSingle($filterColName,$filterId,$startDate,$endDate,$typeId,$groupByDate);


        if($groupByDate == 'false') {
            $exportedFile = new CSVWriterFile(
                array( "Order ID",'Gross sales', 'Net sales')
            );
            $exportedFile->openFile("php://output");
            foreach ($data as $row) {
                $exportedFile->writeLine([
                   $row['order_id'],
                    $row['gross'],
                    $row['net'],
                ]);
            }
        } else {
            $exportedFile = new CSVWriterFile(
                array( "Date","Orders",'Gross sales', 'Net sales')
            );
            $exportedFile->openFile("php://output");
            foreach ($data as $row) {
                $exportedFile->writeLine([
                    $row['x'],
                    $row['count'],
                    $row['gross'],
                    $row['net'],
                ]);
            }
        }

        $exportedFile->closeFile();
    }


    private function getTypeId($type) {
        if($type == "woo") {
            return 0;
        }
        return 1; // edd
    }

    private function getCollNameByTag($tag,$model) {
        switch ($tag) {
            case "traffic_source": {
                $name = 'traffic_source_id';
            }break;
            case "traffic_landing": {
                $name = 'landing_id';
            }break;
            case "utm_source": {
                $name = 'utm_source_id';
            }break;
            case "utm_medium": {
                $name = 'utm_medium_id';
            }break;
            case "utm_campaing": {
                $name = 'utm_campaing_id';
            }break;
            case "utm_term": {
                $name = 'utm_term_id';
            }break;
            case "utm_content": {
                $name = 'utm_content_id';
            }break;
            default : $name = "";
        }

        if($model == self::$MODEL_FIRST_VISIT) {
            return $name;
        }
        return "last_".$name;
    }

    private function getTableByTag($tag) {
        switch ($tag) {
            case "traffic_source": {
                return $this->trafficTable;
            }break;
            case "traffic_landing": {
                return $this->landingTable;
            }break;
            case "utm_source": {
                return $this->utmSource;

            }break;
            case "utm_medium": {
                return $this->utmMedium;

            }break;
            case "utm_campaing": {
                return $this->utmCampaing;

            }break;
            case "utm_term": {
                return $this->utmTerme;

            }break;
            case "utm_content": {
                return $this->utmContent;
            }break;
        }
    }
    /**
     * Ajax response
     */
    function loadStatSingleData() {
        $model = $_POST["model"]; // first_visit or last_visit
        $startDate = $_POST["start_date"];
        $endDate = $_POST["end_date"];
        $endDate = date('Y-m-d', strtotime($endDate. ' + 1 days'));
        $filterId = $_POST["filter_id"];
        $typeId = $this->getTypeId($_POST["type"]);
        $groupByDate = $_POST['group_by_date'];
        $filterColName = $this->getCollNameByTag($_POST["filter_type"],$model);

        $data = $this->orderTable->getDataForSingle($filterColName,$filterId,$startDate,$endDate,$typeId,$groupByDate);
        wp_send_json_success([
            "data" => $data,
        ]);
        wp_die();
    }
    function loadStatData() {
        $model = $_POST["model"]; // first_visit or last_visit
        $startDate = $_POST["start_date"];
        $endDate = $_POST["end_date"];
        $endDate = date('Y-m-d', strtotime($endDate. ' + 1 days'));

        $page = intval($_POST["page"]) - 1;
        $typeId = $this->getTypeId($_POST["type"]);
        $filterColName = $this->getCollNameByTag($_POST["filter_type"],$model);
        $filterTable = $this->getTableByTag($_POST["filter_type"]);


        $filterItems = $this->orderTable->getSumForFilter($filterTable->getName(),$filterColName,$startDate,$endDate,$page * $this->globalPerPage,$this->globalPerPage,$typeId);
        if(count($filterItems["ids"]) > 0) {
            $data = $this->orderTable->getData($filterTable->getName(),$filterColName,$filterItems["ids"],$startDate,$endDate,$typeId);
            wp_send_json_success([
                "items_sum" => $filterItems,
                "items" => array_values($data),
                "max" => $this->orderTable->getFilterCount($filterColName,$typeId,$startDate,$endDate)
            ]);
        } else {
            wp_send_json_success([
                "items_sum" => $filterItems,
                "items" => [],
                "max" => 0
            ]);
        }


        wp_die();
    }

    function addNewOrderAfterChangeStatus($order_id, $from, $to,$order) {


        $activeStatuses = $this->getSelectedOrderStatus();
        $oldStatus = "wc-".$from;
        $newStatus = "wc-".$to;

        if(in_array($oldStatus,$activeStatuses)) {
            $this->orderTable->deleteOrder($order_id,0);
        }

        if(in_array($newStatus,$activeStatuses)) {
            if($this->orderTable->isExistOrder($order_id,0)) {
                $this->updateWooOrderSale($order_id,-1);
            } else {
                $this->addNewWooOrder($order);
            }
        }
    }

    function addNewOrderAfterPayment($orderId) {
        $order = wc_get_order($orderId);
        $activeStatuses = $this->getSelectedOrderStatus();
        $orderStatus = "wc-".$order->get_status();
        if(in_array($orderStatus,$activeStatuses)) {
            if($this->orderTable->isExistOrder($orderId,0)) {
                $this->updateWooOrderSale($orderId,-1);
            } else {
                $this->addNewWooOrder($order);
            }
        }
    }

    function getUtmIds($utmData) {
        $data = [];
        if(!empty($utmData)) {
            $utms = explode("|", $utmData);
            foreach ($utms as $utm) {
                $item = explode(":", $utm);
                $name = $item[0];
                if ($item[1] != "undefined") {
                    switch ($name) {
                        case "utm_source":
                            $data[$name] = $this->utmSource->insert($item[1]);
                            break;
                        case "utm_medium":
                            $data[$name] = $this->utmMedium->insert($item[1]);
                            break;
                        case "utm_campaign":
                            $data[$name] = $this->utmCampaing->insert($item[1]);
                            break;
                        case "utm_term":
                            $data[$name] = $this->utmTerme->insert($item[1]);
                            break;
                        case "utm_content":
                            $data[$name] = $this->utmContent->insert($item[1]);
                            break;
                    }
                }
            }
        }
        return $data;
    }

    /**
     * Hook for woocommerce_checkout_order_created
     * Create new stat order
     * @param \WC_Order $order
     */
    function addNewWooOrder($order) {

        $sale = $this->getWooOrderSale($order);
        $enrichData = get_post_meta($order->get_id(),'pys_enrich_data',true);

        $landing = $enrichData ? $enrichData['pys_landing'] : "";
        $source = $enrichData ? $enrichData['pys_source']: "";
        $utmData = $enrichData ? $enrichData['pys_utm']: "";

        $lastLanding = $enrichData && isset($enrichData['last_pys_landing']) ? $enrichData['last_pys_landing'] : "";
        $lastSource = $enrichData && isset($enrichData['last_pys_source']) ? $enrichData['last_pys_source']: "";
        $lastUtmData = $enrichData && isset($enrichData['last_pys_utm']) ? $enrichData['last_pys_utm']: "";

        $trafficSourceId = $this->trafficTable->insert($source);
        $landingPageId = $this->landingTable->insert($landing);

        $utmIds = $this->getUtmIds($utmData);
        $utmSourceId = isset($utmIds['utm_source']) ? $utmIds['utm_source'] : null;
        $utmMediumId = isset($utmIds['utm_medium']) ? $utmIds['utm_medium'] : null;
        $utmCampaingId = isset($utmIds['utm_campaign']) ? $utmIds['utm_campaign'] : null;
        $utmTermId = isset($utmIds['utm_term']) ? $utmIds['utm_term'] : null;
        $utmContentId = isset($utmIds['utm_content']) ? $utmIds['utm_content'] : null;

        $lastTrafficSourceId = $this->trafficTable->insert($lastSource);
        $lastLandingPageId = $this->landingTable->insert($lastLanding);

        $lastUtmIds = $this->getUtmIds($lastUtmData);
        $lastUtmSourceId = isset($lastUtmIds['utm_source']) ? $lastUtmIds['utm_source'] : null;
        $lastUtmMediumId = isset($lastUtmIds['utm_medium']) ? $lastUtmIds['utm_medium'] : null;
        $lastUtmCampaingId = isset($lastUtmIds['utm_campaign']) ? $lastUtmIds['utm_campaign'] : null;
        $lastUtmTermId = isset($lastUtmIds['utm_term']) ? $lastUtmIds['utm_term'] : null;
        $lastUtmContentId = isset($lastUtmIds['utm_content']) ? $lastUtmIds['utm_content'] : null;


        $this->orderTable->insert($order->get_id(),
            $trafficSourceId,$landingPageId,
            $utmSourceId,$utmMediumId,$utmCampaingId, $utmTermId,$utmContentId,
            $lastTrafficSourceId,$lastLandingPageId,
            $lastUtmSourceId,$lastUtmMediumId,$lastUtmCampaingId, $lastUtmTermId,$lastUtmContentId,
            $sale["gross"],$sale["net"],$sale["total"],
            $this->getTypeId("woo"),
            $order->get_date_created()->date('Y-m-d H:i:s')
        );
    }
    /**
     * Update Gross Sale for order
     *
     * @param int $orderId
     * @param int $refundId
     */
    function updateWooOrderSale($orderId,$refundId) {
        $order = wc_get_order($orderId);
        if($order) {
            $sale = $this->getWooOrderSale($order);
            $this->orderTable->updateSale($orderId,$sale["gross"],$sale["net"],$sale["total"],$this->getTypeId("woo"));
        }
    }

    /**
     * Gross sales are the grand total of all sale transactions reported in a period
     * Net sales are defined as gross sales minus the following three deductions: discounts, returns,allowances
     * @param \WC_Order $order
     * @return array{gross: float,net:float}
     */
    function getWooOrderSale($order) {
        $subtotal = 0;
        $items = $order->get_items();
        foreach ($items as $item) {
            $subtotal += $order->get_item_subtotal($item,false,false);
        }
        $refund = floatval($order->get_total_refunded());
        $refundTax = $order->get_total_tax_refunded();
        $gross = $subtotal;
        $net = $subtotal - $order->get_total_discount(true) - ($refund - $refundTax);
        $total = $order->get_total() - $refund;
        return [
            "gross" => $gross,
            "net" => $net,
            "total" => $total,
        ];
    }


}

/**
 * @return OrderStatistics
 */
function OrderStatistics() {
    return OrderStatistics::instance();
}

OrderStatistics();