<?php

namespace App\Services\General;

use App\Enums\ContactType;
use App\Enums\OrderStatus;
use App\Enums\ReturnStatus;
use App\Enums\WithdrawalStatus;

class NotificationMessages
{
    // order message for Admin
    public static function adminNewOrder($order)
    {
        return [
            'title'       => 'New order received',
            //__('New order received')

            'body'        => 'A new order is waiting to be processed.',
            //__('A new order is waiting to be processed.')

            'notify_type' => 'order',
            'notify_id'   => $order->id,
            'icon'        => $order->client?->avatar,
            'params'      => [],
        ];
    }
    public static function adminProcessSubOrder($order)
    {
        return [
            'title'       => 'Sub-order processed',
            //__('Sub-order processed')

            'body'        => 'The sub-order has been processed by :vendor.',
            //__('The sub-order has been processed by :vendor.')

            'notify_type' => 'order',
            'notify_id'   => $order->id,
            'icon'        => $order->vendor?->profile?->logo,
            'params'      => [
                'vendor' => $order->vendor?->profile?->name
                    ?? $order->vendor?->name
            ],
        ];
    }
    public static function adminCancelSubOrder($order)
    {
        return [
            'title'       => 'Sub-order cancelled',
            //__('Sub-order cancelled')

            'body'        => 'The sub-order was cancelled by :vendor.',
            //__('The sub-order was cancelled by :vendor.')

            'notify_type' => 'order',
            'notify_id'   => $order->id,
            'icon'        => $order->vendor?->profile?->logo,
            'params'      => [
                'vendor' => $order->vendor?->profile?->name
                    ?? $order->vendor?->name
            ],
        ];
    }

    // order message for vendor
    public static function vendorNewOrder($order)
    {
        return [
            'title'       => 'New order received',
            //__('New order received')

            'body'        => 'You have a new order that needs to be processed.',
            //__('You have a new order that needs to be processed.')

            'notify_type' => 'order',
            'notify_id'   => $order->id,
            'icon'        => getNotificationIcon('new-order.png'),
            'params'      => [],
        ];
    }

    public static function vendorCancelSubOrder($order)
    {
        return [
            'title'       => 'Order :number cancelled',
            //__('Order :number cancelled')

            'body'        => 'This order has been cancelled.',
            //__('This order has been cancelled.')

            'notify_type' => 'order',
            'notify_id'   => $order->id,
            'icon'        => getNotificationIcon('cancelled.png'),
            'params'      => [
                'number' => $order->orderGroup?->number,
            ],
        ];
    }


    public static function vendorCompletedOrder($order)
    {
        return [
            'title'       => 'Order completed successfully',
            //__('Order completed')

            'body'        => 'The order is completed and your profit has been added to your wallet.',
            //__('The order is completed and your profit has been added to your wallet.')

            'notify_type' => 'order',
            'notify_id'   => $order->id,
            'icon'        => getNotificationIcon('complete.png'),
            'params'      => [],
        ];
    }

    // order message for client
    public static function clientCancelSubOrder($order)
    {
        $data = [
            'title'       => 'Sorry, Some products are not available in order :number', //__('Sorry, Some products are not available in order :number')
            'body'        => 'Some products are not available, the amount was refunded to your wallet', //__('Some products are not available, the amount was refunded to your wallet')
            'notify_type' => 'order',
            'notify_id'   => $order->orderGroup->id,
            'icon'        => getNotificationIcon('cancelled.png'),
            'params'      => ['number' => $order->orderGroup->number],
        ];
        return $data;
    }

