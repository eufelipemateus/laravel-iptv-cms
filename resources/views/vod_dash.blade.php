<div>
    <i class="fas fa-fw fa-film"></i>
    {{ $total }} {{ __('videos') }}
</div>
<div class="small text-muted mt-2">
    {{ number_format(($storage ?? 0) / 1073741824, 2) }} GB
</div>
