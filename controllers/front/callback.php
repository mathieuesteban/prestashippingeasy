<?php


class prestashippingeasycallbackModuleFrontController extends ModuleFrontController
{
	private $shippingeasy;

	public function initContent()
	{
		parent::initContent();
		$this->shippingeasy = new PrestaShippingEasy();

		// Load library now
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/ShippingEasy.php');

		// Errors
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/Error.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/ApiError.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/ApiConnectionError.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/AuthenticationError.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/InvalidRequestError.php');

		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/ApiRequestor.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/Authenticator.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/Object.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/Order.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/Signature.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/SignedUrl.php');
		require(__PS_BASE_URI__.'modules/'. $this->shippingeasy->name . '/lib/Cancellation.php');

		$values = Tools::file_get_contents('php://input');
		$output = Tools::jsonDecode($values, true);

      	if ($output)
      	{
	      	$order_id = $output['shipment']['orders'][0]['external_order_identifier'];
	        $tracking_number = $output['shipment']['tracking_number'];
	        $carrier_key = $output['shipment']['carrier_key'];
	        $carrier_service_key = $output['shipment']['carrier_service_key'];
	        $shipment_cost_cents = $output['shipment']['shipment_cost'];
	        $shipment_cost = ($shipment_cost_cents / 100);

	        if ($order_id && $tracking_number)
	        {
		        $order=new Order((int)$order_id);
		        if (Validate::isLoadedObject($order)) {
		        	$order->setCurrentState(Configuration::get('PS_OS_SHIPPING'));
			        $comment_update = 'Shipping Tracking Number: ' . $tracking_number . ' Carrier Key: ' . $carrier_key . ' Carrier Service Key: ' . $carrier_service_key . ' Cost: ' . $shipment_cost;

					$msg = new Message();
					$msg->message = Tools::substr($comment_update,0,1600);
					$msg->id_order = (int)($order_id);
					$msg->private = 1;
					$msg->add();

		        	if (Validate::isTrackingNumber($tracking_number)) {
						$order->shipping_number = $tracking_number;
						$order->update();

						$order_carrier = new OrderCarrier($order->getIdOrderCarrier());
						if (Validate::isLoadedObject($order_carrier)) {
							$order_carrier->tracking_number = $tracking_number;
							$order_carrier->update();
						}
					}
				}
			}
		}
 		die();
	}
}
