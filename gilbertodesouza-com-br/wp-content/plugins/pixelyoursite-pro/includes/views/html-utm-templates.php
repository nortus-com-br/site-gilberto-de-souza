<?php
namespace PixelYourSite;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?>

<div class="wrap" id="pys">
    <h1><?php _e( 'UTM Templates', 'pys' ); ?></h1>
    <div class="pys_utm_templates container">


        <div class="row">
            <div class="col">
                <div class="mb-2" >Facebook:</div>

                <div class="utm_template mt-2 copy_text">?utm_source=facebook&utm_medium=paid&utm_campaign={{campaign.name}}&utm_term={{adset.name}}&utm_content={{ad.name}}&fbadid={{ad.id}}</div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="mb-2" >Google Analytics:</div>

                <div class="utm_template mt-2 copy_text">?utm_source=google&utm_medium=paid&utm_campaign={campaignid}&utm_content={adgroupid}&utm_term={keyword}&gadid={creative}</div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="mb-2" >TikTok:</div>

                <div class="utm_template mt-2 copy_text">?utm_source=tiktok&utm_medium=paid&utm_campaign=__CAMPAIGN_NAME__&utm_term=__AID_NAME__&utm_content=__CID_NAME__&ttadid=__CID__</div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col">
                <div class="mb-2" >Pinterest:</div>

                <div class="utm_template mt-2 copy_text">?utm_source=pinterest&utm_medium=paid&utm_campaign={campaign_name}&utm_term={adgroup_name}&utm_content={creative_id}&padid={adid}</div>
            </div>
        </div>

        <div class="row mt-4">
            <div  class="col">
                <div class="mb-2" >Bing:</div>

                <div class="utm_template mt-2 copy_text">?utm_source=bing&utm_medium=paid&utm_campaign={campaign}&utm_content={AdGroupId}&utm_term={AdGroup}&bingid={CampaignId}</div>
            </div>
        </div>
    </div>
</div>