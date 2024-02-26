<?php
namespace PixelYourSite;
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
require_once PYS_PATH.'/modules/google_analytics/server/server_event_helper.php';
/**
 * The Measurement Protocol API wrapper class.
 *
 * A basic wrapper around the GA Measurement Protocol HTTP API used for making
 * server-side API calls to track events.
 *
 */
class GaMeasurementProtocolAPI {

    /** @var string endpoint for GA API */
    public $ga_url = 'https://www.google-analytics.com/collect';
    //public $ga_url = 'https://www.google-analytics.com/debug/collect'; //debug


    /**
     * Send event in shutdown hook (not work in ajax)
     * @param SingleEvent[] $events
     */
    public function sendEventsAsync($events) {
        // not use
    }

    /**
     * Send Event Now
     *
     * @param SingleEvent[] $events
     */
    public function sendEventsNow($events) {
        foreach ($events as $event) {
            $serverEvent = GaServerEventHelper::mapSingleEventToServerData($event);
            $ids = $event->payload['trackingIds'];
            $this->sendEvent($ids,$serverEvent);
        }
    }

    private function sendEvent($tags,$eventData) {
        PYS()->getLog()->debug('Send GA server tags',$tags);
        PYS()->getLog()->debug('Send GA server event',$eventData);
        foreach ($tags as $tag) {
            $eventData['v']     = '1';// API version
            $eventData['tid']   = $tag; // tracking ID
            $eventData['z']     = time();

            $response = wp_safe_remote_request( $this->ga_url, $this->prepareRequestArgs($eventData) );
            if(is_wp_error($response)) {
                PYS()->getLog()->debug('Send GA server event error',$response);
                return;
            }
//            $response_code     = wp_remote_retrieve_response_code( $response );
//            $response_message  = wp_remote_retrieve_response_message( $response );
//            $raw_response_body = wp_remote_retrieve_body( $response );

            PYS()->getLog()->debug('Send GA server event response',$response);
        }
    }

    private function prepareRequestArgs($params) {
        $args = array(
            'method'      => 'POST',
            'timeout'     => MINUTE_IN_SECONDS,
            'redirection' => 0,
           // 'httpversion' => '1.0',
            'sslverify'   => true,
            'blocking'    => true,
           // 'user-agent'  => $this->get_request_user_agent(),
            'headers'     => [],
            'body'        => $this->paramsToString($params),
            'cookies'     => array(),
        );

        return $args;
    }

    public function paramsToString($params) {

        return  http_build_query( $params, '', '&' );
    }
}