<?php
class ControllerPaymentMpu extends Controller {
  private $error = array();
 
  public function index() {
    $this->language->load('payment/mpu');
    $this->document->setTitle('MPU - Configuration');
    $this->load->model('setting/setting');
 
    if (($this->request->server['REQUEST_METHOD'] == 'POST')) {
      $this->model_setting_setting->editSetting('mpu', $this->request->post);
      $this->session->data['success'] = 'Saved.';
      $this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
    }
 
    $this->data['heading_title'] = $this->language->get('heading_title');

    $this->data['text_enabled'] = $this->language->get('text_enabled');
    $this->data['text_disabled'] = $this->language->get('text_disabled');
    $this->data['text_all_zones'] = $this->language->get('text_all_zones');
    $this->data['text_yes'] = $this->language->get('text_yes');
    $this->data['text_no'] = $this->language->get('text_no');

    $this->data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
    $this->data['entry_password'] = $this->language->get('entry_password');    
    $this->data['entry_test'] = $this->language->get('entry_test');
    $this->data['entry_total'] = $this->language->get('entry_total');
    $this->data['entry_order_status'] = $this->language->get('entry_order_status');
    $this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
    $this->data['entry_status'] = $this->language->get('entry_status');
    $this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

    $this->data['button_save'] = $this->language->get('button_save');
    $this->data['button_cancel'] = $this->language->get('button_cancel');
		
    if (isset($this->error['warning'])) {
    	$this->data['error_warning'] = $this->error['warning'];
    } else {
    	$this->data['error_warning'] = '';
    }

    if (isset($this->error['merchant'])) {
    	$this->data['error_merchant'] = $this->error['merchant'];
    } else {
    	$this->data['error_merchant'] = '';
    }

    $this->data['breadcrumbs'] = array();

	$this->data['breadcrumbs'][] = array(
		'text'      => $this->language->get('text_home'),
		'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
		'separator' => false
	);

	$this->data['breadcrumbs'][] = array(
		'text'      => $this->language->get('text_payment'),
		'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
		'separator' => ' :: '
	);

	$this->data['breadcrumbs'][] = array(
		'text'      => $this->language->get('heading_title'),
		'href'      => $this->url->link('payment/mpu', 'token=' . $this->session->data['token'], 'SSL'),
		'separator' => ' :: '
	);

    $this->data['action'] = $this->url->link('payment/mpu', 'token=' . $this->session->data['token'], 'SSL');
    $this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

    
    //Merchant ID
    if (isset($this->request->post['mpu_merchant_id'])) {
      $this->data['mpu_merchant_id'] = $this->request->post['mpu_merchant_id'];
    } else {
      $this->data['mpu_merchant_id'] = $this->config->get('mpu_merchant_id');
    }
    //merchant pwd
	$secret_key = $this->request->post['mpu_password'];
	if(isset($secret_key)){
		$this->data['mpu_password'] = $secret_key;		
	die(print_r($secret_key));
	}else{
		$this->data['mpu_password'] = $this->config->get('mpu_password');
//	die(print_r($this->data['mpu_password']));
	}

    if (isset($this->request->post['mpu_total'])) {
	$this->data['mpu_total'] = $this->request->post['mpu_total'];
    } else {
	$this->data['mpu_total'] = $this->config->get('mpu_total');
    }
    
    if (isset($this->request->post['mpu_order_status_id'])) {
      $this->data['mpu_order_status_id'] = $this->request->post['mpu_order_status_id'];
    } else {
      $this->data['mpu_order_status_id'] = $this->config->get('mpu_order_status_id');
    } 

    $this->load->model('localisation/order_status');
    $this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

    if (isset($this->request->post['mpu_geo_zone_id'])) {
	$this->data['mpu_geo_zone_id'] = $this->request->post['mpu_geo_zone_id'];
    } else {
	$this->data['mpu_geo_zone_id'] = $this->config->get('mpu_geo_zone_id');
    }
    $this->load->model('localisation/geo_zone');
    $this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

    if (isset($this->request->post['mpu_status'])) {
      $this->data['mpu_status'] = $this->request->post['mpu_status'];
    } else {
      $this->data['mpu_status'] = $this->config->get('mpu_status');
    }

    if (isset($this->request->post['mpu_sort_order'])) {
	$this->data['mpu_sort_order'] = $this->request->post['mpu_sort_order'];
    } else {
	$this->data['mpu_sort_order'] = $this->config->get('mpu_sort_order');
    }

    $this->template = 'payment/mpu.tpl';            
    $this->children = array(
      'common/header',
      'common/footer'
    );
 
    $this->response->setOutput($this->render());
  }
  protected function validate() {
	if (!$this->user->hasPermission('modify', 'payment/mpu')) {
		$this->error['warning'] = $this->language->get('error_permission');
	}

	if (!$this->request->post['mpu_merchant_id']) {
		$this->error['merchant'] = $this->language->get('error_merchant');
	}

	if (!$this->error) {
		return true;
	} else {
		return false;
	}
   }
function create_signature_string($input_fields_array)
    {
        unset($input_fields_array["hashValue"]);    // exclude hash value from signature string
        
        sort($input_fields_array, SORT_STRING);
        
        $signature_string = "";
        foreach($input_fields_array as $key => $value)
        {   
            $signature_string .= $value;    
        }
        
        return $signature_string;
    }
    
    function generate_hash_value()
    {
        $input_fields_array = $_POST;
                            
        $signature_string = create_signature_string($input_fields_array);
        global $secret_key;
        
        $hash_value = hash_hmac('sha1', $signature_string, $secret_key, false);
        $hash_value = strtoupper($hash_value);
        
        return $hash_value;
    }
    
    function is_hash_value_matched()
    {
        $is_matched = false;
        $generated_hash_value = generate_hash_value();
        $server_hash_value = $_POST["hashValue"];
        
        if ($generated_hash_value == $server_hash_value)
        {
            $is_matched = true;
        }
        
        return $is_matched;
    }
}
