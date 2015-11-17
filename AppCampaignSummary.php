<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AppCampaignSummary extends Model {

	//
    protected $fillable = ['app_campaign_summary_id', 'summary'];


    /**
     *
     * Sums adverts appearing for each campaign
     *
     */
    public static function sumIt() {

        // fetch all campaigns
        $campaigns = Campaign::all();


        // fetch last synchronization
        $synchronization = SummarySynchronization::find('app_campaign');

        if(!$synchronization || !$campaigns->count()) {
           return;
        }

        $synchronizationTime = $synchronization->updated_at;

        $campaigns->each(function(Campaign $campaign) use ($synchronizationTime) {

           self::_sumIt($campaign->id, $synchronizationTime);
        });

        // update timestamps
        $synchronization->touch();

    }


    private static function _sumIt($campaignId, Carbon $synchronizationTime) {

        // fetch main data (grouped summaries for campaigns)
        $data = Synch::countForCampaignIdAndGroupByApp($campaignId, $synchronizationTime);


        // no data - get the fuck out of here
        if(!$data->count()) {
            return;
        }

        // create array of mapping (app remote id -> id)
        $appsMappingTabel = App::all()->lists('id', 'remote_id');



        // iterate data
        $data->each(function($row) use ($appsMappingTabel) {

            // check if app exists. no -> continue iteration (next row please)
            if(!isset($appsMappingTabel[$row->remote_id])) {
                return;
            }

            $appId = $appsMappingTabel[$row->remote_id];

            // fetch relation id
            $relation = AppCampaign::getIt($row->campaign_id, $appId);
            $relationId = $relation ? $relation->id : null;

            // no relation? goodby!
            if(!$relationId) {
                return;
            }

            $appCampaignSummary = AppCampaignSummary::where('app_campaign_summary_id', $relationId)->first();

            // sych_id for summaries equals 0 means no record in db
            if(!$appCampaignSummary) {

                AppCampaignSummary::create([
                    'app_campaign_summary_id' => $relationId,
                    'summary' => $row->summary
                ]);
            }
            // update existing
            else {

                // check if record exists
                if($appCampaignSummary->count()) {
                    $appCampaignSummary->summary = (int)$row->summary + (int)$appCampaignSummary->summary;
                    $appCampaignSummary->save();
                }
            }
        });

    }


    /**
     *
     * Returns array of apps and summary for apps
     *
     * @param $campaignId
     * @return array
     */
    public static function fetchForCampaign($campaignId) {

        $appIdsForSummary = AppCampaign::where('campaign_id', $campaignId)->lists('app_id', 'id');



        $appCampaignSummaryIds = array_keys($appIdsForSummary);

        $appsNames = App::names(array_values($appIdsForSummary));

        $summary = AppCampaignSummary::whereIn('app_campaign_summary_id', $appCampaignSummaryIds)->lists('summary', 'app_campaign_summary_id');



        $data = [];

        foreach($appCampaignSummaryIds as $id) {

            $data[] = [
                'summary' => isset($summary[$id]) ?$summary[$id] : 0,
                'appName' => $appsNames[$appIdsForSummary[$id]],
                'appId' => $appIdsForSummary[$id]
            ];
        }

        return $data;

    }


}
