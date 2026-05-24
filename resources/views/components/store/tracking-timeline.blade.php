@props(['entries'])

@php
    $displayTz = 'America/Bogota';
@endphp

<ol class="bf-ops-timeline" {{ $attributes }}>
    @foreach($entries as $entry)
        <li @class([
            'bf-ops-timeline__item',
            'bf-ops-timeline__item--upcoming' => ($entry['state'] ?? 'completed') === 'upcoming',
        ])>
            <span class="bf-ops-timeline__dot"></span>
            <div>
                <p @class([
                    'font-medium text-sm',
                    'text-[var(--bf-muted)]' => ($entry['state'] ?? 'completed') === 'upcoming',
                ])>{{ $entry['label'] }}</p>
                @if(($entry['state'] ?? 'completed') === 'completed' && ! empty($entry['created_at']))
                    <p class="text-xs text-[var(--bf-muted)]">
                        {{ \Illuminate\Support\Carbon::parse($entry['created_at'])->timezone($displayTz)->format('d/m/Y H:i') }}
                    </p>
                @elseif(($entry['state'] ?? 'completed') === 'upcoming')
                    <p class="text-xs text-[var(--bf-muted)]">Pendiente</p>
                @endif
            </div>
        </li>
    @endforeach
</ol>
