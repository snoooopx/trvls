<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Flightsdohopback extends MX_Controller {


	function __construct(){
	    $seturl =  $this->uri->segment(3);
      if($seturl != "settings"){
        $chk = modules::run('Home/is_main_module_enabled','flightsdohop');
   if(!$chk){
       backError_404();

   }
      }
     $checkingadmin = $this->session->userdata('pt_logged_admin');
    if(!empty($checkingadmin)){

      $this->data['userloggedin'] = $this->session->userdata('pt_logged_admin');

    }else{

      $this->data['userloggedin'] = $this->session->userdata('pt_logged_agent');

    }

    if(empty($this->data['userloggedin'])){
     redirect("admin");
    }

    if(!empty($checkingadmin)){
     $this->data['adminsegment'] = "admin";

    }else{
      $this->data['adminsegment'] = "agent";
    }

    if($this->data['adminsegment'] == "admin"){

   $chkadmin = modules::run('Admin/validadmin');
   if(!$chkadmin){
      redirect('admin');
   }

    }else{

   $chkagent = modules::run('agent/validagent');
   if(!$chkagent){
      redirect('agent');
   }


    }


   if(!pt_permissions('flightsdohop',$this->data['userloggedin'])){

     redirect('admin');
   }
    $this->load->helper('settings');
    $this->load->model('Flightsdohop/Dohop_model');

     $this->data['isadmin'] = $this->session->userdata('pt_logged_admin');
    $this->data['isSuperAdmin'] = $this->session->userdata('pt_logged_super_admin');



    }

    function index(){

    }


    function settings(){

    $updatesett = $this->input->post('updatesettings');

    if(!empty($updatesett)){

    $this->Dohop_model->update_front_settings();
    redirect('admin/flightsdohop/settings');

    }

    $this->data['settings'] = $this->Settings_model->get_front_settings("flightsdohop");
    $this->data['main_content'] = 'Flightsdohop/settings';
	$this->data['page_title'] = 'Flights Dohop Settings';

	$this->load->view('Admin/template',$this->data);

    }



    }
