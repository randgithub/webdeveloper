<?php
class ControllerModuleChangeOrderStatusFunction extends Controller {


    public function on_change_order_status($order_id) {

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($order_id);
		$url = 'http://thearabkids.xyz/test/public/api/v1/order';
		$params=array(
						'api_token' => 'eng.randlulu@gmail.com',
						'order_id' => $order_id,
						'fname' => $order_info['firstname'],
						'lname' => $order_info['lastname'],
						'city' => $order_info['payment_city'],
						'total' => $order_info['total']
					);
						
		if($order_info)
		{
			$query = $this->db->query("SELECT oh.date_added, os.order_status_id AS status, oh.comment, oh.notify FROM " . DB_PREFIX . "order_history oh LEFT JOIN " . DB_PREFIX . "order_status os ON oh.order_status_id = os.order_status_id WHERE oh.order_id = '" . (int)$order_id . "' AND os.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY oh.date_added DESC LIMIT 2");

			$order_history= $query->rows;
			
			if(count($order_history) == 2)
			{ //compare the last two statuses and send a request if different
				if($order_history[0]->status != $order_history[1]->status)
				{
					do_curl_request($url,$params);
				}
				
			}
			elseif(count($order_history) == 1)
			{
				//first status change
				do_curl_request($url,$params);
			}
			
		}
    }
	
	function do_curl_request($url, $params=array()) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Accept: application/json'));

		$params_string = '';
		if (is_array($params) && count($params)) {
		foreach($params as $key=>$value) {
		  $params_string .= $key.'='.$value.'&'; 
		}
		rtrim($params_string, '&');

		curl_setopt($ch,CURLOPT_POST, count($params));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $params_string);
		}

		//execute post
		$result = curl_exec($ch);

		//close connection
		curl_close($ch);

		return $result;
	}

}
