<?php

return [
    /**
     * Perfiles de recorte y exportación (cliente + referencia UI).
     * Proporción unificada 4:3 para catálogo, home, cinta y tarjetas.
     */
    'profiles' => [
        'catalog' => [
            'label' => 'Catálogo',
            'aspect_w' => 4,
            'aspect_h' => 3,
            'aspect_css' => '4/3',
            'output_width' => 1200,
            'output_height' => 900,
            'quality' => 0.85,
            'mime' => 'image/jpeg',
            'extension' => 'jpg',
            'max_edit_px' => 2048,
            'hint' => 'Proporción 4:3 · ajusta el encuadre en el editor',
        ],
        'logo' => [
            'label' => 'Logo',
            'aspect_w' => 1,
            'aspect_h' => 1,
            'aspect_css' => '1/1',
            'output_width' => 512,
            'output_height' => 512,
            'quality' => 0.88,
            'mime' => 'image/jpeg',
            'extension' => 'jpg',
            'max_edit_px' => 2048,
            'hint' => 'Cuadrada · se recorta en círculo en la barra lateral',
        ],
        'avatar' => [
            'label' => 'Avatar',
            'aspect_w' => 1,
            'aspect_h' => 1,
            'aspect_css' => '1/1',
            'output_width' => 512,
            'output_height' => 512,
            'quality' => 0.9,
            'mime' => 'image/jpeg',
            'extension' => 'jpg',
            'max_edit_px' => 2048,
            'circular' => true,
            'hint' => 'Foto de perfil cuadrada',
        ],
    ],

    'max_upload_kb' => 4096,
];
