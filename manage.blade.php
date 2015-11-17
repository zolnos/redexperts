@extends('app')

@section('content')

    @include('partials.back-button', ['url'=>'campaign/'.$campaign->id.'?tab=ads', 'text' => 'wróć do listy reklam'])

    <div class="panel panel-primary">

        <div class="panel-heading">
            {{$create ? 'Dodaj' : 'Edytuj'}} reklamę
        </div>

        <div class="panel-body">

            @include('partials.form-errors')

            {!! Form::open(array('url' => $create ? 'ads' : 'ads/'.$ad->id, 'method' => $create ? 'POST' : 'PUT' )) !!}

                {!! Form::hidden('campaign_id', $campaign->id) !!}
                {{--{!! Form::hidden('_token', csrf_token() ) !!}--}}

                {!! Form::label('name', 'Tytuł') !!}
                {!! Form::text('name', $ad->name, ['class'=> 'form-control']) !!}
                <span class="help-block"></span>

                {!! Form::label('image', 'Link do obrazka...') !!}
                {!! Form::text('image', $ad->image, ['class'=> 'form-control', 'readonly'=>'readonly']) !!}
                <span class="help-block">Obrazek wczytać możesz na dole strony, men.</span>

                {!! Form::label('automatic_image', '... lub generowany obrazek') !!}
                {!! Form::select('automatic_image', \App\HomePage::selectAll(true), null, ['id'=>'auto-generated-image', 'class'=> 'form-control', 'onchange'=>'generatedChanged()']) !!}
                <span class="help-block">Wybierz generator.</span>


                {{--@if($ad->image)--}}
                    <img id="visible-image" src="{{$ad->image ?
                        ( strpos($ad->image, 'http') !== FALSE ?  $ad->image : \App\Settings::config('advert_image_base_path').$ad->image)
                        : "#"}}" style= "width:400px; padding:20px" ></img>
                    <span class="help-block"></span>
                {{--@endif--}}


                @if(!$create)

                    {!! Form::label('enabled', 'Włączone') !!}
                    {!! Form::select('enabled', ['Nie', 'Tak'], $ad->enabled ? 1 : 0, ['class'=> 'form-control']) !!}


                @endif


                <hr />
                <br />

                {!! Form::label('places', 'Powiązane miejsca') !!}
                {!! Form::select('places[]', $places, $relatedPlaces, ['class'=> 'form-control', 'multiple']) !!}
                <span class="help-block"></span>

                {!! Form::label('apps', 'Powiązane aplikacje') !!}
                {!! Form::select('apps[]', $apps, $relatedApps, ['class'=> 'form-control', 'multiple']) !!}
                <span class="help-block"></span>


                <hr />

            {{--{!! Form::label('red', 'Reakcja na RED') !!}--}}
            {{--{!! Form::text('red', $ad->red ? $ad->red : 'STANDARD', ['class'=> 'form-control']) !!}--}}
            {{--<span class="help-block">Przekierowanie po naciśnięciu RED. Standardowe zachowanie wpisz STANDARD</span>--}}

            {!! Form::label('green', 'Reakcja na GREEN') !!}
            {!! Form::text('green', $ad->green, ['class'=> 'form-control']) !!}
            <span class="help-block">Przekierowanie po naciśnięciu GREEN.</span>

            {!! Form::label('blue', 'Reakcja na BLUE') !!}
            {!! Form::text('blue', $ad->blue , ['class'=> 'form-control']) !!}
            <span class="help-block">Przekierowanie po naciśnięciu BLUE.</span>

            {!! Form::label('yellow', 'Reakcja na YELLOW') !!}
            {!! Form::text('yellow', $ad->yellow, ['class'=> 'form-control']) !!}
            <span class="help-block">Przekierowanie po naciśnięciu YELLOW. </span>

            {{--{!! Form::label('ok', 'Reakcja na OK') !!}--}}
            {{--{!! Form::text('ok', $ad->ok, ['class'=> 'form-control']) !!}--}}
            {{--<span class="help-block">Przekierowanie po naciśnięciu OK.</span>--}}


            <hr />
            @if(!$create)
                <div class="list-group" id="links-to-fire">
                    <a href="#" class="list-group-item disabled">
                        Lista linków do odpalenia w momencie pojawienia się reklamy na ekranie (dodać możesz na dole strony)
                    </a>

                    @foreach($urls as $url)
                        <a id="url-{{$url->id}}" class="list-group-item">{{$url->url}}<span class="badge" style="cursor: pointer" onclick="destroyUrl({{$url->id}})"">zniszcz gnoja</span></a>
                    @endforeach

                </div>
            @endif

                {!! Form::label('', '') !!}
                {!! Form::button($create ? 'Dodaj' : 'Zapisz', ['type'=>'subimt', 'class'=> 'btn btn-success form-control ']) !!}

            {!! Form::close() !!}
        </div>

    </div>


    @if(!$create)
        <div class="panel panel-info" id="add-url-block">

            <div class="panel-heading">
                Linki do odpalenia
            </div>

            <div class="panel-body">

                    {!! Form::open(array('url' => 'adurl', 'method' => 'POST', 'id' => 'add-url-form')) !!}

                        {!! Form::label('url', 'Dodaj URL do odpalenia przy wyświetleniu reklamy') !!}
                        {!! Form::text('url', '', ['class'=> 'form-control', 'id' =>'url']) !!}

                        <br />

                        {!! Form::button('Dodaj ten wpisany wyżej, nowy link', ['type'=> 'submit', 'class'=> 'btn btn-primary form-control']) !!}

                    {!! Form::close() !!}
            </div>
        </div>
    @endif





    <div class="panel panel-info">

        <div class="panel-heading">
           Wczytaj obrazek
        </div>

        <div class="panel-body">

            <div class="col-lg-12 text-center">
                {!! Form::open(array('route' => 'image.upload', 'method' => 'POST', 'id' => 'my-dropzone', 'class' => 'form single-dropzone', 'files' => true)) !!}
                    <button id="upload-submit" class="btn btn-default margin-t-5"><i class="fa fa-upload"></i> Upload Picture</button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>


    @if(!$create)
        {!! Form::open(array('url' => 'ads/'.$ad->id, 'method' => 'DELETE')) !!}

        {!! Form::button('usuń', ['type'=> 'submit', 'class'=> 'btn btn-danger']) !!}

        {!! Form::close() !!}
    @endif

    <script>


        $(document).ready(function() {

            //Dropzone.js Options - Upload an image via AJAX.
            Dropzone.options.myDropzone = {
                uploadMultiple: false,
                // previewTemplate: '',
                addRemoveLinks: false,
                // maxFiles: 1,
                dictDefaultMessage: '',
                init: function() {
                    this.on("addedfile", function(file) {
                        // console.log('addedfile...');
                    });
                    this.on("thumbnail", function(file, dataUrl) {
                        // console.log('thumbnail...');
                        $('.dz-image-preview').hide();
                        $('.dz-file-preview').hide();
                    });
                    this.on("success", function(file, res) {
                        console.log('upload success...', res);
                        $('#img-thumb').attr('src', res.path);
                        $('input[name="pic_url"]').val(res.path);
                        $('input[name="image"]').val(res.path);
                        $('#visible-image').attr('src', '{{\App\Settings::config('advert_image_base_path')}}'+res.path);
                        $('.dz-preview').prepend('<span>'+res.path+'</span>');

                        console.info( '{{\App\Settings::config('advert_image_base_path')}}'+res.path);

                    });
                }
            };
            var myDropzone = new Dropzone("#my-dropzone");

            $('#upload-submit').on('click', function(e) {
                e.preventDefault();
                //trigger file upload select
                $("#my-dropzone").trigger('click');
            });

        });

        //we want to manually init the dropzone.
        Dropzone.autoDiscover = false;



        $('#add-url-form').on('submit', function(event) {

            event.preventDefault();

            var url = $('#url').val();

            window.zibra_adserver.blockElement($('#add-url-block'));

            $.ajax({
                'url': '/adurl',
                'method': 'post',
                'type': 'json',
                cache    : false,
                data: {
                    '_token': '{{ csrf_token() }}',
                    'url' : url,
                    'ad_id': '{{$ad->id}}'
                },
                success: function(data) {
                    $('#add-url-block').unblock();
                    $('#url').val('');
                    alertify.success('OK!');

//                    $('#links-to-fire').append('<a href="'+data.url+'" target="_blank" class="list-group-item">'+data.url+'</a>');


                    $('#links-to-fire').append('<a id="url-'+data.id+'" class="list-group-item">'+data.url+'<span class="badge" style="cursor: pointer" onclick="destroyUrl('+data.id+')"">zniszcz gnoja</span></a>');

                    console.info(data);
                },
                error: function(data) {
                    $('#add-url-block').unblock();
                    alertify.error(JSON.stringify(JSON.parse(data.responseText), null, '\t'));
                }
            });

            return false;
        });


        function destroyUrl(id) {


            window.zibra_adserver.blockElement($('#url-'+id));

            $.ajax({
                'url': '/adurl/'+id,
                'method': 'post',
                'type': 'json',
                cache    : false,
                data: {
                    '_token': '{{ csrf_token() }}',
                    '_method': 'DELETE'
                },
                success: function(data) {
                    $('#url-'+data).remove();
                    alertify.success('Gnój zniszczony!');
                },
                error: function(data) {
                    alertify.error(JSON.stringify(JSON.parse(data.responseText), null, '\t'));
                }
            });
        }


        function generatedChanged() {
            console.info('Yes it is true', $('#auto-generated-image').val());
            var val = $('#auto-generated-image').val();

            if(val != '0') {
                $('#visible-image').attr('src', '{{\App\Settings::config('c2a_generator_image_url')}}' + val);
                $('#image').val('{{\App\Settings::config('c2a_generator_image_url')}}' + val);
            }

        }


    </script>


@endsection

