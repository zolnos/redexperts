<?php
/**
 * Created by PhpStorm.
 * User: Pawel
 * Date: 2015-10-22
 * Time: 12:06
 */
namespace App\Zibra\ActiveBox;
use Intervention\Image\Facades\Image;

class HPGenerator {


    public function create($params, $returnAsImage = false) {

        $background = isset($params['background']) ? $params['background'] : false;
        if(!$background) {
            return $this->error('Tło jest wymagane');
        }

        $img = $this->background($background);


        $c2a = isset($params['c2a']) ? $params['c2a']: false ;
        if($c2a) {
            $c2aOffset = isset($params['c2aOffset']) ? $params['c2aOffset']: 0 ;
            $img = $this->c2a($img, $c2a, $c2aOffset);
        }

        $bottomBox = isset($params['bottomBox']) ? $params['bottomBox'] : false;

        if($bottomBox) {
            $img = $this->bottomBox($img, $bottomBox);
        }

        $rightBox = isset($params['rightBox']) ? $params['rightBox'] : false;

        if($bottomBox) {
            $img = $this->rightBox($img, $rightBox);
        }


        $showBroadcast = isset($params['broadcast']) ? true : false;
        if($showBroadcast) {

            $nr = rand(1,2);
            $broadcast = Image::make('images/broadcast/'.$nr.'.jpg');
            $broadcast->resize(1018, 580);
            $img->insert($broadcast, 'top-left');
        }

        $width = isset($params['width']) ? $params['width'] : false;
        if($width) {
            $img = $this->width($img, $width);
        }




        if($returnAsImage === true) {
            return $img;
        }

        return $img->response('png');

    }

    public function error($text) {

        $plain = Image::make('img/error.jpg');

        $plain->text($text, 200, 340, function($font) {
            $font->size(54);
            $font->color('#b10707');
            $font->file('fonts/Roboto/Roboto-Regular.ttf');
            $font->angle(10);
        });

        $plain = $this->width($plain, 380);
        return $plain->response('jpg');
    }


    private function background($image) {

        $plain = Image::make('img/plain.png');
        $plain->resize(1280, 720);

//        dd(\App\Settings::config('c2a_generator_image_path').$image);

        if($image != null) {

            $background = Image::make(\App\Settings::config('c2a_generator_image_path').$image);
            $background->resize(1280, 720);
            $background->crop(262,720,1018,0);
            $plain->insert($background,'top-right');

            $background = Image::make(\App\Settings::config('c2a_generator_image_path').$image);
            $background->resize(1280, 720);
            $background->crop(1018,140,0,580);
            $plain->insert($background,null,0, 580);
        }

        return $plain;
    }

    private function width($img, $width) {
        return $img->resize(null, $width, function ($constraint) {
            $constraint->aspectRatio();
        });
    }

    private function c2a($img, $c2a, $offset = 0) {
        return $img->insert(\App\Settings::config('c2a_generator_image_path').$c2a, 'bottom-right', 0, $offset);
    }

    /**
     * @param $img
     * @param $kind
     * @return mixed
     */
    private function rightBox($img, $kind) {

        switch($kind) {
            case 'currency_red':
            case 'currency_black':
                $box = new ActiveBoxCurrency();
                $currency = $box->createImage($kind);
                $img -> insert($currency, 'top-right');
                return $img;
                break;
        }
        return $img;

    }

    /**
     * @param $img
     * @param $kind
     * @return mixed
     */
    private function bottomBox($img, $kind) {
        switch($kind) {
            case 'history_white':
            case 'history_transparent':
                $box = new ActiveBoxHistory();
                $currency = $box->createImage($kind);
                $img -> insert($currency, 'bottom-left');
                return $img;
                break;
        }
        return $img;
    }

}