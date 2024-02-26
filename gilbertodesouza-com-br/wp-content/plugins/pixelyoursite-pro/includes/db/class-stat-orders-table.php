<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class StatOrdersTable extends DataBaseTable {

    function getName()
    {
        return $this->wpdb->prefix . "pys_stat_order";
    }

    function getCreateSql()
    {
        $collate = '';
        $tableName = $this->getName();
        if ( $this->wpdb->has_cap( 'collation' ) ) {
            $collate = $this->wpdb->get_charset_collate();
        }
        return "CREATE TABLE $tableName (
              id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
              order_id BIGINT UNSIGNED NOT NULL,
              
              traffic_source_id BIGINT UNSIGNED NULL,
              landing_id BIGINT UNSIGNED NULL,
              utm_source_id BIGINT UNSIGNED NULL,
              utm_medium_id BIGINT UNSIGNED NULL,
              utm_campaing_id BIGINT UNSIGNED NULL,
              utm_term_id BIGINT UNSIGNED NULL,
              utm_content_id BIGINT UNSIGNED NULL,
              
              last_traffic_source_id BIGINT UNSIGNED NULL,
              last_landing_id BIGINT UNSIGNED NULL,
              last_utm_source_id BIGINT UNSIGNED NULL,
              last_utm_medium_id BIGINT UNSIGNED NULL,
              last_utm_campaing_id BIGINT UNSIGNED NULL,
              last_utm_term_id BIGINT UNSIGNED NULL,
              last_utm_content_id BIGINT UNSIGNED NULL,
              
              gross_sale FLOAT UNSIGNED NOT NULL,
              net_sale FLOAT UNSIGNED NOT NULL,
              total_sale FLOAT UNSIGNED NOT NULL,
              type TINYINT NOT NULL,
              date timestamp default current_timestamp, 
              PRIMARY KEY  (id)
            ) $collate;";
    }

    /**
     * @param $filterColName
     * @param $type
     * @return int
     */
    function getFilterCount($filterColName,$type,$dateStart,$dateEnd) {
        $sql = $this->wpdb->prepare("SELECT $filterColName FROM {$this->getName()} 
                                            WHERE type = %d AND $filterColName IS NOT NULL AND date BETWEEN %s AND %s
                                            GROUP BY $filterColName",
            $type,$dateStart,$dateEnd);

        return count($this->wpdb->get_results($sql));
    }

    function getSumForFilter($filterTableName,$filterColName,$startDate,$endDate,$from, $max,$type) {
        $data = ["ids" => [],"filters" => []];
        $sql = $this->wpdb->prepare("SELECT count(order_id) as count, t2.id as item_id, t2.item_value, ROUND(SUM(gross_sale),2) as gross, ROUND(SUM(net_sale),2) as net, ROUND(SUM(total_sale),2) as total 
                                                FROM {$this->getName()} 
                                                LEFT JOIN  $filterTableName as t2 ON  $filterColName = t2.id 
                                                WHERE type = %d AND $filterColName IS NOT NULL  AND date BETWEEN %s AND %s
                                                GROUP BY $filterColName
                                                ORDER BY total DESC
                                                LIMIT %d, %d
                                                ",$type,$startDate,$endDate,$from,$max);

        $rows = $this->wpdb->get_results($sql);
        foreach ($rows as $row) {
            $data["ids"][] = $row->item_id;
            $data["filters"][] = ["id" => $row->item_id,"name" => $row->item_value,"gross" => $row->gross,"net" => $row->net,"total" => $row->total,"count" => $row->count];
        }
        return $data;
    }

    function getDataAll($filterTableName,$filterColName,$startDate,$endDate,$type) {
        $sql = $this->wpdb->prepare("SELECT count(order_id) as count, t2.id as item_id, t2.item_value, ROUND(SUM(gross_sale),2) as gross, ROUND(SUM(net_sale),2) as net, ROUND(SUM(total_sale),2) as total 
                                                FROM {$this->getName()} 
                                                LEFT JOIN  $filterTableName as t2 ON  $filterColName = t2.id 
                                                WHERE type = %d AND $filterColName IS NOT NULL  AND date BETWEEN %s AND %s
                                                GROUP BY $filterColName
                                                ORDER BY total DESC
                                               
                                                ",$type,$startDate,$endDate);
        return $this->wpdb->get_results($sql);
    }

    function getData($filterTableName,$filterColName,$ids,$startDate,$endDate,$type) {
        $data = [];
        $in = '(' . implode(',', $ids) .')';
        //data: [{x:'2016-12-25', y:20}, {x:'2016-12-26', y:10},{x:'2016-12-27', y:15}]


        $sql = $this->wpdb->prepare("SELECT count(order_id) as count,t2.id as item_id, t2.item_value,CAST(date AS DATE) date ,ROUND(SUM(gross_sale),2) as gross, ROUND(SUM(net_sale),2) as net, ROUND(SUM(total_sale),2) as total 
FROM {$this->getName()} 
LEFT JOIN  $filterTableName as t2 ON  $filterColName = t2.id 
WHERE type = %d AND $filterColName IN $in AND date BETWEEN %s AND %s
GROUP BY cast(`date` as date), $filterColName
ORDER BY date DESC
",$type,$startDate,$endDate);

        /**
         * @var {item_value:String,date:String,gross: float,net:float}[]$rows
         */
        $rows = $this->wpdb->get_results($sql);

        foreach ($rows as $row) {
            if(!key_exists($row->item_value,$data)) {
                $data[$row->item_value] = [
                    "item" => ["id" => $row->item_id,"name" => $row->item_value],
                    "data" => []
                ];
            }
            $data[$row->item_value]["data"][] = ["x"=>$row->date,"gross" => $row->gross,"net" => $row->net,"total" => $row->total,"count"=>$row->count];
        }

        return $data;
    }

    function getDataForSingle($filterColName,$filterId,$startDate,$endDate,$type,$groupByDate) {
        $data = [];

        $group = "";
        if($groupByDate == 'true') {
            $sql = $this->wpdb->prepare("SELECT count(order_id) as count ,order_id, CAST(date AS DATE) date ,ROUND(SUM(gross_sale),2) as gross, ROUND(SUM(net_sale),2) as net , ROUND(SUM(total_sale),2) as total
                    FROM {$this->getName()} 
                    WHERE type = %d AND $filterColName = %d  AND date BETWEEN %s AND %s
                    GROUP BY cast(`date` as date)
                    ORDER BY total DESC
                    ",$type,$filterId,$startDate,$endDate);
            $rows = $this->wpdb->get_results($sql);
            foreach ($rows as $row) {
                $data[] = ["x"=>$row->date,"gross" => $row->gross,"net" => $row->net,"total" => $row->total,"order_id" => $row->order_id,"count" => $row->count];
            }
        } else {
            $sql = $this->wpdb->prepare("SELECT  order_id, CAST(date AS DATE) date ,ROUND(gross_sale,2) as gross, ROUND(net_sale,2) as net, ROUND(total_sale,2) as total
                    FROM {$this->getName()} 
                    WHERE type = %d AND $filterColName = %d  AND date BETWEEN %s AND %s
                    ORDER BY total DESC
                    ",$type,$filterId,$startDate,$endDate);
            $rows = $this->wpdb->get_results($sql);
            foreach ($rows as $row) {
                $data[] = ["x"=>$row->date,"gross" => $row->gross,"net" => $row->net,"total" => $row->total,"order_id" => $row->order_id,"count" => 1];
            }
        }



        return $data;
    }

    /**
     * Add new order
     * @param int $order_id
     * @param int $traffic_source_id
     * @param int $landing_id
     * @param int $utm_source_id
     * @param int $utm_medium_id
     * @param int $utm_campaing_id
     * @param int $utm_term_id
     * @param int $utm_content_id
     *
     * @param int $last_traffic_source_id
     * @param int $last_landing_id
     * @param int $last_utm_source_id
     * @param int $last_utm_medium_id
     * @param int $last_utm_campaing_id
     * @param int $last_utm_term_id
     * @param int $last_utm_content_id
     *
     * @param int $gross_sale
     * @param int $net_sale
     * @param int $total_sale
     * @return int|false The number of rows inserted, or false on error.
     */
    function insert($order_id,
                    $traffic_source_id,$landing_id,$utm_source_id,
                    $utm_medium_id,$utm_campaing_id,$utm_term_id,$utm_content_id,
                    $last_traffic_source_id,$last_landing_id,$last_utm_source_id,
                    $last_utm_medium_id,$last_utm_campaing_id,$last_utm_term_id,$last_utm_content_id,
                    $gross_sale,$net_sale,$total_sale,$type,$createDate
    ) {
        if($gross_sale < 0) $gross_sale = 0;
        if($net_sale < 0) $net_sale = 0;
        if($total_sale < 0) $total_sale = 0;
        if($this->isExistOrder($order_id,$type)) {
            return 0;
        }
        return $this->wpdb->insert($this->getName(),
            ["order_id" => $order_id,
                "traffic_source_id" => $traffic_source_id,
                "landing_id" => $landing_id,
                "utm_source_id" => $utm_source_id,
                "utm_medium_id" => $utm_medium_id,
                "utm_campaing_id" => $utm_campaing_id,
                "utm_term_id" => $utm_term_id,
                "utm_content_id" => $utm_content_id,

                "last_traffic_source_id" => $last_traffic_source_id,
                "last_landing_id" => $last_landing_id,
                "last_utm_source_id" => $last_utm_source_id,
                "last_utm_medium_id" => $last_utm_medium_id,
                "last_utm_campaing_id" => $last_utm_campaing_id,
                "last_utm_term_id" => $last_utm_term_id,
                "last_utm_content_id" => $last_utm_content_id,

                "gross_sale" => $gross_sale,
                "net_sale" => $net_sale,
                "total_sale" => $total_sale,

                "type" => $type,
                "date" => $createDate
                ],
            ['%d',
                '%d','%d','%d','%d', '%d','%d','%d',
                '%d','%d','%d','%d', '%d','%d','%d',
                '%f','%f','%f',
                '%d','%s']
        );
    }

    function isExistOrder($orderId,$type) {
        $row = $this->wpdb->get_row($this->wpdb->prepare("SELECT id FROM {$this->getName()} WHERE order_id = %d AND type = %d",$orderId,$type));
        return $row != null;
    }

    /**
     * @param $orderId
     * @return bool|int
     */
    function deleteOrder($orderId,$type) {
      return  $this->wpdb->delete($this->getName(),['order_id' => $orderId,'type' => $type],["%d","%d"]);
    }

    /**
     * @param int $order_id
     * @param int $gross_sale
     * @param int $net_sale
     * @param int $total_sale
     * @return bool|int
     */
    function updateSale($order_id,$gross_sale,$net_sale,$total_sale,$type) {
        if($gross_sale < 0) $gross_sale = 0;
        if($net_sale < 0) $net_sale = 0;

        if(!$this->isExistOrder($order_id,$type)) return false;

        return $this->wpdb->update($this->getName(),
            ["gross_sale" => $gross_sale, "net_sale" => $net_sale,"total_sale" => $total_sale],
            ["order_id" => $order_id,'type' => $type],
            ['%f','%f'],
            ['%d','%d']
        );
    }
}