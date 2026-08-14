@extends('app')

@section('style')
<style>
    .vod-form-card {
        max-width: 760px;
        margin: 0 auto;
    }

    .vod-form-section + .vod-form-section {
        border-top: 1px solid #e3e6f0;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
    }

    .vod-preview {
        width: 100%;
        max-height: 340px;
        border-radius: .35rem;
        background: #111827;
    }
</style>
@endsection

@section('content')
@php($vod = $Vod ?? null)
@php($isStoreMode = app()->isStore())

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 class="h3 mb-1 text-gray-800">{{ $vod ? __('Edit VOD') : __('New VOD') }}</h1>
        <p class="text-muted mb-0">{{ __('Add a title, a description and the video file.') }}</p>
    </div>
    <a href="{{ route('vods.list') }}" class="btn btn-sm btn-secondary shadow-sm mt-3 mt-sm-0">
        <i class="fas fa-arrow-left fa-sm text-white-50"></i> {{ __('Back') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <strong>{{ __('Please review the information.') }}</strong>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $vod ? route('vods.update', $vod->id) : route('vods.store') }}" enctype="multipart/form-data">
    @csrf

    <div class="card mb-4 vod-form-card shadow-sm">
        <div class="card-body p-4">
            <div class="form-group">
                <label for="name">{{ __('Title') }}</label>
                <input
                    id="name"
                    type="text"
                    class="form-control"
                    name="name"
                    value="{{ old('name', $vod?->name) }}"
                    placeholder="{{ __('Enter the video title') }}"
                    required
                    autofocus
                >
            </div>

            <div class="form-group mb-0">
                <label for="description">{{ __('Description') }}</label>
                <textarea
                    id="description"
                    class="form-control"
                    name="description"
                    rows="5"
                    placeholder="{{ __('Briefly describe the video') }}"
                >{{ old('description', $vod?->description) }}</textarea>
            </div>

            <div class="vod-form-section">
                @if($vod && !$isStoreMode)
                    <div class="form-group">
                        <label>{{ __('Preview') }}</label>

                        @if($vod->is_playable)
                            <video id="vod-preview" class="vod-preview" controls preload="metadata">
                                <source src="{{ route('vods.stream', $vod->id) }}" type="{{ $vod->mime_type ?: 'video/mp4' }}">
                                {{ __('Your browser does not support video playback.') }}
                            </video>
                            <small class="form-text text-muted">
                                {{ __('Current preview for this video.') }}
                            </small>
                        @else
                            <div id="vod-preview-empty" class="alert alert-light border mb-0">
                                {{ __('No video available for preview yet.') }}
                            </div>
                            <video id="vod-preview" class="vod-preview d-none" controls preload="metadata"></video>
                        @endif
                    </div>
                @endif

                <div class="form-group mb-0">
                    <label for="file">{{ __('Video file') }}</label>
                    <input
                        id="file"
                        type="file"
                        class="form-control"
                        name="file"
                        accept="video/*,.mkv"
                        @required(!$vod?->path)
                    >
                    @if($vod?->original_filename)
                        <small class="form-text text-muted">
                            {{ __('Current file') }}: {{ $vod->original_filename }}.
                            {{ __('Choose another file only if you want to replace it.') }}
                        </small>
                    @else
                        <small class="form-text text-muted">{{ __('Select the video you want to make available.') }}</small>
                    @endif
                    @if($vod && !$isStoreMode)
                        <small class="form-text text-muted">{{ __('When selecting a new file, the preview updates before saving.') }}</small>
                    @endif
                </div>
            </div>

            <div class="vod-form-section d-flex justify-content-end">
                <button class="btn btn-primary px-4">
                    <i class="fas fa-save fa-sm mr-1"></i> {{ $vod ? __('Save changes') : __('Add video') }}
                </button>
            </div>
        </div>
    </div>
</form>

<script>
    (function () {
        var input = document.getElementById('file');
        var preview = document.getElementById('vod-preview');
        var emptyState = document.getElementById('vod-preview-empty');
        var objectUrl = null;

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0] ? input.files[0] : null;

            if (!file) {
                return;
            }

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }

            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.classList.remove('d-none');

            if (emptyState) {
                emptyState.classList.add('d-none');
            }
        });

        window.addEventListener('beforeunload', function () {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
            }
        });
    })();
</script>
@endsection
