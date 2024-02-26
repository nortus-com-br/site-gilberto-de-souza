<?php

namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

$filters = [
    "traffic_source" => "Traffic source",
    "traffic_landing" => "Landing page",
    "utm_source" => "utm_source",
    "utm_medium" => "utm_medium",
    "utm_campaing" => "utm_campaing",
    "utm_term" => "utm_term",
    "utm_content" => "utm_content",
];
$visitModel = PYS()->getOption('visit_data_model');
?>

<div class="wrap" id="pys">
    <h1><?php _e( 'PixelYourSite Pro', 'pys' ); ?></h1>
    <div class="pys_stat">
        <div class="row">
            <div class="col">

                <h2 class="section-title">WooCommerce Reports (beta)</h2>

            </div>
        </div>

        <?php

            $status = OrderStatistics()->getSyncStatus();
            if($status == OrderStatistics::$SYNC_STATUS_START) :
                $lastPage = OrderStatistics()->getSyncPage();
                $selected = OrderStatistics::getSelectedOrderStatus();
                $countPages = ceil(OrderStatistics()->getWooOrdersCount($selected)/OrderStatistics()->perPage);

        ?>
            <div class="row">
                <div class="col">
                    <div class="text-center h2">Preparing your new settings, don't close this page.<span class="spinner is-active" style="float:none; vertical-align: bottom;"></span></div>
                    <div class="progress stat_progress" data-page="<?=$lastPage?>" data-max_page="<?=$countPages?>">
                        <div class="progress-bar" role="progressbar" style="width: 0;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                </div>
            </div>
        <?php else: ?>

            <div class="row">
                <div class="col">
                    <ul class="pys_stats_filters">
                        <?php foreach ($filters as $filter => $name) : ?>
                            <li class="filter" data-type="<?=$filter?>">
                                <?=$name?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="row stat_data ">

                <div class="col">
                    <div class="loading text-center">
                        <span class="spinner .is-active"></span>Loading ...
                    </div>
                    <div class="global_data">
                        <select class="pys_stat_time mt-3">
                            <option value="yesterday" >Yesterday</option>
                            <option value="today" >Today</option>
                            <option value="7" >Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="current_month" >Current month</option>
                            <option value="last_month" >Last month</option>
                            <option value="year_to_date" >Year to date</option>
                            <option value="last_year" >Last year</option>
                            <option value="custom" >Custom dates</option>
                        </select>
                        <div class="pys_stat_time_custom mt-3">
                            <input type="text" class="datepicker datepicker_start mr-2" placeholder="From"/>
                            <input type="text" class="datepicker datepicker_end mr-2"placeholder="To"/>
                            <button class="btn btn-primary load">Load</button>
                        </div>

                        <select class="pys_visit_model mt-3">
                            <option value="first_visit" <?=selected("first_visit",$visitModel)?>>First Visit</option>
                            <option value="last_visit" <?=selected("last_visit",$visitModel)?>>Last Visit</option>
                        </select>

                        <canvas id="pys_stat_graphics" class="mt-3" width="400" height="100"></canvas>
                        <div class="text-lg-right mt-3">
                            <form class="report_form" target="_blank" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="label"/>
                                <input type="hidden" name="model"/>
                                <input type="hidden" name="start_date"/>
                                <input type="hidden" name="end_date"/>
                                <input type="hidden" name="type"/>
                                <input type="hidden" name="filter_type"/>
                                <input type="hidden" name="export_csw" value="woo_report"/>
                                <button class="btn btn-primary report" >Download</button>
                            </form>
                        </div>
                        <table class="pys_stat_info mt-3 table ">
                            <thead>
                            <tr>
                                <th class="title"></th>
                                <th class="num_sale">Orders:</th>
                                <th >Gross sales:</th>
                                <th >Net sales:</th>
                                <th >Total sales:</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <ul class="pagination">

                        </ul>
                    </div>
                    <div class="single_data">
                        <select class="pys_stat_time">
                            <option value="yesterday" >Yesterday</option>
                            <option value="today" >today</option>
                            <option value="7" >Last 7 days</option>
                            <option value="30" selected>Last 30 days</option>
                            <option value="current_month" >Current month</option>
                            <option value="last_month" >Last month</option>
                            <option value="year_to_date" >Year to date</option>
                            <option value="last_year" >Last year</option>
                            <option value="custom" >Custom dates</option>
                        </select>
                        <div class="pys_stat_time_custom mt-3">
                            <input type="text" class="datepicker datepicker_start mr-2" placeholder="From"/>
                            <input type="text" class="datepicker datepicker_end mr-2"placeholder="To"/>
                            <button class="btn btn-primary load">Load</button>
                        </div>

                        <div class="d-flex mt-3">
                            <span class="single_filter mr-3"></span>
                            <button class="btn single_back">< Back</button>
                        </div>
                        <canvas id="pys_stat_single_graphics" width="400" height="100"></canvas>
                        <div class="text-lg-right mt-3">
                            <form class="report_form" target="_blank" method="post" enctype="multipart/form-data">
                                <input type="hidden" name="start_date"/>
                                <input type="hidden" name="end_date"/>
                                <input type="hidden" name="filter_id"/>
                                <input type="hidden" name="type"/>
                                <input type="hidden" name="model"/>
                                <input type="hidden" name="group_by_date"/>
                                <input type="hidden" name="filter_type"/>
                                <input type="hidden" name="export_csw" value="woo_single_report"/>
                                <button class="btn btn-primary report" >Download</button>
                            </form>

                        </div>
                        <div class="btn-group order_buttons " role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-primary date">Date</button>
                            <button type="button" class="btn btn-secondary order">Order ID</button>
                        </div>

                        <table class="pys_stat_single_info mt-3 table">
                            <thead>
                            <tr>
                                <th class="row_title">Date</th>
                                <th class="num_sale">Orders:</th>
                                <th >Gross sales:</th>
                                <th >Net sales:</th>
                                <th >Total sales:</th>
                            </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                        <ul class="pys_stat_single_info_pagination pagination"></ul>
                    </div>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col">

                    <h2 class="section-title">Settings</h2>

                </div>
            </div>

            <div class="row">
                <div class="col">
                    <h4 class="label">Active orders status:</h4>

                    <select class="form-control pys-select2"
                            data-placeholder="Select Order status"
                            id="woo_stat_order_statuses"  style="width: 100%;"
                            multiple>

                        <?php
                        $selected = OrderStatistics::getSelectedOrderStatus();
                        foreach ( wc_get_order_statuses() as $option_key => $option_value ) : ?>
                            <option value="<?php echo esc_attr( $option_key ); ?>"
                                <?php selected( in_array( $option_key, $selected ) ); ?>
                            >
                                <?php echo esc_attr( $option_value ); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <div class="col-4">
                    <button class="btn btn-block btn-sm btn-save btn-save-woo-stat">Save Settings</button>

                </div>
            </div>

        <?php endif; ?>


    </div>
</div>