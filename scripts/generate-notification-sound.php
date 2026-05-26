<?php

declare(strict_types=1);

/**
 * Sintetiza un "ding" tipo campana (parciales inarmónicos + decay exponencial).
 */
$rate = 44100;
$duration = 0.55;
$fundamental = 830.61;
$sampleCount = (int) ($rate * $duration);

/** @var list<array{ratio: float, amp: float, decay: float}> */
$partials = [
    ['ratio' => 1.00, 'amp' => 0.55, 'decay' => 4.5],
    ['ratio' => 2.00, 'amp' => 0.35, 'decay' => 6.0],
    ['ratio' => 2.41, 'amp' => 0.28, 'decay' => 7.5],
    ['ratio' => 3.04, 'amp' => 0.20, 'decay' => 9.0],
    ['ratio' => 4.16, 'amp' => 0.12, 'decay' => 11.0],
    ['ratio' => 5.43, 'amp' => 0.07, 'decay' => 14.0],
];

$data = '';

for ($i = 0; $i < $sampleCount; $i++) {
    $t = $i / $rate;
    $attack = min(1.0, $t / 0.008);
    $value = 0.0;

    foreach ($partials as $partial) {
        $freq = $fundamental * $partial['ratio'];
        $value += $partial['amp']
            * sin(2 * M_PI * $freq * $t)
            * exp(-$partial['decay'] * $t);
    }

    $sample = (int) max(-32767, min(32767, 32767 * 0.42 * $attack * $value));
    $data .= pack('s', $sample);
}

$byteRate = $rate * 2;
$blockAlign = 2;
$bitsPerSample = 16;
$chunkSize = 36 + strlen($data);
$header = 'RIFF'
    .pack('V', $chunkSize)
    .'WAVEfmt '
    .pack('V', 16)
    .pack('v', 1)
    .pack('v', 1)
    .pack('V', $rate)
    .pack('V', $byteRate)
    .pack('v', $blockAlign)
    .pack('v', $bitsPerSample)
    .'data'
    .pack('V', strlen($data));

$target = dirname(__DIR__) . '/public/sounds/notification.wav';

if (! is_dir(dirname($target))) {
    mkdir(dirname($target), 0755, true);
}

file_put_contents($target, $header . $data);

echo 'Wrote bell ' . strlen($header . $data) . " bytes to {$target}\n";
