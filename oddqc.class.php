<?php
	class oddqc {
		private $ch;
		private $access_key;
		private $secret_key;
		private $zone;
		private $param_list = array();

		const base_url = 'https://api.qingcloud.com/iaas/';
		const api_version = 1;
		const signature_version = 1;
		const signature_header = "GET\n/iaas/\n";

		public $error_code = array(
			1100 => 'format',
			1200 => 'auth_failed',
			1300 => 'message_expire',
			1400 => 'forbidden',
			2100 => 'not_found',
			2400 => 'over_balance',
			2500 => 'over_quota',
			5000 => 'internal',
			5100 => 'server_is_busy',
			5200 => 'no_avaliable_resource',
			5300 => 'update_in_progress',
		);

		public function __construct($access_key, $secret_key, $zone = 'pek2'){
			$this->access_key = $access_key;
			$this->secret_key = $secret_key;
			$this->zone = $zone;
			$this->ch = curl_init();
			// 初始化 curl 参数
			$this->curl_setopt(CURLOPT_RETURNTRANSFER, true);
		}
		public function set_zone($zone){
			$this->zone = $zone;
		}
		private function curl_setopt($option, $value){
			curl_setopt($this->ch, $option, $value);
		}
		public function param($key, $value){
			$this->param_list[$key] = $value;
		}
		private function signature(){
			$param = $this->param_list;
			// 1. 按参数名进行升序排列
			ksort($param);
			// 2. 对参数名称和参数值进行URL编码；3. 构造URL请求
			$query_str = http_build_query($param);
			// 4. 构造被签名串
			$signature_str = self::signature_header . $query_str;
			// 5. 计算签名
			$signature = urlencode(base64_encode(hash_hmac('sha256', $signature_str, $this->secret_key, TRUE)));
			// 6. 添加签名
			$query_str.= '&signature=' . $signature;
			return $query_str;
		}
		public function send_request(){
			// 增加公共参数
			$d = new DateTime();
			$d->setTimezone(new DateTimeZone('UTC'));
			$this->param('time_stamp', $d->format('Y-m-d\TH:i:s\Z'));
			$this->param('access_key_id', $this->access_key);
			$this->param('version', self::api_version);
			$this->param('signature_method', 'HmacSHA256');
			$this->param('signature_version', self::signature_version);
			$this->param('zone', $this->zone);
			// 签名
			$query_str = $this->signature();
			// 开始请求
			$this->curl_setopt(CURLOPT_URL, self::base_url.'?'.$query_str);
			$raw_result = curl_exec($this->ch);
			if(!$raw_result){
				// 请求失败
				throw new oddqc_curl_exception(curl_error($this->ch), curl_errno($this->ch));
			}
			$result = json_decode($raw_result, 1);
			if($result['ret_code'] > 0){
				// 错误
				$err_class = 'client';
				if($err = $this->error_code[$result['ret_code']]){
					$err_class = 'oddqc_'.$err.'_exception';
				}else{
					// 未定义的错误
					if($result['ret_code'] < 5000){
						$err_class = 'client';
					}else{
						$err_class = 'server';
					}
				}
				throw new $err_class($result['message'], $result['ret_code']);
			}
			// 在成功时，清空参数并返回
			$this->param_list = array();
			return $result;
		}
		public function test_signature(){
			$k = $this->access_key;
			$s = $this->secret_key;
			$this->access_key = 'QYACCESSKEYIDEXAMPLE';
			$this->secret_key = 'SECRETACCESSKEY';
			$this->param_list = json_decode('
				{
					"count":1,
					"vxnets.1":"vxnet-0",
					"zone":"pek1",
					"instance_type":"small_b",
					"signature_version":1,
					"signature_method":"HmacSHA256",
					"instance_name":"demo",
					"image_id":"centos64x86a",
					"login_mode":"passwd",
					"login_passwd":"QingCloud20130712",
					"version":1,
					"access_key_id":"QYACCESSKEYIDEXAMPLE",
					"action":"RunInstances",
					"time_stamp":"2013-08-27T14:30:10Z"
				}', true);
			echo $this->signature();
			$this->access_key = $k;
			$this->secret_key = $s;
		}
	}
	class oddqc_exception extends Exception{}
	// ===============================
	class oddqc_curl_exception extends oddqc_exception{}
	class oddqc_client_exception extends oddqc_exception{}
	class oddqc_server_exception extends oddqc_exception{}
	// ===============================
	class oddqc_format_exception extends oddqc_client_exception{}
	class oddqc_auth_failed_exception extends oddqc_client_exception{}
	class oddqc_message_expire_exception extends oddqc_client_exception{}
	class oddqc_forbidden_exception extends oddqc_client_exception{}
	class oddqc_not_found_exception extends oddqc_client_exception{}
	class oddqc_over_balance_exception extends oddqc_client_exception{}
	class oddqc_over_quota_exception extends oddqc_client_exception{}
	// ===============================
	class oddqc_internal_exception extends oddqc_server_exception{}
	class oddqc_server_is_busy_exception extends oddqc_server_exception{}
	class oddqc_no_avaliable_resource_exception extends oddqc_server_exception{}
	class oddqc_update_in_progress_exception extends oddqc_server_exception{}