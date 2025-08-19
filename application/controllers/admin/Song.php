<?php
class Song extends CI_Controller
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


    public function index()
    {
        


        $this->load->view('admin/header');
        $this->load->view('admin/footer');
    }

    public function add_new_song(){
        $data['main_categories'] = $this->general_model->getAll('categories');
          $this->load->view('admin/header');
        $this->load->view('admin/song_form',$data);
        $this->load->view('admin/footer');
    }
    public function get_subcategories()
{
    // Read raw input (JSON)
    $raw_input = file_get_contents('php://input');
    $input_data = json_decode($raw_input, true);

    $category_id = isset($input_data['category_id']) ? $input_data['category_id'] : null;

    // If no category_id provided
    if (empty($category_id)) {
        echo json_encode([
            'status'  => false,
            'code'    => 400,
            'message' => 'Category ID is required'
        ]);
        return;
    }

    // Fetch subcategories using your common general_model function
    $conditions = ['main_category_id' => $category_id,'isActive'=>1];
    $subcategories = $this->general_model->getAll('subcategories', $conditions);

    if (!empty($subcategories)) {
        echo json_encode([
            'status' => true,
            'code'   => 200,
            'data'   => $subcategories
        ]);
    } else {
        echo json_encode([
            'status'  => false,
            'code'    => 404,
            'message' => 'No subcategories found'
        ]);
    }
}
public function save_song(){
    echo "<pre>";
    print_r($_POST);
    die;
}
}