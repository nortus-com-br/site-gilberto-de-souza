<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

function maybeMigrate() {
	
	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return;
	}
	
	if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	
	$pys_7_version = get_option( 'pys_core_version', false );


    if ($pys_7_version && version_compare($pys_7_version, '9.0.0', '<') ) {
        migrate_9_0_0();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );
    } elseif ($pys_7_version && version_compare($pys_7_version, '8.6.8', '<') ) {
        migrate_8_6_7();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );
    } elseif ($pys_7_version && version_compare($pys_7_version, '8.3.1', '<') ) {
        migrate_8_3_1();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );
    } elseif ($pys_7_version && version_compare($pys_7_version, '8.0.0', '<') ) {
        migrate_8_0_0();

        update_option( 'pys_core_version', PYS_VERSION );
        update_option( 'pys_updated_at', time() );
    }
}

function migrate_9_0_0() {
    $globalOptions = [
        "automatic_events_enabled" => PYS()->getOption("signal_events_enabled") || PYS()->getOption("automatic_events_enabled"),
        "automatic_event_internal_link_enabled" => PYS()->getOption("signal_click_enabled"),
        "automatic_event_outbound_link_enabled" => PYS()->getOption("signal_click_enabled"),
        "automatic_event_video_enabled" => PYS()->getOption("signal_watch_video_enabled"),
        "automatic_event_tel_link_enabled" => PYS()->getOption("signal_tel_enabled"),
        "automatic_event_email_link_enabled" => PYS()->getOption("signal_email_enabled"),
        "automatic_event_form_enabled" => PYS()->getOption("signal_form_enabled"),
        "automatic_event_download_enabled" => PYS()->getOption("signal_download_enabled"),
        "automatic_event_comment_enabled" => PYS()->getOption("signal_comment_enabled"),
        "automatic_event_scroll_enabled" => PYS()->getOption("signal_page_scroll_enabled"),
        "automatic_event_time_on_page_enabled" => PYS()->getOption("signal_time_on_page_enabled"),
        "automatic_event_scroll_value" => PYS()->getOption("signal_page_scroll_value"),
        "automatic_event_time_on_page_value" => PYS()->getOption("signal_time_on_page_value"),
        "automatic_event_adsense_enabled" => PYS()->getOption("signal_adsense_enabled"),
        "automatic_event_download_extensions" => PYS()->getOption("download_event_extensions"),
    ];
    PYS()->updateOptions($globalOptions);
}

function migrate_8_6_7() {
    if(PYS()->getOption( 'woo_advance_purchase_enabled' ,true)) {
        $globalOptions = array(
            "woo_advance_purchase_fb_enabled"   => true,
            'woo_advance_purchase_ga_enabled'   => true,
        );
    } else {
        $globalOptions = array(
            "woo_advance_purchase_fb_enabled"   => false,
            'woo_advance_purchase_ga_enabled'   => false,
        );
    }



    PYS()->updateOptions($globalOptions);
}

function migrate_8_3_1() {
    $globalOptions = array(
        "enable_page_title_param"          => !PYS()->getOption( 'enable_remove_page_title_param' ,false),
        'enable_content_name_param'        => !PYS()->getOption( 'enable_remove_content_name_param' ,false),
    );

    PYS()->updateOptions($globalOptions);
}

function migrate_8_0_0() {

    $globalOptions = array(
        "signal_click_enabled"          => isEventEnabled( 'click_event_enabled' ),
        "signal_watch_video_enabled"    => isEventEnabled( 'watchvideo_event_enabled' ),
        "signal_adsense_enabled"        => isEventEnabled( 'adsense_enabled' ),
        "signal_form_enabled"           => isEventEnabled( 'form_event_enabled' ),
        "signal_user_signup_enabled"    => isEventEnabled( 'complete_registration_event_enabled' ),
        "signal_download_enabled"       => isEventEnabled( 'download_event_enabled' ),
        "signal_comment_enabled"        => isEventEnabled( 'comment_event_enabled' )
    );

    PYS()->updateOptions($globalOptions);

    $gaOptions = array(
        'woo_view_item_list_enabled' => GA()->getOption('woo_view_category_enabled')
    );
    GA()->updateOptions($gaOptions);
}
