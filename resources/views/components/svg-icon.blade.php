@php
    $iconName = trim($name);
    $svgrepoViewName = 'components.svg.svgrepo.' . $iconName;
    $legacyViewName = 'components.svg.' . $iconName;
    $availableAssets = array_merge(
        config('svg-assets.assets', []),
        config('svg-assets.ui', []),
        config('svg-assets.svgrepo', [])
    );
@endphp

@if (in_array($iconName, $availableAssets, true) && view()->exists($svgrepoViewName))
    @include($svgrepoViewName)
@elseif (in_array($iconName, $availableAssets, true) && view()->exists($legacyViewName))
    @include($legacyViewName)
@else
    <svg {{ $attributes->merge(['viewBox' => '0 0 64 64', 'fill' => 'none', 'xmlns' => 'http://www.w3.org/2000/svg']) }}>
        <rect x="8" y="8" width="48" height="48" rx="10" stroke="currentColor" stroke-width="4" />
        <path d="M20 32H44" stroke="currentColor" stroke-width="4" stroke-linecap="round" />
    </svg>
@endif
