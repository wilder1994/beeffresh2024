<?php

declare(strict_types=1);

use App\Enums\Notifications\NotificationChannel;
use App\Enums\Notifications\NotificationType;

return [
    'queues' => [
        'default' => env('NOTIFICATION_QUEUE', 'notifications'),
        'email' => env('NOTIFICATION_EMAIL_QUEUE', 'notifications-email'),
    ],

    'job' => [
        'tries' => (int) env('NOTIFICATION_JOB_TRIES', 3),
        'timeout' => (int) env('NOTIFICATION_JOB_TIMEOUT', 60),
        'backoff' => [30, 120, 300],
    ],

    'delayed_order_minutes' => (int) env('NOTIFICATION_DELAYED_ORDER_MINUTES', 45),

    /*
    |--------------------------------------------------------------------------
    | Tipos → canales por defecto y audiencias
    |--------------------------------------------------------------------------
    */
    'types' => [
        NotificationType::OrderReceived->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer', 'operations'],
        ],
        NotificationType::PaymentConfirmed->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer', 'operations'],
        ],
        NotificationType::OrderPreparing->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer', 'operations'],
        ],
        NotificationType::OrderReadyForDelivery->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer', 'operations', 'available_couriers'],
        ],
        NotificationType::OrderAssigned->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['courier'],
        ],
        NotificationType::OrderReassigned->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['courier'],
        ],
        NotificationType::OrderPickedUp->value => [
            'channels' => [NotificationChannel::Internal],
            'audiences' => ['customer', 'operations'],
        ],
        NotificationType::OrderInTransit->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer'],
        ],
        NotificationType::OrderDelivered->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer'],
        ],
        NotificationType::OrderFailed->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer', 'operations'],
        ],
        NotificationType::OrderReturnedToStore->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['operations'],
        ],
        NotificationType::OrderUnassigned->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['operations'],
        ],
        NotificationType::OrderDelayed->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['operations'],
        ],
        NotificationType::PaymentDeclined->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['customer', 'operations'],
        ],
        NotificationType::WebhookFailed->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['operations'],
        ],
        NotificationType::DeliveryFailedCourier->value => [
            'channels' => [NotificationChannel::Internal, NotificationChannel::Email],
            'audiences' => ['courier', 'operations'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contenido por tipo (título, cuerpo, CTA)
    | Placeholders: {order_id}, {customer_name}, {status_label}, {amount}, {reference}
    |--------------------------------------------------------------------------
    */
    'content' => [
        NotificationType::OrderReceived->value => [
            'title' => 'Pedido #{order_id} recibido',
            'body' => 'Hemos recibido tu pedido #{order_id}. Te avisaremos cuando confirmemos el pago.',
            'action_label' => 'Ver seguimiento',
        ],
        NotificationType::PaymentConfirmed->value => [
            'title' => 'Pago confirmado · Pedido #{order_id}',
            'body' => 'Tu pago por ${amount} fue aprobado. Estamos preparando tu pedido.',
            'action_label' => 'Ver seguimiento',
        ],
        NotificationType::OrderPreparing->value => [
            'title' => 'Pedido #{order_id} en preparación',
            'body' => 'Tu pedido está siendo preparado en nuestra tienda.',
            'action_label' => 'Ver seguimiento',
        ],
        NotificationType::OrderReadyForDelivery->value => [
            'title' => 'Pedido #{order_id} listo para entrega',
            'body' => 'Tu pedido está listo y pronto saldrá hacia tu dirección.',
            'action_label' => 'Ver seguimiento',
        ],
        NotificationType::OrderAssigned->value => [
            'title' => 'Nuevo pedido #{order_id} asignado',
            'body' => 'Tienes un nuevo pedido para recoger y entregar.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderReassigned->value => [
            'title' => 'Pedido #{order_id} reasignado',
            'body' => 'Se te ha reasignado el pedido #{order_id}.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderPickedUp->value => [
            'title' => 'Pedido #{order_id} recogido',
            'body' => 'El domiciliario recogió tu pedido en tienda.',
            'action_label' => 'Ver seguimiento',
        ],
        NotificationType::OrderInTransit->value => [
            'title' => 'Pedido #{order_id} en camino',
            'body' => 'Tu pedido va en camino a tu dirección.',
            'action_label' => 'Ver seguimiento',
        ],
        NotificationType::OrderDelivered->value => [
            'title' => 'Pedido #{order_id} entregado',
            'body' => 'Tu pedido fue entregado correctamente. ¡Gracias por tu compra!',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderFailed->value => [
            'title' => 'Entrega fallida · Pedido #{order_id}',
            'body' => 'No pudimos completar la entrega de tu pedido. Te contactaremos pronto.',
            'action_label' => 'Ver seguimiento',
        ],
        NotificationType::OrderReturnedToStore->value => [
            'title' => 'Pedido #{order_id} devuelto a tienda',
            'body' => 'El pedido #{order_id} fue devuelto a tienda tras una entrega fallida.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderUnassigned->value => [
            'title' => 'Pedido #{order_id} sin domiciliario',
            'body' => 'El pedido #{order_id} está listo pero no tiene domiciliario asignado.',
            'action_label' => 'Ir a operaciones',
        ],
        NotificationType::OrderDelayed->value => [
            'title' => 'Pedido #{order_id} retrasado',
            'body' => 'El pedido #{order_id} lleva más tiempo del esperado en estado {status_label}.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::PaymentDeclined->value => [
            'title' => 'Pago rechazado · {reference}',
            'body' => 'No se pudo procesar tu pago. Intenta nuevamente o usa otro método.',
            'action_label' => 'Reintentar pago',
        ],
        NotificationType::WebhookFailed->value => [
            'title' => 'Webhook de pago fallido',
            'body' => 'Error al procesar webhook Wompi: {error}',
            'action_label' => 'Ver pagos',
        ],
        NotificationType::DeliveryFailedCourier->value => [
            'title' => 'Entrega fallida · Pedido #{order_id}',
            'body' => 'Registraste una entrega fallida para el pedido #{order_id}.',
            'action_label' => 'Ver pedidos',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contenido para operaciones (admin / despacho) — mismo tipo, distinto tono
    |--------------------------------------------------------------------------
    */
    'content_operations' => [
        NotificationType::OrderReceived->value => [
            'title' => 'Nuevo pedido #{order_id}',
            'body' => 'Pedido recibido de {customer_name}. Pendiente de confirmación de pago.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::PaymentConfirmed->value => [
            'title' => 'Pago confirmado · Pedido #{order_id}',
            'body' => 'Pago aprobado por ${amount}. Cliente: {customer_name}.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderPreparing->value => [
            'title' => 'Pedido #{order_id} en preparación',
            'body' => 'El pedido de {customer_name} está en preparación en tienda.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderReadyForDelivery->value => [
            'title' => 'Pedido #{order_id} listo para entrega',
            'body' => 'El pedido de {customer_name} está listo. Esperando que un domiciliario lo acepte.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderPickedUp->value => [
            'title' => 'Pedido #{order_id} recogido',
            'body' => 'El domiciliario recogió el pedido #{order_id} en tienda.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::OrderFailed->value => [
            'title' => 'Entrega fallida · Pedido #{order_id}',
            'body' => 'No se completó la entrega del pedido #{order_id}. Revisa operaciones.',
            'action_label' => 'Ver pedido',
        ],
        NotificationType::PaymentDeclined->value => [
            'title' => 'Pago rechazado · Pedido #{order_id}',
            'body' => 'Pago rechazado para el pedido #{order_id} ({reference}).',
            'action_label' => 'Ver pedido',
        ],
    ],

    'content_courier' => [
        NotificationType::OrderReadyForDelivery->value => [
            'title' => 'Nuevo pedido #{order_id} para recoger',
            'body' => 'Pedido de {customer_name} listo en tienda. Puedes aceptarlo desde tu panel.',
            'action_label' => 'Ver pedidos disponibles',
        ],
        NotificationType::OrderAssigned->value => [
            'title' => 'Tomaste el pedido #{order_id}',
            'body' => 'Ya puedes recogerlo en tienda y completar la entrega.',
            'action_label' => 'Ver pedido',
        ],
    ],

    'email' => [
        'from' => [
            'address' => env('MAIL_FROM_ADDRESS', 'noreply@beeffresh.test'),
            'name' => env('MAIL_FROM_NAME', 'BEEF FRESH'),
        ],
        'layout' => 'emails.notifications.layout',
    ],
];
