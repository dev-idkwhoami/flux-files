@props(['bytes'])

@php
    $bytes = (int) $bytes;
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];

    if ($bytes === 0) {
        $formatted = '0 B';
    } else {
        $power = floor(log($bytes, 1024));
        $power = min($power, count($units) - 1);
        $size = $bytes / pow(1024, $power);
        $formatted = round($size, $power > 0 ? 2 : 0) . ' ' . $units[$power];
    }
@endphp

<span {{ $attributes }}>{{ $formatted }}</span>
