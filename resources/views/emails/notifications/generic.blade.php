<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $content->title }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f7f1e8; font-family: Figtree, Arial, sans-serif; color: #2b2118; }
        .wrap { max-width: 560px; margin: 0 auto; padding: 24px 16px; }
        .card { background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid #e8dcc8; box-shadow: 0 8px 24px rgba(43,33,24,.08); }
        .head { background: linear-gradient(135deg, #6b3f2a, #8b4f35); color: #fff; padding: 24px; }
        .brand { font-size: 12px; letter-spacing: .12em; text-transform: uppercase; opacity: .85; }
        .title { font-size: 22px; font-weight: 700; margin: 8px 0 0; line-height: 1.3; }
        .body { padding: 24px; line-height: 1.6; font-size: 15px; }
        .cta { display: inline-block; margin-top: 20px; padding: 12px 20px; background: #b91c1c; color: #fff !important; text-decoration: none; border-radius: 999px; font-weight: 600; }
        .foot { padding: 16px 24px 24px; font-size: 12px; color: #7a6a5a; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="card">
        <div class="head">
            <div class="brand">BEEF FRESH · Carnes frescas</div>
            <h1 class="title">{{ $content->title }}</h1>
        </div>
        <div class="body">
            <p>{{ $content->body }}</p>
            @if($content->actionUrl)
                <a href="{{ $content->actionUrl }}" class="cta">{{ $content->actionLabel ?? 'Ver detalle' }}</a>
            @endif
        </div>
        <div class="foot">
            Este correo fue enviado automáticamente por BeefFresh. Si tienes dudas, responde a este mensaje o contáctanos.
        </div>
    </div>
</div>
</body>
</html>
