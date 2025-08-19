<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->library('form_validation');

        $this->load->helper('url');
        $this->load->model('general_model');
        $this->form_validation->set_error_delimiters("<div class='error'>", "</div>");

if ($this->session->userdata('admin')) {

			if ($this->router->fetch_method() != 'logout') {
                redirect('dashboard');
            }

		}
    }

    public function index()
    {
        $this->form_validation->set_rules('mobile', 'mobile', 'required');
        $this->form_validation->set_rules('password', 'password', 'required');

        if ($this->form_validation->run() === true) {



			$password = md5($this->input->post('password'));



			$email = $this->input->post('mobile');



			$where = array('mobile' => $email, 'password' => $password,'isActive'=>1);



			$user = $this->general_model->getOne('users', $where);


			//  echo "<pre>";

			//  print_r($user);die;


			if ($user) {


				
				$session = array(



					'id' => $user->id,

					'mobile' => $user->mobile,
             );

				$this->session->set_userdata('admin', $session);

				$this->session->set_flashdata('success', 'You have logged in successfully!');

				redirect('dashboard', 'refresh');

			} else {



				$this->session->set_flashdata('error', 'Invalid email or password. Please try again.');

				redirect('admin', 'refresh');

			}





		}


        // $this->load->view('admin/header');
        $this->load->view('admin/login_view');
        // $this->load->view('admin/footer');

    }
	 public function logout() {
        // Clear session and redirect to login page
        $this->session->unset_userdata('admin');
        $this->session->sess_destroy();
        redirect('admin');
    }
}
