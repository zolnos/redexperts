<?php namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ad extends Model {

	//

    protected $fillable = ['label', 'image', 'name', 'campaign_id', 'enabled', 'red', 'green', 'blue', 'yellow', 'ok'];

    protected $casts = [ 'enabled' => 'boolean'];


    /**
     *
     * Ad can only belongs to one campaign
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function campaign() {
        return $this->belongsTo('App\Campaign');
    }

    /**
     *
     * Relationship many to many with places
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function places() {
        return $this->belongsToMany('App\Place');
    }

    /**
     *
     * Ad can belong to many apps
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function apps() {
        return $this->belongsToMany('App\App');
    }

    /**
     *
     * Fetch related apps names
     *
     * @return array
     */
    public function relatedAppsNames() {

        $ids = $this->apps;

        $names = $ids->map(function($app){
           return $app->name;
        })
            ->toArray();

        return $names;
    }

    /**
     *
     * Fetch related places names
     *
     * @return array
     */
    public function relatedPlacesNames() {

        $ids = $this->places;

        $names = $ids->map(function($place){
            return $place->definition->name . ' ('.$place->zone->name.')';
        })
            ->toArray();

        return $names;
    }

    /**
     *
     * Overrided delete method.
     * Check for additional conditions before delete.
     *
     * @return bool|null
     * @throws \Exception
     */
    public function delete() {

        if($this->enabled == true) {

            flash()->warning('Człowieku, nie usunę włączonej reklamy! Wyłącz ją wcześniej.');
            return false;
        }

        AdApp::where('ad_id', $this->id)->delete();

        return parent::delete();

    }


    /**
     *
     * Returns urls related to ad
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function urls() {
        return $this->hasMany('\App\AdUrl');
    }

    /**
     *
     * Relation to summary
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function summaries() {
        return $this->hasMany('\App\AdSummary');
    }

    /**
     *
     * summarize
     *
     *
     */
    public function sumIt() {

        //pobierz ostatni czas synchronizacji
        $lastSynchronizationTime = 0;

        $a = [];

        //pobierz datę od ktorej należy synchronizować
        $last = $this->summaries->first();

        if($last) {
            $lastSynchronizationTime = $last -> updated_at;
//            dd($lastSynchronizationTime);
        }

        $lastSummaryTime = null;

            $count = DB::table('synches')
                ->where('date', '>', $this->created_at)
                ->where('date', '>', $lastSynchronizationTime)
                ->where('advert_id', $this->id)
                ->groupBy('action')
                ->select('action', DB::raw('count(*) as total'))
                ->lists('total', 'action');

//        dd($count->getBindings());

            if(count($count)) {

                $alreadyForAdvert = $this->summaries->lists('summary', 'action');

                $this->summaries->each(function($sum) { $sum->delete(); });

                foreach ($count as $action => $total) {

                    AdSummary::create([
                        'ad_id' => $this->id,
                        'action' => $action,
                        'summary' => $total + (isset($alreadyForAdvert[$action]) ? $alreadyForAdvert[$action] : 0)
                    ]);

                    $a[$action] = $total + (isset($alreadyForAdvert[$action]) ? $alreadyForAdvert[$action] : 0);

                }
            }

        return $a;
    }

}
