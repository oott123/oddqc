<?php
	class ol extends oddqc{
		private $instance_id;
		public function __construct($access_key, $access_secret, $zone, $instance_id){
			parent::__construct($access_key, $access_secret, $zone);
			$this->instance_id = $instance_id;
		}
		public function get_eip(){
			$this->param('action', 'DescribeInstances');
			$this->param('instances.1', $this->instance_id);
			$instance = $this->send_request()['instance_set'][0];
			return $instance['eip'];
		}
		public function shutdown_instance(){
			$eip_id = $this->get_eip()['eip_id'];
			// 取消分配 ip
			$this->param('action', 'DissociateEips');
			$this->param('eips.1', $eip_id);
			$job_dissociate = $this->send_request()['job_id'];
			// 停止实例
			$this->param('action', 'StopInstances');
			$this->param('instances.1', $this->instance_id);
			$this->param('force', 0);
			$this->send_request()['job_id'];
			// 释放 ip
			$this->wait_before_job_clear($job_dissociate);	//需要取消分配完毕的 ip
			$this->param('action', 'ReleaseEips');
			$this->param('eips.1', $eip_id);
			$this->send_request()['job_id'];
			return true;
		}
		public function start_instance(){
			// 启动实例
			$this->param('action', 'StartInstances');
			$this->param('instances.1', $this->instance_id);
			$job_start = $this->send_request()['job_id'];
			// 获取 ip
			$this->param('action', 'AllocateEips');
			$this->param('bandwidth', 4);
			$this->param('billing_mode', 'traffic');
			$eip_id = $this->send_request()['eips'][0];
			// 分配 ip
			$this->wait_before_job_clear($job_start);	//需要启动完成的示例
			$this->param('action', 'AssociateEip');
			$this->param('eip', $eip_id);
			$this->param('instance', $this->instance_id);
			$this->send_request();
			return true;
		}
		private function wait_before_job_clear($job_id = NULL, $timeout = 10){
			for ($i=0; $i < $timeout; $i++){
				$this->param('action', 'DescribeJobs');
				if($job_id !== NULL){
					$this->param('jobs.1', $job_id);
				}
				$count = $this->send_request()['total_count'];
				if($count == 0){
					return true;
				}
				sleep(1);
			}
			return false;
		}
	}