<?php
class ControllerPaymentMpu extends Controller {
  	protected function index() {
	    $this->language->load('payment/mpu');
	    $this->data['button_confirm'] = $this->language->get('button_confirm');
	    $this->data['action'] = 'http://122.248.120.252:60145/UAT/Payment/Payment/pay';	//testing
	  
	    $this->load->model('checkout/order');
	    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

	    $this->data['merchantID'] = $this->config->get('mpu_merchant_id');
		$this->data['invoiceNo'] = date('His').$this->session->data['invoice_no'];
		
	    	if($this->config->get('mpu_password'))
		{
			$MPU_SecretKey = $this->config->get('mpu_password');
		}
		else {
			$MPU_SecretKey = 'ERR';
		}

		$productDescMPU = "EZM" + date('n') . $this->data['invoiceNo'];
//		die($productDescMPU);
		$MPU_Amount= sprintf("%012d", $order_info['total']);
		$this->data['amount'] = $MPU_Amount;
		//ksd cal//
		//$this->data['currencyCode'] = $order_info['currency_code'];
		$this->data['currencyCode'] = "104";
		$this->data['productDesc'] = $productDescMPU;
		//$this->data['productDesc'] = "Digital Goods"; //to be implemented....
		
		//For Later Use If Needed
		/*
			$this->data['userDefined1'] = "";
			$this->data['userDefined2'] = "";
			$this->data['userDefined3'] = "";
		*/
		//END OF LATER USEAGE

		$to_generate = $MPU_Amount . $this->data['invoiceNo']. $this->data['currencyCode'] . $this->data['merchantID'] .$this->data['productDesc'];
		
		$this->data['hashValue'] = strtoupper($this->generate_hash_value($to_generate,$MPU_SecretKey));

	    if ($this->config->get('mpu_password')) {
			$this->data['digest'] = $this->session->data['order_id'] . $this->data['amount'] . $this->config->get('mpu_password');
			$secret_key = $this->data['digest'];
	    }

		$this->data['bill_name'] = html_entity_decode($order_info['payment_firstname'] . ' ' . $order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
	    $this->data['bill_addr_1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
		$this->data['bill_addr_2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
		$this->data['bill_city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
		$this->data['bill_state'] = html_entity_decode($order_info['payment_zone'], ENT_QUOTES, 'UTF-8');
		$this->data['bill_post_code'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
		$this->data['bill_country'] = html_entity_decode($order_info['payment_country'], ENT_QUOTES, 'UTF-8');
		$this->data['bill_tel'] = html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
		$this->data['bill_email'] = $order_info['email'];

		if ($this->cart->hasShipping()) {
			$this->data['ship_name'] = html_entity_decode($order_info['shipping_firstname'] . ' ' . $order_info['shipping_lastname'], ENT_QUOTES,'UTF-8');
			$this->data['ship_addr_1'] = html_entity_decode($order_info['shipping_address_1'],ENT_QUOTES,'UTF-8');
			$this->data['ship_addr_2'] = html_entity_decode($order_info['shipping_address_2'],ENT_QUOTES,'UTF-8');
			$this->data['ship_city'] = html_entity_decode($order_info['shipping_city'],ENT_QUOTES,'UTF-8');
			$this->data['ship_state'] = html_entity_decode($order_info['shipping_zone'],ENT_QUOTES,'UTF-8');
			$this->data['ship_post_code'] = html_entity_decode($order_info['shipping_postcode'],ENT_QUOTES,'UTF-8');
			$this->data['ship_country'] = html_entity_decode($order_info['shipping_country'],ENT_QUOTES,'UTF-8');
		} else {
			$this->data['ship_name'] = '';
			$this->data['ship_addr_1'] = '';
			$this->data['ship_addr_2'] = '';
			$this->data['ship_city'] = '';
			$this->data['ship_state'] = '';
			$this->data['ship_post_code'] = '';
			$this->data['ship_country'] = '';
		}

		$this->data['currency'] = $this->currency->getCode();
		$this->data['callback'] = $this->url->link('payment/mpu/callback', '', 'SSL');
		
		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mpu.tpl')){
		    $this->template = $this->config->get('config_template') . '/template/payment/mpu.tpl';
		} else {
	        $this->template = 'default/template/payment/mpu.tpl';
		}
	  
	   
	  	
		
		############NMZ##################
		/*if ($order_info)
		{
		    $this->language->load('payment/mpu');

			$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

			if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$this->data['base'] = HTTP_SERVER;
			} else {
				$this->data['base'] = HTTPS_SERVER;
			}
			
			$this->data['language'] = $this->language->get('code');
			$this->data['direction'] = $this->language->get('direction');

			$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
			$this->data['text_response'] = $this->language->get('text_response');
			$this->data['text_success'] = $this->language->get('text_success');
			$this->data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
			$this->data['text_failure'] = $this->language->get('text_failure');
			$this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/cart'));

			if (isset($this->request->get['code']) && $this->request->get['code'] == 'A' && $status) {
				$this->load->model('checkout/order');
				$this->model_checkout_order->confirm($this->request->get['trans_id'], $this->config->get('config_order_status_id'));

				$message = '';

				if (isset($this->request->get['code'])) {
					$message .= 'code: ' . $this->request->get['code'] . "\n";
				}

				if (isset($this->request->get['auth_code'])) {
					$message .= 'auth_code: ' . $this->request->get['auth_code'] . "\n";
				}

				if (isset($this->request->get['ip'])) {
					$message .= 'ip: ' . $this->request->get['ip'] . "\n";
				}

				if (isset($this->request->get['cv2avs'])) {
					$message .= 'cv2avs: ' . $this->request->get['cv2avs'] . "\n";
				}

				if (isset($this->request->get['valid'])) {
					$message .= 'valid: ' . $this->request->get['valid'] . "\n";
				}

				//$this->model_checkout_order->update($order_id, $this->config->get('mpu_order_status_id'), $message, false);
				$this->data['continue'] = $this->url->link('checkout/success');

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mpu_success.tpl')) {
					$this->template = $this->config->get('config_template') . '/template/payment/mpu_success.tpl';
				} else {
					$this->template = 'default/template/payment/mpu_success.tpl';
				}
				$this->children = array(
					'common/column_left',
					'common/column_right',
					'common/content_top',
					'common/content_bottom',
					'common/footer',
					'common/header'
				);

				$this->response->setOutput($this->render());
			} else {
				$this->data['continue'] = $this->url->link('checkout/cart');

				if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/mpu_failure.tpl')) {
					$this->template = $this->config->get('config_template') . '/template/payment/mpu_failure.tpl';
				} else {
					$this->template = 'default/template/payment/mpu_failure.tpl';
				}

				$this->children = array(
					'common/column_left',
					'common/column_right',
					'common/content_top',
					'common/content_bottom',
					'common/footer',
					'common/header'
				);

				$this->response->setOutput($this->render());
			}
		}*/
		$this->render();
		############DEFAULT###############
		if($order_info)
		{
			$data=array_merge($this->request->post,$this->request->get);
			if($data['status'] == 'Y' || $data['status'] == 'y')
			{
				//some update
			}
		}
		#######################MPU TEST COMMERCING#############  
	}
		  
	function generate_hash_value($to_generate,$MPU_SecretKey)
    	{
		$secret_key = $MPU_SecretKey;
		$hash_value = hash_hmac('sha1', $to_generate, $secret_key, false);
		$hash_value = strtoupper($hash_value);
		return $hash_value;
    	}
	########################END OF EDIT####################


	public function callback(){
    	if (isset($this->request->post['orderid'])) {
			$order_id = trim(substr($this->request->post['orderid'],6));
		} else {
			$order_id = 0;
			die('Illegal Access');
		}	

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);

  	}
}
?>
