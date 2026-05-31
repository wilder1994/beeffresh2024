<?php

declare(strict_types=1);

namespace App\Enums\Notifications;

enum NotificationType: string
{
    case OrderReceived = 'order_received';
    case PaymentConfirmed = 'payment_confirmed';
    case OrderPreparing = 'order_preparing';
    case OrderReadyForDelivery = 'order_ready_for_delivery';
    case OrderAssigned = 'order_assigned';
    case OrderReassigned = 'order_reassigned';
    case OrderPickedUp = 'order_picked_up';
    case OrderInTransit = 'order_in_transit';
    case OrderDelivered = 'order_delivered';
    case OrderFailed = 'order_failed';
    case OrderReturnedToStore = 'order_returned_to_store';
    case OrderUnassigned = 'order_unassigned';
    case OrderDelayed = 'order_delayed';
    case PaymentDeclined = 'payment_declined';
    case WebhookFailed = 'webhook_failed';
    case DeliveryFailedCourier = 'delivery_failed_courier';
    case InventoryOutOfStock = 'inventory_out_of_stock';

    public function label(): string
    {
        return match ($this) {
            self::OrderReceived => 'Pedido recibido',
            self::PaymentConfirmed => 'Pago confirmado',
            self::OrderPreparing => 'Pedido en preparación',
            self::OrderReadyForDelivery => 'Pedido listo para entrega',
            self::OrderAssigned => 'Nuevo pedido asignado',
            self::OrderReassigned => 'Pedido reasignado',
            self::OrderPickedUp => 'Pedido recogido',
            self::OrderInTransit => 'Pedido en camino',
            self::OrderDelivered => 'Pedido entregado',
            self::OrderFailed => 'Entrega fallida',
            self::OrderReturnedToStore => 'Devolución a tienda',
            self::OrderUnassigned => 'Pedido sin asignar',
            self::OrderDelayed => 'Pedido retrasado',
            self::PaymentDeclined => 'Pago rechazado',
            self::WebhookFailed => 'Webhook fallido',
            self::DeliveryFailedCourier => 'Entrega fallida (domiciliario)',
            self::InventoryOutOfStock => 'Producto agotado',
        };
    }
}