    public static function clientOrderStatus($order)
    {
        $message = match ($order->status) {

            OrderStatus::CANCELLED => [
                'title' => 'Order :number was cancelled',
                //__('Order :number was cancelled')

                'body'  => 'Your payment has been refunded to your wallet.',
                //__('Your payment has been refunded to your wallet.')
                'icon'  => getNotificationIcon('cancelled.png'),
            ],

            OrderStatus::PROCESSING => [
                'title' => 'Order :number is being prepared',
                //__('Order :number is being prepared')

                'body'  => 'We are currently preparing your order.',
                //__('We are currently preparing your order.')
                'icon'  => getNotificationIcon('accept.png'),
            ],

            OrderStatus::RECEIVED => [
                'title' => 'Order :number received',
                //__('Order :number received')

                'body'  => 'We have received your order successfully.',
                //__('We have received your order successfully.')
                'icon'  => getNotificationIcon('complete.png'),
            ],

            OrderStatus::IN_WAREHOUSE => [
                'title' => 'Order :number is in our warehouse',
                //__('Order :number is in our warehouse')

                'body'  => 'Your order is ready for the next shipping step.',
                //__('Your order is ready for the next shipping step.')
                'icon'  => getNotificationIcon('default.png'),
            ],

            OrderStatus::SHIPPED => [
                'title' => 'Order :number has been shipped',
                //__('Order :number has been shipped')

                'body'  => 'Your order is on its way to you.',
                //__('Your order is on its way to you.')
                'icon'  => getNotificationIcon('shipped.png'),
            ],

            OrderStatus::DELIVERED => [
                'title' => 'Order :number delivered',
                //__('Order :number delivered')

                'body'  => 'Your order has been delivered. Enjoy!',
                //__('Your order has been delivered. Enjoy!')
                'icon'  => getNotificationIcon('complete.png'),
            ],

            default => [
                'title' => 'Order :number update',
                //__('Order :number update')

                'body'  => 'Your order status has been updated.',
                //__('Your order status has been updated.')
                'icon'  => getNotificationIcon('default.png'),
            ],
        };

        return array_merge($message, [
            'notify_type' => 'order',
            'notify_id'   => $order->id,
            'status'      => $order->status,
            'params'      => ['number' => $order->number],
        ]);
    }


    // return orders
    public static function vendorReturnOrder($returnOrder)
    {
        $message = match ($returnOrder->status) {

            ReturnStatus::PENDING => [
                'title' => 'New return request',
                //__('New return request')

                'body'  => 'New return request need to handle',
                //__('New return request need to handle')
            ],

            default => [
                'title' => 'Return request update',
                //__('Return request update')

                'body'  => 'Your return request status has been updated.',
                //__('Your return request status has been updated.')
            ],
        };

        return array_merge($message, [
            'notify_type' => 'return',
            'notify_id'   => $returnOrder->id,
            'status'      => $returnOrder->status,
            'icon'        => getNotificationIcon('default.png'),
            'params'      => ['number' => $returnOrder->number],
        ]);
    }

    // return order
    public static function clientReturnOrder($returnOrder)
    {
        $message = match ($returnOrder->status) {

            ReturnStatus::APPROVED => [
                'title' => 'New return request',
                //__('New return request')

                'body'  => 'New return request need to handle',
                //__('New return request need to handle')
                'icon'  => getNotificationIcon('accept.png'),
            ],

            ReturnStatus::REJECTED => [
                'title' => 'New return request',
                //__('New return request')
                'body'  => $returnOrder->vendor_note,
                'icon'  => getNotificationIcon('cancelled.png'),
            ],

            ReturnStatus::PICKED => [
                'title' => 'Return request picked',
                //__('New return request')

                'body'  => 'Your return request has been picked.',
                //__('New return request need to handle')
                'icon'  => getNotificationIcon('shipped.png'),
            ],


            ReturnStatus::DELIVERED => [
                'title' => 'Return request delivered',
                //__('New return request')

                'body'  => 'Your return request has been delivered.',
                //__('New return request need to handle')
                'icon'  => getNotificationIcon('complete.png'),
            ],

            default => [
                'title' => 'Return request update',
                //__('Order :number update')

                'body'  => 'Your return request status has been updated.',
                //__('Your order status has been updated.')
                'icon'  => getNotificationIcon('default.png'),
            ],
        };

        return array_merge($message, [
            'notify_type' => 'return',
            'notify_id'   => $returnOrder->id,
            'status'      => $returnOrder->status,
            'params'      => ['number' => $returnOrder->number],
        ]);
    }

    public static function adminReturnOrder($returnOrder)
    {
        $message = match ($returnOrder->status) {

            ReturnStatus::APPROVED => [
                'title' => 'Return request approved',
                //__('Return request approved')

                'body'  => 'A return request has been approved. Please proceed with the return process.',
                //__('A return request has been approved. Please proceed with the return process.')
            ],

            ReturnStatus::REJECTED => [
                'title' => 'Return request rejected',
                //__('Return request rejected')

                'body'  => $returnOrder->vendor_note,
            ],
            default => [
                'title' => 'Order :number update',
                //__('Order :number update')

                'body'  => 'Your order status has been updated.',
                //__('Your order status has been updated.')
            ],
        };

        return array_merge($message, [
            'notify_type' => 'return',
            'notify_id'   => $returnOrder->id,
            'status'      => $returnOrder->status,
            'icon'        => $returnOrder->user?->avatar,
            'params'      => ['number' => $returnOrder->number],
        ]);
    }

