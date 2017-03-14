<?php
// PayUponPickup/src/Providers/PayUponPickupServiceProvider.php

namespace PayUponPickup\Providers;

use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Events\Dispatcher;
use Plenty\Modules\Payment\Events\Checkout\ExecutePayment;
use Plenty\Modules\Payment\Events\Checkout\GetPaymentMethodContent;
use Plenty\Modules\Basket\Events\Basket\AfterBasketCreate;
use Plenty\Modules\Basket\Events\Basket\AfterBasketChanged;
use Plenty\Modules\Basket\Events\BasketItem\AfterBasketItemAdd;
use Plenty\Modules\Payment\Method\Contracts\PaymentMethodContainer;
use PayUponPickup\Helper\PayUponPickupHelper;
use PayUponPickup\Methods\PayUponPickupPaymentMethod;

/**
 * Class PayUponPickupServiceProvider
 * @package PayUponPickup\Providers
 */
class PayUponPickupServiceProvider extends ServiceProvider
{
	public function register()
	{

	}

	/**
	 * Boot additional services for the payment method
	 *
	 * @param PayUponPickupHelper $paymentHelper
	 * @param PaymentMethodContainer $payContainer
	 * @param Dispatcher $eventDispatcher
	 */
	public function boot( PayUponPickupHelper $paymentHelper,
			PaymentMethodContainer $payContainer,
			Dispatcher $eventDispatcher)
	{
		// Create the ID of the payment method if it doesn't exist yet
		$paymentHelper->createMopIfNotExists();

		// Register the Pay upon pickup payment method in the payment method container
		$payContainer->register('plenty_payuponpickup::PAYUPONPICKUP', PayUponPickupPaymentMethod::class,
				[ AfterBasketChanged::class, AfterBasketItemAdd::class, AfterBasketCreate::class ]
				);

		// Listen for the event that gets the payment method content
		$eventDispatcher->listen(GetPaymentMethodContent::class,
				function(GetPaymentMethodContent $event) use( $paymentHelper)
				{
					if($event->getMop() == $paymentHelper->getPaymentMethod())
					{
						$event->setValue('');
						$event->setType('continue');
					}
		});

		// Listen for the event that executes the payment
		$eventDispatcher->listen(ExecutePayment::class,
				function(ExecutePayment $event) use( $paymentHelper)
				{
					if($event->getMop() == $paymentHelper->getPaymentMethod())
					{
						$event->setValue('<h1>Pay upon pickup<h1>');
						$event->setType('htmlContent');
					}
		});
	}
}


?>