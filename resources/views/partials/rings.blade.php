{{-- Anillos concéntricos que hacen eco del sello circular del logo --}}
<svg class="{{ $class ?? '' }}" viewBox="0 0 400 400" fill="none" aria-hidden="true">
    @foreach ([190, 160, 130, 100, 70] as $i => $r)
        <circle cx="200" cy="200" r="{{ $r }}" stroke="#e8cf9b" stroke-opacity="{{ 0.55 - $i * 0.08 }}" stroke-width="1" @if($i % 2) stroke-dasharray="2 6" @endif />
    @endforeach
    <circle cx="200" cy="10" r="3" fill="#e8cf9b" />
    <circle cx="330" cy="200" r="2" fill="#e8cf9b" fill-opacity=".7" />
</svg>
