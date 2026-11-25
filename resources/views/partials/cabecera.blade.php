<div class="top">
  <div><h1>{{ $h }}</h1><p>{{ $p ?? '' }}</p></div>
  <div class="top-r">
    @isset($mes)
      @php
        $q = fn ($m) => array_merge(request()->except('page'), ['mes' => $m->format('Y-m')]);
        $nombre = ucfirst($mes->locale('es')->translatedFormat('F Y'));
      @endphp
      <div class="mes glass" role="group" aria-label="Mes">
        <a href="{{ request()->fullUrlWithQuery($q($mes->copy()->subMonth())) }}" aria-label="Mes anterior">‹</a>
        <span>{{ $nombre }}</span>
        <a href="{{ request()->fullUrlWithQuery($q($mes->copy()->addMonth())) }}" aria-label="Mes siguiente">›</a>
      </div>
    @endisset
    @isset($chip)<span class="chip">{{ $chip }}</span>@endisset
  </div>
</div>
