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
    $this->load->view('admin/song_view'); 
    $this->load->view('admin/footer');
}

public function fetch_songs()
{
    $limit = 10;  
    $page = $this->input->post('page');
    $search = $this->input->post('search');
    $offset = ($page > 1) ? ($page - 1) * $limit : 0;

    // ----- COUNT QUERY -----
    $this->db->from('songs');
    $this->db->where('songs.isActive', 1);
    if (!empty($search)) {
        $this->db->like('songs.title', $search);
    }
    $total_rows = $this->db->count_all_results(); // run separately

    // ----- DATA QUERY WITH JOIN -----
    $this->db->select('songs.*, categories.name AS category_name, subcategories.title AS subcategory_name');
    $this->db->from('songs');
    $this->db->join('categories', 'categories.id = songs.category_id', 'left');
    $this->db->join('subcategories', 'subcategories.id = songs.sub_category_id', 'left');
    // $this->db->where('songs.isActive', 1);
    if (!empty($search)) {
        $this->db->like('songs.title', $search);
    }
    $this->db->limit($limit, $offset);
    $query = $this->db->get();
    $songs = $query->result();

    // ----- PAGINATION -----
    $total_pages = ceil($total_rows / $limit);
    $start_page = max(1, $page - 1);
    $end_page = min($total_pages, $start_page + 2); // Show only 3 pages max

    $pagination = '';
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $page) ? 'active' : '';
        $pagination .= "<li class='page-item $active'><a href='javascript:void(0)' class='page-link' data-page='$i'>$i</a></li>";
    }
    if ($end_page < $total_pages) {
        $next_page = $end_page + 1;
        $pagination .= "<li class='page-item'><a href='javascript:void(0)' class='page-link' data-page='$next_page'>Next</a></li>";
    }

    echo json_encode([
        'songs' => $songs,
        'pagination' => $pagination,
        'total_rows' => $total_rows,
        'offset' => $offset
    ]);
}
public function toggle_status()
    {
        if ($this->input->method() === 'post') {
            $id = $this->input->post('id');
            $status = $this->input->post('status');

            if (is_numeric($id) && ($status === '0' || $status === '1')) {
                // $this->load->model('Category_model');

                $where = ['id' => $id];
                $data = ['isActive' => $status];

                $update = $this->general_model->update('songs', $where, $data);


                if ($update) {
                    echo json_encode([
                        'success' => true,
                        'message' => $status == '1' ? 'Published successfully' : 'Unpublished successfully'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to update status'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid input'
                ]);
            }
        }
    }

public function edit($id)
    {
        $category = $this->general_model->getOne('songs', ['id' => $id]);

        if (!$category) {
            show_404();
        }

        $data['song'] = $category;
                $data['main_categories'] = $this->general_model->getAll('categories');

        //    echo "<pre>";
        //    print_r($data['song']);
        //    die;
        $this->load->view('admin/header');
        $this->load->view('admin/edit_song__form', $data);
        $this->load->view('admin/footer');
    }
public function update_song()
{
    // Load required helpers
    $this->load->helper('security');

    // Get POST data securely
    $id             = $this->input->post('id', true);
    $title          = $this->input->post('title', true);
    $category_id    = $this->input->post('category_id', true);
    $sub_category_id= $this->input->post('sub_category_id', true);
    $description    = $this->input->post('description', false); // allow HTML (CKEditor)

    // Basic validation
    if (empty($id) || empty($title) || empty($category_id)  || empty($description)) {
        echo json_encode([
            'status'  => false,
            'message' => 'All fields are required.'
        ]);
        return;
    }

    // Prepare update data
    $update_data = [
        'title'           => $title,
        'category_id'     => $category_id,
        'sub_category_id' => $sub_category_id,
        'description'     => $description,
        // 'updated_at'      => date('Y-m-d H:i:s')
    ];

    // Update using query builder
    $this->db->where('id', $id);
    $updated = $this->db->update('songs', $update_data);

    if ($updated) {
        echo json_encode([
            'status'  => true,
            'message' => 'Song updated successfully.'
        ]);
    } else {
        echo json_encode([
            'status'  => false,
            'message' => 'Failed to update song. Please try again.'
        ]);
    }
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