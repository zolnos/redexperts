<?php namespace App\Http\Controllers;

use App\Ad;
use App\Campaign;
use App\Events\CampaignDefinitionWasChanged;
use App\Helpers\ModelTableViewHelper;
use App\Http\Requests;

use App\Place;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdsController extends Controller {

	//

    public function __construct() {

        $this->middleware('auth');
    }


    public function index(Ad $model) {


        $ads = Ad::all();

        return view('ads.list', compact('ads'));
    }

    public function create($campaignId) {

        $campaign = Campaign::find($campaignId);

        $create = true;

        $ad = new Ad;

        $places = $campaign->placeWithDefinition();
//        dd($places);


        $relatedApps = $ad->apps;

        $apps = $campaign->apps->lists('name', 'id');

        $relatedPlaces = $ad->places->lists('id');

        return  view('ads.manage', compact('campaign', 'create', 'ad', 'places', 'relatedApps', 'apps', 'relatedPlaces'));

    }


    /**
     *
     * Updates the model
     *
     * @param $id
     * @param Requests\StoreAdRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update($id, Requests\StoreAdRequest $request) {


        $ad = Ad::find($id);

        $ad->fill($request->all());

        $ad->apps()->sync($request->get('apps', []));

        $ad->places()->sync($request->get('places', []));

        if($ad->save()) {
            flash()->success('Zapisałem zmiany.');

            Event::fire(new CampaignDefinitionWasChanged($ad->campaign));
        }
        else {
            flash()->error('Nie zapisałem. Jakiś błąd. Gadaj z adminem.');
        }

        return redirect('/ads/'.$id.'/edit');

    }


    public function store(Requests\StoreAdRequest $request)
    {

        if($ad = Ad::create($request->all())) {

            $ad->apps()->sync($request->get('apps', []));

            $ad->places()->sync($request->get('places', []));

            flash()->success('Dodano!');

            Event::fire(new CampaignDefinitionWasChanged($ad->campaign));

            return redirect('/ads/'.$ad->id.'/edit');

        }
        else {

            flash()->error('System error when adding new campaign. Talk to admin.');

            return redirect()->back()->withInput();
        }
    }


    public function show($id) {

        $ad = Ad::find($id);

        $r = $ad->sumIt();

        $campaign = $ad->campaign;

        if(!$ad) {
            flash()->error('Brak zdefiniowanej reklamy o id: '. $id);
            return redirect('ads');
        }

        return view('ads.show', compact('ad', 'campaign'));
    }


    /**
     *
     * Displays an edit form
     *
     * @param $id
     * @return \Illuminate\View\View
     */
    public function edit($id) {

        $ad = Ad::find($id);

        $create = false;

        $campaign = $ad->campaign;

        $places = $campaign->placeWithDefinition();

        $relatedApps = $ad->apps->lists('id');

        $apps = $campaign->apps->lists('name', 'id');

        $urls = $ad->urls;

        $relatedPlaces = $ad->places->lists('id');

        return view('ads.manage', compact('ad', 'campaign', 'create', 'places', 'relatedApps', 'apps', 'relatedPlaces', 'urls'));

    }


    public function destroy($id) {

        $ad = Ad::find($id);

        if(!$ad) {

            flash()->error('Nie znalazłem reklamy z id:'.$id. ', Zapytaj admina co jest grane. ');
            return redirect('campaign');
        }

        $campaign = $ad->campaign;

        if($ad->delete()) {
            flash()->success('Usunąlem.');
            return redirect('campaign/'.$campaign->id.'?tab=ads');
        }
        else {
            return redirect()->back();
        }



    }

}
