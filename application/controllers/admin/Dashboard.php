<?php
class Dashboard extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');

        $this->load->helper('url');
        $this->load->model('general_model');
       

if (!$this->session->userdata('admin')) {

			redirect('admin');

		}
    }


    public function index(){
     

          $this->load->view('admin/header');
        $this->load->view('admin/dashboard_view');
        $this->load->view('admin/footer');
    }

   
}  
?>