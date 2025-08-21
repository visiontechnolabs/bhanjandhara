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
      $this->db->from('songs');
        $song_count = $this->db->count_all_results();

        // Count total categories
        $this->db->from('categories');
        $category_count = $this->db->count_all_results();

        
        // Pass data to view
        $data['song'] = $song_count;
        $data['category'] = $category_count;
        // $data['sub_cagtegory'] = $sub_category_count;

          $this->load->view('admin/header');
        $this->load->view('admin/dashboard_view',$data);
        $this->load->view('admin/footer');
    }

   
}  
?>