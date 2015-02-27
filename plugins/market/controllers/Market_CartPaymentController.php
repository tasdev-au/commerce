<?php
namespace Craft;

/**
 * Cart. Step "Payment".
 *
 * Class Market_CartPaymentController
 * @package Craft
 */
class Market_CartPaymentController extends Market_BaseController
{
	protected $allowAnonymous = true;

	/**
	 * @throws HttpException
	 * @throws \Exception
	 */
	public function actionSetShippingMethod()
	{
		$this->requirePostRequest();

		$id = craft()->request->getPost('shippingMethodId');
        if(craft()->market_cart->setShippingMethod($id)) {
            craft()->userSession->setFlash('market', 'Shipping method has been set');
            $this->redirectToPostedUrl();
        } else {
            craft()->urlManager->setRouteVariables(['shippingMethodError' => 'Wrong shipping method']);
        }
	}
	/**
	 * @throws HttpException
	 * @throws \Exception
	 */
	public function actionSetPaymentMethod()
	{
		$this->requirePostRequest();

		$id = craft()->request->getPost('paymentMethodId');
        if(craft()->market_cart->setPaymentMethod($id)) {
            craft()->userSession->setFlash('market', 'Payment method has been set');
            $this->redirectToPostedUrl();
        } else {
            craft()->urlManager->setRouteVariables(['paymentMethodError' => 'Wrong payment method']);
        }
	}

    /**
     * @throws HttpException
     */
    public function actionPay()
    {
        $this->requirePostRequest();

        $paymentForm = new Market_PaymentFormModel;
        $paymentForm->attributes = $_POST;

        //in case of success "pay" redirects us somewhere
        if(!craft()->market_payment->processPayment($paymentForm, $customError)) {
            craft()->urlManager->setRouteVariables(compact('paymentForm', 'customError'));
        }
    }

    public function actionCancel()
    {
        $this->actionGoToComplete();
        $this->redirect('market/cart');
    }

    public function actionSuccess()
    {
        $this->actionGoToComplete();
        $this->redirect('market/cart');
    }

    /**
     * @throws Exception
     */
    public function actionGoToComplete()
    {
        $order = craft()->market_cart->getCart();

        if($order->canTransit(Market_OrderRecord::STATE_COMPLETE)) {
            $order->transition(Market_OrderRecord::STATE_COMPLETE);
        } else {
            throw new Exception('unable to go to payment state from the state: ' . $order->state);
        }
    }
}