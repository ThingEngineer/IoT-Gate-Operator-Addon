<?php

class Projects extends CI_Controller {
	
	function __construct()
	{
		parent::__construct();
	}
	
	function index()
	{
		$this->load->helper(array('file', 'date'));
		
		$data = json_decode(read_file('../app/logs/gatestate.data'), TRUE);
		
		switch ($data['gatestate'])
		{
			case 0:
				$view_data['gatestate'] = 'Closed';
				break;
			case 1:
				$view_data['gatestate'] = 'Opening...';
				break;
			case 2:
				$view_data['gatestate'] = 'Open';
				break;
			case 3:
				$view_data['gatestate'] = 'Closing...';
				break;
		}
		
		$last_opened = json_decode(read_file('../app/logs/gateopened.data'), TRUE);
		$view_data['last_opened'] = timespan($last_opened['last_opened'], time()) . ' ago';
		
		//Load View
		$t['data'] = $view_data;
		$this->load->view('gate_view', $t);
	}
	
	function save_gate_state()
	{
		$this->load->helper('file');
		
		$data = file_get_contents('php://input');
	    write_file('../app/logs/gatestate.data', $data);
	
		$data = json_decode($data, TRUE);
		if ($data['gatestate'] == 1)
		{
			$last_opened = array('last_opened' => time());
			write_file('../app/logs/gateopened.data', json_encode($last_opened));
		}
	}
	
	function get_gate_state()
	{
		$this->load->helper(array('file', 'date'));
		$this->load->library('ajax');
		
		$data = json_decode(read_file('../app/logs/gatestate.data'), TRUE);
		
		$last_opened = json_decode(read_file('../app/logs/gateopened.data'), TRUE);
		$data['last_opened'] = timespan($last_opened['last_opened'], time()) . ' ago';
		
		$data['device_state'] = json_decode(read_file('../app/logs/devicestate.data'), TRUE);
		$device_offline_timestamp = json_decode(read_file('../app/logs/device_offline_timestamp.data'), TRUE);
		$data['device_offline_timestamp'] = ' for ' . timespan($device_offline_timestamp['timestamp'], time());
		
		$this->ajax->output_ajax($data, 'json', TRUE);		// send json data, enforce ajax request
	}
	
	function save_device_state()
	{
		$this->load->helper('file');
		
		$data = file_get_contents('php://input');
	    write_file('../app/logs/devicestate.data', $data);
	
		$data = json_decode($data, TRUE);
		if ($data['devicestate'] == 0)
		{
			$device_offline_timestamp = array('timestamp' => time());
			write_file('../app/logs/device_offline_timestamp.data', json_encode($device_offline_timestamp));
		}
	}
	
}

/* End of file Projects.php */
/* Location: ./application/controllers/projects.php */