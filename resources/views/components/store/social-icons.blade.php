@props([
    'profile',
])

@php
    $networks = [
        [
            'label' => 'WhatsApp',
            'url' => $profile->social_whatsapp,
            'icon' => 'whatsapp',
            'color' => '#25D366',
        ],
        [
            'label' => 'TikTok',
            'url' => $profile->social_tiktok,
            'icon' => 'tiktok',
            'color' => '#010101',
        ],
        [
            'label' => 'Instagram',
            'url' => $profile->social_instagram,
            'icon' => 'instagram',
            'color' => '#E4405F',
        ],
        [
            'label' => 'Facebook',
            'url' => $profile->social_facebook,
            'icon' => 'facebook',
            'color' => '#1877F2',
        ],
        [
            'label' => 'X / Twitter',
            'url' => $profile->social_twitter,
            'icon' => 'twitter',
            'color' => '#000000',
        ],
    ];
@endphp

<ul class="flex flex-wrap items-center justify-center gap-3 md:gap-4" role="list">
    @foreach($networks as $network)
        <li>
            @if(filled($network['url']))
                <a
                    href="{{ $network['url'] }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="bf-social-icon"
                    style="--bf-social-color: {{ $network['color'] }}"
                    aria-label="{{ $network['label'] }}"
                    title="{{ $network['label'] }}"
                >
                    @include('components.store.social-icons.'.$network['icon'])
                </a>
            @else
                <span
                    class="bf-social-icon bf-social-icon--disabled"
                    style="--bf-social-color: {{ $network['color'] }}"
                    aria-label="{{ $network['label'] }} (no configurado)"
                    title="{{ $network['label'] }} — próximamente"
                >
                    @include('components.store.social-icons.'.$network['icon'])
                </span>
            @endif
        </li>
    @endforeach
</ul>

@if(collect($networks)->every(fn (array $network) => blank($network['url'])))
    <p class="mt-4 text-sm text-[var(--bf-muted)]">Las redes sociales se pueden configurar desde el panel de administración.</p>
@endif
