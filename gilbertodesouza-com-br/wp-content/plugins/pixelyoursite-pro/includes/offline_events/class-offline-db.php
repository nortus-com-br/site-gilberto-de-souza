<?php
namespace PixelYourSite;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class OfflineEventsDb {

    /**
     * @param int $page
     * @param String $exportType
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array|object|null
     */
    static function getPostIds($postType,$page,$exportType,$start,$end,$orderStatus) {
        global $wpdb;
        $startPage = ($page -1) * 100;
        $startDate = $start->format("Y-m-d");
        $endDate = $end->format("Y-m-d");
        $statusMask = implode(', ', array_fill(0, count($orderStatus), '%s'));
        $args = [$postType];


        if($exportType == "export_all") {
            $args = array_merge($args,$orderStatus);
            $args[] = $startPage;
            $query = $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_type = %s 
                        AND post_status IN($statusMask)
                        LIMIT %d, 100",
                $args
            );
        } else {
            $args[] = $startDate;
            $args[] = $endDate;
            $args = array_merge($args,$orderStatus);
            $args[] = $startPage;
            $query = $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_type = %s 
                       AND post_date >= %s 
                       AND post_date <= %s 
                       AND post_status IN($statusMask)
                       LIMIT %d, 100",
                $args
            );
        }

        return $wpdb->get_results( $query );
    }
    /**
     * @param String $postType
     * @param String $exportType
     * @param \DateTime $start
     * @param \DateTime $end
     * @return int
     */
    static function getPostCount($postType,$exportType,$start,$end,$orderStatus) {
        global $wpdb;
        $startDate = $start->format("Y-m-d");
        $endDate = $end->format("Y-m-d");
        $statusMask = implode(', ', array_fill(0, count($orderStatus), '%s'));

        $args = [$postType];
        if($exportType == "export_all") {
            $args = array_merge($args,$orderStatus);
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM    $wpdb->posts WHERE   post_type = %s AND post_status IN($statusMask)",
                $args
            );
        } else {
            $args[] = $startDate;
            $args[] = $endDate;
            $args = array_merge($args,$orderStatus);
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM    $wpdb->posts  WHERE   post_type = %s 
                                 AND post_date >= %s 
                                 AND post_date <= %s
                                 AND post_status IN($statusMask)",
                $args
            );
        }

        return $wpdb->get_var( $query );
    }
}