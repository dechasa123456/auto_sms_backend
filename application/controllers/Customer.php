<?php
defined('BASEPATH') OR exit('No direct script access allowed');
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Methods:GET");
header("Access-Control-Allow-Headers:Content-Type");
header("Access-Control-Allow-Credentials:true");
class Customer extends MY_Controller {
function __construct()
  {
    parent::__construct();
    $this->load->database();
    $this->load->model('customer_model');
    $this->load->model('account');
    $this->load->helper(array('form', 'url'));
    $this->load->library('form_validation');
    $this->load->library('session');
   
   
  }
	
public function register() 
{
 $name  = ucwords($this->input->post('name'));
 $phone = $this->input->post('phone');
 $email = $this->input->post('email');
 $insert_customer = $this->db->query("insert into customer (customer_name,customer_phone,customer_email) values('$name','$phone','$email') RETURNING customer_id;");
 if ($insert_customer->result()[0]->customer_id) {
    $customer_id = $insert_customer->result()[0]->customer_id;
    $password = hash_hmac('sha256', $this->input->post('password'), '');
    $insert_account = $this->db->query("insert into account (customer_id,account_name,account_phone,account_username,account_type,account_password) values($customer_id,'$name','$phone','$email','customer','$password') RETURNING account_id;");
 if ($insert_account->result()[0]->account_id) {
     $data['login_id'] = $insert_account->result()[0]->account_id;
     $data['customer_id'] = $customer_id;
     $data['login_name'] = $name;
 } else {
     $data['status'] = "OOPS! There is the server error!";    
    }
} else {
    $data['status'] = "OOPS! There is the server error!";    

}
echo json_encode(($data));
}
public function gmail_github_login()
{
  $name  = ucwords($this->input->post('name'));
  $email = $this->input->post('email');
  $check = $this->customer_model->sql("select ac.account_id,ac.customer_id from customer as cm 
  join account as ac on ac.customer_id = cm.customer_id where ac.account_username = '$email';");
  // print_r($check);
  $data = [];
 if (!count($check)) {
  $insert_customer = $this->db->query("insert into customer (customer_name,customer_email) values('$name','$email') RETURNING customer_id;");
  if ($insert_customer->result()[0]->customer_id) {
    $customer_id = $insert_customer->result()[0]->customer_id;
    $insert_account = $this->db->query("insert into account (customer_id,account_name,account_username,account_type) values($customer_id,'$name','$email','customer') RETURNING account_id;");
    if ($insert_account->result()[0]->account_id) {
      $data['login_id'] = $insert_account->result()[0]->account_id;
      $data['customer_id'] = $customer_id;
      $data['login_name'] = $name;
    } else {
      $data['status'] = "OOPS! There is the server error!";    
    }
  } else {
    $data['status'] = "OOPS! There is the server error!";   
  }
} else {
  $data['login_name'] = $name;
  $data['login_id'] = $check[0]->account_id;
  $data['customer_id'] = $check[0]->customer_id;
}
echo json_encode($data);
}
public function group_category()
{
  $name = $this->input->post('settingName');
  $status = $this->input->post('status');
  $type = $this->input->post('type');
  $customer_id = $this->input->post('customer_id');
  $data = [];
  $insert = $this->db->query("insert into group_category (gc_name,gc_type,gc_status,customer_id) values('$name','$type','$status',$customer_id) RETURNING gc_id;");
  if ($insert->result()[0]->gc_id) {
    $data['gc_id'] = $insert->result()[0]->gc_id;
  } else {
    $data['gc_id'] = null;
  }
  echo json_encode($data);
}
public function get_group($customer_id = null)
{
  $data = $this->customer_model->sql("select gc_name as group_name,gc_status as status,gc_date as date,gc_id from group_category where gc_type='Group' and customer_id = $customer_id order by gc_id desc;");
  echo json_encode($data);
}
public function for_bulksms_group()
{
  $data = $this->customer_model->sql("select count(cm.gc_id) as total_group, gc_name as group_name,
  group_category.gc_id from group_category 
  join customer_member as cm on cm.gc_id=group_category.gc_id 
  and group_category.gc_type='Group'
  group by group_name,group_category.gc_id
  order by gc_id desc;");
  echo json_encode($data);
}
public function for_bulksms_category()
{
  $data = $this->customer_model->sql("select count(cm.gc_id) as total_group, gc_name as group_name,
  group_category.gc_id from group_category 
  join customer_member as cm on cm.gc_id=group_category.gc_id 
  and group_category.gc_type='Category'
  group by group_name,group_category.gc_id
  order by gc_id desc;");
  echo json_encode($data);
}
public function sent_sm_history()
{
  $data = [];
  $customer_id = $this->input->post('customer_id');
  if ($this->input->post('from') && $this->input->post('to')) {
    $from = date('Y-m-d',strtotime($this->input->post('from')));
    $to = date('Y-m-d',strtotime($this->input->post('to')));
    $get  = $this->customer_model->sql("select sent_sms_sender,sent_sms_reciever,sent_sms_subject,sent_sms_text,sent_sms_date::date,sent_sms_id from sent_sms where customer_id = $customer_id and sent_sms_date::date >= '$from'::date and  sent_sms_date::date <= '$to'::date
    order by sent_sms_id desc;");

  } else {
    // $from = date('Y-m-d');
    // $to   = date('Y-m-d');
    $get  = $this->customer_model->sql("select sent_sms_sender,sent_sms_reciever,sent_sms_subject,sent_sms_text,sent_sms_date::date,sent_sms_id from sent_sms where customer_id = $customer_id order by sent_sms_id desc;");
    // $get  = $this->customer_model->sql("select sent_sms_sender,sent_sms_reciever,sent_sms_subject,sent_sms_text,sent_sms_date::date,sent_sms_id from sent_sms where customer_id = $customer_id and sent_sms_date::date >= '$from'::date and  sent_sms_date::date <= '$to'::date
    // order by sent_sms_id desc;");
  }
  $data['sent_data'] = $get;
  $data['current_date'] = date('d-m-Y');
  echo json_encode($data);
}
public function get_category($customer_id = null)
{
  $data = $this->customer_model->sql("select gc_name as category_name,gc_status as status,gc_date as date,gc_id from group_category where gc_type='Category' and customer_id = $customer_id order by gc_id desc;");
  echo json_encode($data);
}
public function get_members($customer_id = null)
{
  $data = $this->customer_model->sql("select 
  cm.cm_name,cm.cm_phone,cm.cm_email,cm.cm_status,gc.gc_name,cm.cm_type
  from customer_member as cm left join group_category as gc on gc.gc_id = cm.gc_id where cm.customer_id = $customer_id order by cm.cm_id desc;");
  echo json_encode($data);
} 
public function get_members_phone()
{
  $data = $this->customer_model->sql("select 
  cm.cm_id as id,cm.cm_phone as label
  from customer_member as cm;");
  echo json_encode($data);
} 
public function get_auto_replay($customer_id = null)
{
  $data = $this->customer_model->sql("select automation_name as auto_message,automation_status as auto_status,automation_id,automation_date as registered_date from automation  where customer_id = $customer_id order by automation_id desc;");
  echo json_encode($data);
} 
public function get_users($customer_id = null)
{
  $data = $this->customer_model->sql("select account_name as name,account_phone as phone,account_username as email,account_status as status,account_date as date,account_id from account where customer_id = $customer_id order by account_id desc;");
  echo json_encode($data);
} 
public function get_loged_user($login_id = null)
{
  $data = $this->customer_model->sql("select account_name as name,account_phone as phone,account_username as email,account_status as status,account_date as date,account_id from account where account_id = $login_id;");
  echo json_encode($data);
} 
//0921831852
public function user_registration()
{
  $name = $this->input->post('name');
  $status = $this->input->post('status');
  $phone = $this->input->post('phone');
  $email = $this->input->post('email');
  $password = hash_hmac('sha256', $this->input->post('password'), '');
  $customer_id = $this->input->post('customer_id');
  $insert_account = $this->db->query("insert into account (customer_id,account_name,account_username,account_type,account_password,account_phone,account_status) values($customer_id,'$name','$email','member','$password','$phone','$status') RETURNING account_id;");
  if ($insert_account->result()[0]->account_id) {
    $data['account_id'] = $insert_account->result()[0]->account_id;
    } else {
    $data['account_id'] = null;    
   }
   echo json_encode($data);
}
public function update_user()
{
  $name = $this->input->post('name');
  $phone = $this->input->post('phone');
  $email = $this->input->post('email');
  $login_id = $this->input->post('login_id');
  $customer_id = $this->input->post('customer_id');
  $data_account = array(
           'account_name'=>$name,
           'account_phone'=>$phone,
           'account_username'=>$email,
  );
  $data_customer = array(
           'customer_name'=>$name,
           'customer_phone'=>$phone,
           'customer_email'=>$email,
  );
  $this->account->where('account_id',$login_id)->update($data_account);
  $insert_customer = $this->customer_model->where('customer_id',$customer_id)->update($data_customer);
  if ($insert_customer) {
    $data['account_id'] = 'ok';
  } else {
    $data['account_id'] = null;
  }
  echo json_encode($data);
}
public function register_member()
{
  $name = $this->input->post('name');
  $phone = $this->input->post('phone');
  $email = $this->input->post('email');
  $group_category = $this->input->post('group_category');
  $type = $this->input->post('type');
  $customer_id = $this->input->post('customer_id');
  $insert = $this->db->query("insert into customer_member (cm_name,cm_phone,cm_email,customer_id,cm_type,gc_id) values('$name','$phone','$email',$customer_id,'$type',$group_category) RETURNING cm_id;");
  if ($insert->result()[0]->cm_id) {
    $data['cm_id'] = $insert->result()[0]->cm_id;
  } else {
    $data['cm_id'] = null;
  }
  echo json_encode($data);
}
public function auto_register()
{
  $message = $this->input->post('message');
  $status = $this->input->post('status');
  $customer_id = $this->input->post('customer_id');
  $insert = $this->db->query("insert into automation (automation_name,automation_status,customer_id) values('$message','$status',$customer_id) RETURNING automation_id;");
  if ($insert->result()[0]->automation_id) {
    $data['automation_id'] = $insert->result()[0]->automation_id;
  } else {
    $data['automation_id'] = null;
  }
  echo json_encode($data);
}
public function recover_userpassword()
{
  $password = hash_hmac('sha256', $this->input->post('password'), '');
  $user_id = $this->input->post('user_id');
  $update = $this->db->query("update account set account_password = '$password' where account_id = $user_id RETURNING account_id;");
  if ($update->result()[0]->account_id) {
    $data['account_id'] = $update->result()[0]->account_id;
  } else {
    $data['account_id'] = null;
  }
  echo json_encode($data);
}
public function send_message()
{
  $text = $this->input->post('text');
  $subject = $this->input->post('subject');
  $reciever = $this->input->post('reciever');
  $sender = "0922088220";
  $customer_id = $this->input->post('customer_id');
  $insert = $this->db->query("insert into sent_sms (
    sent_sms_text,
    sent_sms_subject,
    sent_sms_reciever,
    sent_sms_sender,
    customer_id) 
     values(
    '$text',
    '$subject',
    '$reciever',
    '$sender',
    $customer_id) RETURNING sent_sms_id;");
  if ($insert->result()[0]->sent_sms_id) {
    $data['sent_sms_id'] = $insert->result()[0]->sent_sms_id;
  } else {
    $data['sent_sms_id'] = null;
  }
  echo json_encode($data);
}
public function send_bulk_message()
{
  $success_id = null;
  $text = $this->input->post('text');
  $subject = $this->input->post('subject');
  $sender = "0922088220";
  $customer_id = $this->input->post('customer_id');
  $recievers = $this->get_member_phone($this->input->post('gc_id'));
  foreach($recievers as $row) {
  $reciever_phone = $row->cm_phone;
  $insert = $this->db->query("insert into sent_sms (
    sent_sms_text,
    sent_sms_subject,
    sent_sms_reciever,
    sent_sms_sender,
    customer_id) 
     values(
    '$text',
    '$subject',
    '$reciever_phone',
    '$sender',
    $customer_id) RETURNING sent_sms_id;");
    $success_id = $insert->result()[0]->sent_sms_id;
     }
  if ($success_id) {
    $data['sent_sms_id'] = $success_id;
  } else {
    $data['sent_sms_id'] = null;
  }
  echo json_encode($data);
}
public function get_member_phone($group_category_id = 4)
{
  $get = $this->customer_model->sql("select cm_phone from customer_member where gc_id = $group_category_id;");
  return $get;
}
}