    public static function adminWithdrawalRequest($withdrawal)
    {
        $data = [
            'title' => 'New withdrawal request',
            //__('Withdrawal request pending')
            'body'  => 'A withdrawal request has been submitted. Please review and approve or reject it.',
            //__('A withdrawal request has been submitted. Please review and approve or reject it.')
        ];
        return array_merge($data, [
            'notify_type' => 'withdrawal',
            'notify_id'   => $withdrawal->id,
            'icon'        => $withdrawal->user?->profile?->logo,
            'params'      => ['number' => $withdrawal->number],
        ]);
    }

    public static function vendorWithdrawalRequest($withdrawal)
    {
        $message = match ($withdrawal->status) {
            WithdrawalStatus::APPROVED => [
                'title' => 'Withdrawal request approved',
                //__('Withdrawal request approved')
                'body'  => 'Your withdrawal request has been approved.',
                //__('Your withdrawal request has been approved.')
                'icon'  => getNotificationIcon('accept.png'),
            ],
            WithdrawalStatus::REJECTED => [
                'title' => 'Withdrawal request rejected',
                //__('Withdrawal request rejected')
                'body'  => $withdrawal->admin_note,
                //__('Your withdrawal request has been rejected.')
                'icon'  => getNotificationIcon('transaction-failed.png'),
            ],
            default => [
                'title' => 'Withdrawal request update',
                //__('Withdrawal request update')
                'body'  => 'Your withdrawal request status has been updated.',
                //__('Your withdrawal request status has been updated.')
                'icon'  => getNotificationIcon('default.png'),
            ],
        };
        return array_merge($message, [
            'notify_type' => 'withdrawal',
            'notify_id'   => $withdrawal->id,
            'params'      => ['number' => $withdrawal->number],
        ]);
    }

    public static function contact($contact): array
    {
        $message = match ($contact->type) {
            ContactType::CONTACT => [
                'title' => 'New contact message', // __('New contact us message')
                'body'  => $contact->message,
                'notify_type' => 'contact',
            ],
            ContactType::TICKET => [
                'title' => 'New ticket message', // __('New ticket message')
                'body'  => $contact->message,
                'notify_type' => 'ticket',
            ],
            default => [
                'title' => 'New contact message',
                'body'  => $contact->message,
                'notify_type' => 'contact',
            ],
        };

        return array_merge($message, [
            'notify_id'   => $contact->id,
            'icon'        => getNotificationIcon('default.png'),
            'params'      => [],
        ]);
    }

    public static function reply($reply)
    {
        $data = [
            'title'       => 'New reply on ticket', // __('New reply on ticket')
            'body'        => $reply->reply,
            'notify_type' => 'reply',
            'notify_id'   => $reply->contact_id,
            'icon'        => getNotificationIcon('default.png'),
            'params'      => [],
        ];
        return $data;
    }

    public static function productsImport()
    {
        $data = [
            'title'       => 'Products Import', // __('Products Import')
            'body'        => 'Products imported successfully', // __('Products imported successfully')
            'notify_type' => 'product',
            'notify_id'   => null,
            'icon'        => getNotificationIcon('default.png'),
            'params'      => [],
        ];
        return $data;
    }


    public static function subscriptionExpiringTomorrow($subscription)
    {
        return [
            'title'       => 'Subscription expiring tomorrow',
            //__('Subscription expiring tomorrow')

            'body'        => 'Your subscription will expire tomorrow.',
            //__('Your subscription will expire tomorrow.')

            'notify_type' => 'subscription',
            'notify_id'   => $subscription->id,
            'icon'        => getNotificationIcon('default.png'),
            'params'      => [],
        ];
    }

    public static function expiredSubscription($subscription)
    {
        return [
            'title'       => 'Subscription expired',
            //__('Subscription expired')

            'body'        => 'Your subscription has expired. renew now',
            //__('Your subscription has expired. renew now')

            'notify_type' => 'subscription',
            'notify_id'   => $subscription->id,
            'icon'        => getNotificationIcon('default.png'),
            'params'      => [],
        ];
    }
}
