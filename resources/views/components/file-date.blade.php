@props(['date'])

@php
    $locale = app()->getLocale();
    $format = config("flux-files.localization.$locale.formats.datetime", 'd/m/Y H:i:s');

    if ($date instanceof \Carbon\Carbon || $date instanceof \DateTime) {
        $formatted = $date->format($format);
    } elseif (is_string($date)) {
        $formatted = \Carbon\Carbon::parse($date)->format($format);
    } else {
        $formatted = now()->format($format);
    }
@endphp

<span {{ $attributes }}>{{ $formatted }}</span>
