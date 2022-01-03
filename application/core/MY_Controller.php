<?php

//require_once(APPPATH.'controllers/Auth.php');

class MY_Controller extends CI_Controller {
	function __construct() {
		parent::__construct();
		// error_reporting(0);
		// $this->load->library('form_validation','session');
		$this->load->database();
		$this->load->helper(array('form', 'url'));
		$this->load->library('form_validation');
		$this->load->library('session');
		// if(!$this->session->userdata('id')){
        //  redirect('login');
		// }
		$school_id = $this->session->userdata('school_id');
	}
	function count()
	{
	$count = 0;
	return $count;
	}
	public function input_array($input = []) {
		$data = '';
		for($i = 0;$i<sizeof($input);$i++) {
			$data .=$input[$i].',';
		}
		$data = '{'.rtrim($data,',').'}';
		return json_decode(json_encode($data));
	}

	}