<?php
/**
 * Created by PhpStorm.
 * User: Pawel
 * Date: 2015-10-14
 * Time: 12:00
 */
namespace App\Zibra\ActiveBox;



use App\ActiveBoxHistoryEvent;
use Intervention\Image\Facades\Image;

class ActiveBoxHistory extends ActiveBox
{



    private $templates = [
      'history_transparent' => [
          'background' => 'img/history-bg-transparent.png',
          'color' => '#ffffff',
          'font-size' => 25,
          'font-path' => 'fonts/Roboto/Roboto-Regular.ttf'
      ],
        'history_white' => [
            'background' => 'img/history-bg-white.png',
            'color' => '#000000',
            'font-size' => 25,
            'font-path' => 'fonts/Roboto/Roboto-Regular.ttf'
        ]
    ];


    /**
     * @param int $number
     * @return ActiveBoxHistoryEvent
     */
    private function getHistory($number = 2) {

        $events = ActiveBoxHistoryEvent::where('day', date('d'))
            ->where('month', date('m'))
            ->get();


        if(!$events->count()) {

            $empty = new ActiveBoxHistoryEvent();
            $empty->text = '';
            $empty->year = '';

            $events->push($empty);
        }


        return $events->random(1);

    }


    private function todayIs() {

        $month = [
            'stycznia',
            'lutego',
            'marca',
            'kwietnia',
            'maja',
            'czerwca',
            'lipca',
            'sierpnia',
            'września',
            'października',
            'listopada',
            'grudnia'
        ];

        return date('d') . ' ' . $month[date('m')-1];
    }


    /**
     * @return resource
     */
    public function createImage($template='history_transparent')
    {

        $temp = $this->templates[$template];

        $stamp = Image::make($temp['background']);

        $yStartPosition = 55;
        $lineHeight = 40;
        $xStartPosition = 30;

        $event = $this->getHistory(1);
        $text = $event->text;

        if(!$text) {
            return $stamp;
        }

        $pos = strpos($text, ' ', 50);

        if(!$pos) {
            return $stamp;
        }

        $n = str_split($text, $pos);

        $firstLine = trim(array_shift($n));
        $secondLine = trim(join('', $n));


        $stamp->text($this->todayIs() . ' ' . $event->year . ' '. $firstLine, $xStartPosition, $yStartPosition , function($font) use ($temp) {

            $font->size($temp['font-size']);
            $font->color($temp['color']);
            $font->file($temp['font-path']);
        });

        $stamp->text($secondLine, $xStartPosition, $yStartPosition + $lineHeight , function($font) use ($temp) {

            $font->size($temp['font-size']);
            $font->color($temp['color']);
            $font->file($temp['font-path']);
        });

        return $stamp;
    }
}
