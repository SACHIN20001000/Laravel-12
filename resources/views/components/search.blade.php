@props([
    'route' => '',
    'placeholder' => 'Search...',
    'value' => '',
    'hiddenFields' => []
])

<form method="GET" action="{{ $route }}" class="mb-3">
    @foreach($hiddenFields as $key => $val)
        <input type="hidden" name="{{ $key }}" value="{{ $val }}">
    @endforeach
    <div class="input-group">
        <input type="text" class="form-control" name="search" placeholder="{{ $placeholder }}" value="{{ $value }}">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
        @if($value)
            <a href="{{ $route . (!empty($hiddenFields) ? '?' . http_build_query($hiddenFields) : '') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>

