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
</style>
@endsection

@section('content')
@php($vod = $Vod ?? null)

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
@endsection
