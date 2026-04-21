{{--
    Komponen Logo SAB Merden — Water Drop
    Props:
      $size   : 'sm' | 'md' | 'lg'  (default: 'md')
      $dark   : true/false — apakah background gelap (sidebar), untuk warna teks
      $label  : string teks sub-label (opsional, misal 'Admin Panel')
--}}
@props([
    'size'  => 'md',
    'dark'  => false,
    'label' => null,
])

@php
    $iconSize  = match($size) { 'sm' => 'w-7 h-7', 'lg' => 'w-12 h-12', default => 'w-9 h-9' };
    $svgSize   = match($size) { 'sm' => 'w-4 h-4', 'lg' => 'w-7 h-7',  default => 'w-5 h-5' };
    $titleSize = match($size) { 'sm' => 'text-xs',  'lg' => 'text-xl',  default => 'text-sm'  };
    $labelSize = 'text-xs';
    $titleColor = $dark ? 'text-white'     : 'text-slate-800';
    $labelColor = $dark ? 'text-slate-400' : 'text-slate-500';
@endphp

<div class="flex items-center gap-2.5">
    {{-- Logo icon: water drop --}}
    <div class="{{ $iconSize }} bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
        <svg class="{{ $svgSize }} text-white" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C12 2 4 10 4 15.5a8 8 0 0016 0C20 10 12 2 12 2z"/>
            <path d="M8.5 16.5a3.5 3.5 0 005-2.5" stroke="white" stroke-width="1.4" stroke-linecap="round" fill="none" opacity="0.5"/>
        </svg>
    </div>

    {{-- Brand name --}}
    <div>
        <p class="{{ $titleSize }} font-bold leading-tight {{ $titleColor }}">SAB Merden</p>
        @if($label)
            <p class="{{ $labelSize }} {{ $labelColor }} leading-tight">{{ $label }}</p>
        @endif
    </div>
</div>
