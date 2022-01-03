<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET");
header("Access-Control-Allow-Headers:Content-Type");
header("Access-Control-Allow-Credentials:true");
class Login extends CI_Controller {
function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->helper(array('form', 'url'));
    $this->load->library('form_validation');
    $this->load->library('session');
    // load model
    $this->load->model('account');
   
  }
	public function index()
	{
  $data = array();
  $this->data = $data;
	$this->load->view('admin/login',$data);
	}
  function test() {
    echo hash_hmac('sha256', 'farisadmin', '');
  }
  public function check_login()
  {
    $username = $this->input->post('username');
    $password = $this->input->post('password');
    $data=[];
    if ($username && $password) {
    $check_account = $this->account->where('account_status','Active')->where('account_username',$username)->where('account_password', hash_hmac('sha256', $password, ''))->get();
    if ($check_account) {
     $data['login_id']=$check_account->account_id;
     $data['customer_id']=$check_account->customer_id;
     $data['login_name'] = $check_account->account_name;
    } else {
     $data['status']="Your password and username is wrong!";
    }
  } else {
    $data['status']="The fields password and username are required!";

  }
  echo json_encode($data);
  }
  public function logout()
  {
    $this->session->unset_userdata('id');
    $this->session->unset_userdata('name');
    $this->session->unset_userdata('type');
    redirect('login');
  }
}
