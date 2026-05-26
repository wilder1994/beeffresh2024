@props(['class' => ''])

<div {{ $attributes->merge(['class' => 'bf-realtime-status '.$class]) }} data-bf-realtime-status data-state="fallback">
    <span class="bf-realtime-dot bf-realtime-dot--off" data-bf-realtime-dot aria-hidden="true"></span>
    <span class="bf-realtime-status__label" data-bf-realtime-label>Sin conexión realtime (modo fallback)</span>
</div>
