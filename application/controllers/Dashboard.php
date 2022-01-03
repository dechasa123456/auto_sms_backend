<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET");
header("Access-Control-Allow-Headers:Content-Type");
header("Access-Control-Allow-Credentials:true");
class Dashboard extends MY_Controller {
function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->model('student_model');
    $this->load->helper(array('form', 'url'));
    $this->load->library('form_validation');
    $this->load->library('session');
   
   
  }
	public function index()
	{
      $school_id = $this->session->userdata('school_id');
    $data = array();
    $data['new']    = $this->student_model->where('school_id',$school_id)->where('student_status','New')->count_rows();
    $data['failed'] = $this->student_model->where('school_id',$school_id)->where('student_status','Failed')->count_rows();
    $data['passed'] = $this->student_model->where('school_id',$school_id)->where('student_status','Passed')->count_rows();
    $data['total']  = $this->student_model->where('school_id',$school_id)->count_rows();
    $data['dashboard'] = 'active';
	  $this->data = $data;
    $this->load->view('admin/dashboard',$data);
	}
  public function reactSaveData()
  {
       if (!empty($_FILES['image']['name'])) {
      $file_name = $_FILES['image']['name'];
      $file_tmp  = $_FILES['image']['tmp_name'];
      $ext_type  = explode('.',$file_name)[1];
      $new_image_name = 'image_' . date('Y-m-d-H-i-s') . '_' . uniqid() .'.'.$ext_type;
      move_uploaded_file($file_tmp,"photo/".$new_image_name);
      $new_image_name = 'photo/'.$new_image_name;
      } else {
      $new_image_name = null;
      }
  
    echo $new_image_name;
}
}
