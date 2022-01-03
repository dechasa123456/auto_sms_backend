<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET");
header("Access-Control-Allow-Headers:Content-Type");
header("Access-Control-Allow-Credentials:true");
class Accounts extends MY_Controller {
function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->model('account');
    $this->load->helper(array('form', 'url'));
    $this->load->library('form_validation');
    $this->load->library('session');
   
   
  }
	public function index()
	{
    $data = array();
    $data['user'] = 'active';
    $school_id = $this->session->userdata('school_id');
    $data['user_data'] = $this->account->sql("select *from account where school_id = $school_id;");
	$this->data = $data;
    $this->load->view('admin/user',$data);
	}
 public function change_status()
 {
    $data  = array(
        'account_status'=>$this->input->post('status')
    );
    $update = $this->account->where('account_id',$this->input->post('account_id'))->update($data);
    if($update) {
    $this->session->set_userdata('success','Thank you, It is '.$this->input->post('status'). ' successfully!');
    redirect('accounts');
} else {
    $this->session->set_userdata('error','It is not evaluated, try again!');
    redirect('accounts');    
}
 }
 function auth(){
     echo $this->input->post('username');
 }
public function add_user(){
    $school_id = $this->session->userdata('school_id');
 $data = array(
    'school_id'=>$school_id,
     'account_name'=>ucwords($this->input->post('name')),
     'account_phone'=>$this->input->post('phone'),
     'account_username'=>$this->input->post('username'),
     'account_status'=>$this->input->post('status'),
     'account_type'=>$this->input->post('user_type'),
     'account_password'=>hash_hmac('sha256', $this->input->post('password'), ''),
     'account_date'=>date('Y-m-d')
 );
 $insert = $this->account->insert($data,'account_id');
 if (!$insert) {
     $this->session->set_userdata('success','New user is added successfully!');
     redirect('accounts');
 } else {
     $this->session->set_userdata('error','It is not added, try again!');
     redirect('accounts');
 }
}
}
