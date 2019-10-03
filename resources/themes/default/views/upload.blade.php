@extends('master')

@section('page', 'upload')

@section('content')
    <h1>{{ config('app.name') }}</h1>
    <h2>@lang('app.upload-files-title')</h2>

    <div id="upload-column" class="wide">
        <form action="{{ route('upload.store') }}" class="dropzone" id="upload-form">
            <div class="dz-message needsclick">
                @lang('app.drop-file-here')
            </div>
            <p class="max-filesize-warning">
                @lang('app.maximum-filesize') {{ Upload::fileMaxSize(true) }}
            </p>
        </form>
    </div>

    <div id="settings-column">
        <label>@lang('app.preview-link')</label>
        <p class="link"><a href="#" target="_blank" id="preview-link"></a></p>
        <button class="clippy" data-clipboard-target="#preview-link">&nbsp;</button>

        <label>@lang('app.direct-link')</label>
        <p class="link"><a href="#" target="_blank" id="download-link"></a></p>
        <button class="clippy" data-clipboard-target="#download-link">&nbsp;</button>

        <label>@lang('app.delete-link')</label>
        <p class="link"><a href="#" id="delete-link"></a></p>
        <button class="clippy" data-clipboard-target="#delete-link">&nbsp;</button>
    </div>

    <p class="spacer">&nbsp;</p>

    <div id="files-list">
        <h2>@lang('app.files-list')</h2>
        <p>@lang('app.you-can-add-files')</p>

        <div class="preview-tpl">
            <div class="file-row">
                <div class="dz-filename"><span data-dz-name></span></div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
                <div class="dz-result">&nbsp;</div>
                <div class="dz-size"><span data-dz-size></span></div>
                <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress>&nbsp;</span></div>
                <p class="spacer">&nbsp;</p>
            </div>
        </div>
    </div>


    <script type="text/javascript">

        // Getting file preview template and removing
        var previewtpl = $('.preview-tpl').html();
        $('.preview-tpl').remove();

        // Getting count of successful files
        var success = 0;

        Dropzone.options.uploadForm = {
            url: '{{ route('upload.store') }}',
            createImageThumbnails: false,
            clickable: true,

            previewsContainer: 'div#files-list',
            previewTemplate: previewtpl,
            paramName: 'file',
            maxFiles: '{{ config('sharing.max_files') }}',
            maxFilesize: '{{ round(Upload::fileMaxSize() / 1000000) }}',
            parallelUploads: 1, // TODO : increase this limit but must fix bug first when creating folders
            dictMaxFilesExceeded: '@lang('app.files-count-limit')',
            dictFileTooBig: '@lang('app.file-too-big')',
            headers: {
                'X-Upload-Bundle': '{{ $bundle_id }}'
            },
            complete: function(file) {
                $('#files-list').show();
                $(file.previewElement).children('.dz-progress').hide('fast');
            },
            queuecomplete: function () {
                // Do not complete batch if not file was uploaded
                if ($('.file-row.dz-success').length <= 0) return false;

                $.ajax({
                    async: true,
                    dataType: 'json',
                    headers: {
                        'X-Upload-Bundle': '{{ $bundle_id }}'
                    },
                    method: 'POST',
                    url: '{{ route('upload.complete') }}'

                    }).done(function (data) {
                        if (data.result == true) {
                            $('#preview-link').attr('href', data.bundle_url).html(data.bundle_url);
                            $('#download-link').attr('href', data.download_url).html(data.download_url);
                            $('#delete-link').attr('href', data.delete_url).html(data.delete_url);

                            $('#upload-column').removeClass('wide');
                            $('#settings-column').show();
                        }
                    });
            }
        };

    </script>
@endsection
