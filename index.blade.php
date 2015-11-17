@extends('app')

@section('content')

    {{--@include('partials.back-button', ['url'=>'test', 'text' => 'wr�� do listy reklam'])--}}


    <div class="panel panel-primary">

            <div class="panel-heading">
                Zbuduj stronę startową
            </div>


            <div class="panel-body">

                <div class="row background-row">

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

                        {!! Form::open(array('route' => array('image.upload', 'place=c2a_generator_image_path'), 'method' => 'POST', 'id' => 'background-dropzone', 'class' => 'form single-dropzone', 'files' => true)) !!}
                        <button id="upload-submit-background" class="btn btn-default margin-t-5"><i class="fa fa-upload"></i> Wczytaj tło</button>
                        {!! Form::close() !!}

                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="preview-background"></div>
                    </div>

                </div>

                <hr>

                <div class="row c2a-row">

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

                        {!! Form::open(array('route' => array('image.upload', 'place=c2a_generator_image_path'), 'method' => 'POST', 'id' => 'c2a-dropzone', 'class' => 'form single-dropzone', 'files' => true)) !!}
                        <button id="upload-submit-c2a" class="btn btn-default margin-t-5"><i class="fa fa-upload"></i> Wczytaj c2a</button>
                        {!! Form::close() !!}

                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <div class="preview-c2a"></div>
                    </div>

                </div>

                <hr>

                <div class="row c2a-row">

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

                        Przesunięcie Call2Action w pionie w px

                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <input id="c2a-offset" type="number" onchange="homePageCreateor.refresh()" value="0"/>
                    </div>

                </div>


                <hr>

                <div class="row bottom-row">

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

                        Dolny BOX

                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

                        {!! Form::select('activebox',
                            array(
                            'brak' => 'BRAK',
                            'history_transparent' => 'Wydarzyło się (przezroczyste)',
                            'history_white' => 'Wydarzyło się (białe)'
                            ), null, ['onchange'=>'homePageCreateor.refresh()' ,'id' => 'bottomBox']) !!}

                    </div>

                </div>

                <hr>

                <div class="row bottom-row">

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">

                        Prawy BOX


                    </div>

                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">


                        {!! Form::select('activebox',
                            array(
                            'brak' => 'BRAK',
                            'currency_red' => 'Kursy walut (czerwony)',
                            'currency_black' => 'Kursy walut (czarny)'
                            ), null, ['onchange'=>'homePageCreateor.refresh()' ,'id' => 'rightBox']) !!}

                    </div>

                </div>

            </div>

    </div>


        <div class="panel panel-info">
            <div class="panel-heading">
                Podgląd
            </div>
            <div class="panel-body">
                <img id="preview" src="/png/380/" />
            </div>
        </div>


    <div class="well">
        <input id="home-page-name" type="text" placeholder="Wpisz nazwę strony startowej" maxlength="90"/>
        <br />
        <a href="#" onclick="homePageCreateor.savePage()"> zpisz stronę</a>
    </div>


    <div class="well">

        <a id="show-fullscreen" href="#" onclick="" target="_blank"> Pokaż obraz w pełnych rozmiarach</a>
    </div>



        <div id="preview-template" style="display: none;">
            <div class="dz-preview dz-file-preview">
                <div class="dz-details" style="">
                    <img data-dz-thumbnail />
                </div>
            </div>
        </div>


        <code id="code">
        </code>



    <script>


        $(document).ready(function() {


            var background = null,
                c2a = null,
                bottomBox = null,
                    rightBox = null,
                    c2aOffset = null;

            //Dropzone.js Options - Upload an image via AJAX.
            Dropzone.options.backgroundDropzone = {
                uploadMultiple: false,
                addRemoveLinks: false,
                dictDefaultMessage: '',
                previewsContainer: '.preview-background',
                previewTemplate: $('#preview-template').html(),
                thumbnailWidth: null,
                init: function() {
                    this.on("thumbnail", function(file) {
                        if($('.background-row .dz-details img').length >1) {
                            $('.background-row .dz-details img').first().remove();
                        }
                    });
                    this.on("success", function(file, res) {
//
                        background = res.path;
                        refresh();
                    });
                }
            };
            var backgroundDropzone = new Dropzone("#background-dropzone");

            $('#upload-submit-background').on('click', function(e) {
                e.preventDefault();
                //trigger file upload select
                $("#background-dropzone").trigger('click');
            });


            Dropzone.options.c2aDropzone = {
                uploadMultiple: false,
                addRemoveLinks: false,
                dictDefaultMessage: '',
                previewsContainer: '.preview-c2a',
                previewTemplate: $('#preview-template').html(),
                init: function() {
                    this.on("thumbnail", function(file) {
                        if($('.c2a-row .dz-details img').length >1) {
                            $('.c2a-row .dz-details img').first().remove();
                        }
                    });
                    this.on("success", function(file, res) {
                        c2a = res.path;
                        refresh();
                    });
                }
            };
            var c2aDropzone = new Dropzone("#c2a-dropzone");

            $('#upload-submit-c2a').on('click', function(e) {
                e.preventDefault();
                //trigger file upload select
                $("#c2a-dropzone").trigger('click');
            });

            function savePage() {

                var args = getArgs();
                args.push('name='+ $('#home-page-name').val());


                if(!$('#home-page-name').val()) {
                    alertify.error('Podaj nazwę!');
                    return;
                }
                args.push('_token={{ csrf_token() }}');


                $.post('/homepage', args.join('&'))
                        .then(function(data) {
                            console.info(data);
                            alertify.success('Zapisałem to!');
                        })
            }

            function refresh() {

               var args = getArgs();

                $('#preview').attr('src', '/generate?'+args.join('&')+'&width=360&broadcast=true');
                $('#show-fullscreen').attr('href', '/generate?'+args.join('&')+'&width=1280');

                $.get('/generate/definition?'+args.join('&'))
                        .then(function(data) {
                            $('#code').text(JSON.stringify(data))
                        })


//                console.info('/generate?'+args.join('&'));
            }

            function getArgs() {
                var args = [];

                bottomBox = $('#bottomBox').val();
                rightBox = $('#rightBox').val();
                c2aOffset = $('#c2a-offset').val();

                if(background != null) {
                    args.push('background='+background);
                }
                if(bottomBox != null) {
                    args.push('bottomBox='+bottomBox);
                }
                if(rightBox != null) {
                    args.push('rightBox='+rightBox);
                }
                if(c2a != null) {
                    args.push('c2a='+c2a);
                }
                if(c2aOffset != null) {
                    args.push('c2aOffset='+c2aOffset);
                }

                return args;
            }


            homePageCreateor.refresh = refresh;
            homePageCreateor.savePage = savePage;

        });

        //we want to manually init the dropzone.
        Dropzone.autoDiscover = false;

        var homePageCreateor = {};

    </script>



@endsection

