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
public function save_song()
{
    // $this->load->model('general_model');

    // Prepare data from POST
    $data = array(
        'category_id'      => $this->input->post('main_category_id'),
        'sub_category_id'  => $this->input->post('sub_category_id') ? $this->input->post('sub_category_id') : NULL,
        'title'            => $this->input->post('song_name'),
        'description'      => $this->input->post('song_lyrics', FALSE), // preserve HTML
        'isActive'         => 1,
        'created_on'       => date('Y-m-d')
    );

    // Insert into DB
    $insert_id = $this->general_model->insert('songs', $data);

    if ($insert_id) {
        $response = array(
            'status' => true,
            'message' => 'Song saved successfully!',
            'song_id' => $insert_id
        );
    } else {
        $response = array(
            'status' => false,
            'message' => 'Failed to save song. Please try again.'
        );
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


}