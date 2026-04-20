<?php

namespace App\Domain\OrderManagement\Services;

use App\Domain\Cart\Models\Cart;
use App\Domain\Cart\Services\CartStockValidationService;
use App\Domain\OrderManagement\Actions\ClearCartAction;
use App\Domain\OrderManagement\Actions\CreateOrderAction;
use App\Domain\OrderManagement\Actions\CreateOrderItemsAction;
use App\Domain\OrderManagement\Actions\DecrementStockAction;
use App\Domain\OrderManagement\Actions\UpdateOrderItemsStatusAction;
use App\Domain\OrderManagement\Actions\UpdateOrderStatusAction;
use App\Domain\OrderManagement\DTOs\CheckoutDTO;
use App\Domain\OrderManagement\DTOs\CreateOrderDTO;
use App\Domain\OrderManagement\Enums\OrderStatus;
use App\Domain\OrderManagement\Exceptions\CheckoutException;
use App\Domain\OrderManagement\Exceptions\PaymentFailedException;
use App\Domain\OrderManagement\Models\Order;
use App\Domain\ProductCatalog\Enums\ProductStatus;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    public function __construct(
        private readonly CartStockValidationService $stockValidation,
        private readonly PaymentSimulatorService $paymentSimulator,
        private readonly CreateOrderAction $createOrder,
        private readonly CreateOrderItemsAction $createOrderItems,
        private readonly DecrementStockAction $decrementStock,
        private readonly ClearCartAction $clearCart,
        private readonly UpdateOrderStatusAction $updateStatus,
        private readonly UpdateOrderItemsStatusAction $updateItemsStatus,
    ) {}

    /**
     * @throws CheckoutException
     * @throws PaymentFailedException
     */
    public function checkout(CheckoutDTO $dto): Order
    {
        $paymentErrorMessage = null;

        /** @var Order $order */
        $order = DB::transaction(function () use ($dto, &$paymentErrorMessage): Order {
            $cart = Cart::query()
                ->firstOrCreate(['user_id' => $dto->userId])
                ->load(['items.product.vendor']);

            if ($cart->items->isEmpty()) {
                throw new CheckoutException('Your cart is empty.');
            }

            foreach ($cart->items as $item) {
                $product = $item->product;

                if ($product === null) {
                    throw new CheckoutException('A cart item is missing its product.');
                }

                if ($product->status !== ProductStatus::Active) {
                    throw new CheckoutException("{$product->name} is no longer available.");
                }

                $validation = $this->stockValidation->validate($product, $item->quantity);

                if (! $validation->allowed || $validation->allowedQuantity !== $item->quantity) {
                    throw new CheckoutException($validation->warning ?? "Unable to purchase {$product->name}.");
                }
            }

            $order = $this->createOrder->execute(new CreateOrderDTO(
                userId: $dto->userId,
                paymentMethod: $dto->paymentMethod,
                status: OrderStatus::Pending->value,
            ));

            $this->createOrderItems->execute($order, $cart->items);

            $total = (float) $cart->items->sum(fn ($item) => (float) $item->product->price * (int) $item->quantity);

            $payment = $this->paymentSimulator->charge($total);
            if (! $payment->success) {
                $paymentErrorMessage = $payment->message;

                return $order;
            }

            foreach ($cart->items as $item) {
                $this->decrementStock->execute($item->product, $item->quantity);
            }

            $this->clearCart->execute($cart);
            $this->updateStatus->execute($order, OrderStatus::Paid->value);
            $this->updateItemsStatus->execute($order, OrderStatus::Paid->value);

            return $order->load('items');
        });

        if ($paymentErrorMessage !== null) {
            throw new PaymentFailedException((string) $order->id, $paymentErrorMessage);
        }

        return $order;
    }
}
