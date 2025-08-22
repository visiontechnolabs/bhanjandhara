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
    $page = (int) $this->input->post('page');
    $search = $this->input->post('search');
    if ($page < 1) $page = 1;
    $offset = ($page - 1) * $limit;

    // ----- COUNT QUERY -----
    $this->db->from('songs');
    if (!empty($search)) {
        $this->db->like('songs.title', $search);
    }
    $total_rows = $this->db->count_all_results();

    // ----- DATA QUERY WITH JOIN -----
    $this->db->select('songs.id, songs.title, songs.isActive, categories.name AS category_name');
    $this->db->from('songs');
    $this->db->join('categories', 'categories.id = songs.category_id', 'left');
    if (!empty($search)) {
        $this->db->like('songs.title', $search);
    }
    $this->db->limit($limit, $offset);
    $query = $this->db->get();
    $songs = $query->result();

    // ----- PAGINATION -----
    $total_pages = ceil($total_rows / $limit);

    $pagination = '';

    // Prev button
    $prev_disabled = ($page <= 1) ? 'disabled' : '';
    $prev_page = ($page > 1) ? $page - 1 : 1;
    $pagination .= "<li class='page-item $prev_disabled'>
                        <a href='javascript:void(0)' class='page-link' data-page='$prev_page'>Prev</a>
                    </li>";

    // Determine page range (only 3 pages)
    $start_page = max(1, $page - 1);
    $end_page = min($total_pages, $start_page + 2);
    if ($end_page - $start_page < 2) {
        $start_page = max(1, $end_page - 2);
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        $active = ($i == $page) ? 'active' : '';
        $pagination .= "<li class='page-item $active'>
                            <a href='javascript:void(0)' class='page-link' data-page='$i'>$i</a>
                        </li>";
    }

    // Next button
    $next_disabled = ($page >= $total_pages) ? 'disabled' : '';
    $next_page = ($page < $total_pages) ? $page + 1 : $total_pages;
    $pagination .= "<li class='page-item $next_disabled'>
                        <a href='javascript:void(0)' class='page-link' data-page='$next_page'>Next</a>
                    </li>";

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
    $song = $this->general_model->getOne('songs', ['id' => (int)$id]);
    if (!$song) { show_404(); }

    // main categories (active)
    $main_categories = $this->general_model->getAll('categories', [ 'isActive' => 1,'parent_id'=>null]);


    $data = [
        'song'            => $song,
        'main_categories' => $main_categories,
    ];
//     echo "<pre>";
// print_r($data);
// die;
    $this->load->view('admin/header');
    $this->load->view('admin/edit_song_form', $data);
    $this->load->view('admin/footer');
}


public function update_song()
{
    $this->load->helper('security');

    $id          = $this->input->post('id', true);
    $title       = $this->input->post('title', true);
    $description = $this->input->post('description', false); 
    $category_ids = $this->input->post('category_id');

    $final_category_id = null;
    if (is_array($category_ids)) {
        foreach ($category_ids as $catId) {
            if (!empty($catId)) {
                $final_category_id = (int)$catId;
            }
        }
    }

    // Validate required fields
    if (empty($id) || empty($title) || empty($final_category_id) || empty($description)) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => false,
                'message' => 'Song title, category, and description are required.'
            ]));
        return;
    }

    // Prepare update data
    $update_data = [
        'title'       => $title,
        'category_id' => $final_category_id,
        'description' => $description,
        'created_on'  => date('Y-m-d H:i:s')
    ];

    $this->db->where('id', $id);
    $this->db->update('songs', $update_data);

    if ($this->db->affected_rows() > 0) {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => true,
                'message' => 'Song updated successfully.'
            ]));
    } else {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => false,
                'message' => 'No changes detected or update failed.'
            ]));
    }
}



   public function add_new_song() {
    $data['categories'] = $this->general_model->getAll('categories', ['parent_id' => NULL]); 
    $this->load->view('admin/header');
    $this->load->view('admin/song_form', $data);
    $this->load->view('admin/footer');
}

  public function get_subcategories() {
    $raw_input = file_get_contents('php://input');
    $input_data = json_decode($raw_input, true);
    $parent_id = isset($input_data['parent_id']) ? (int)$input_data['parent_id'] : 0;

    if (!$parent_id) {
        echo json_encode(['status' => false, 'data' => []]);
        return;
    }

    $subcategories = $this->general_model->getAll('categories', [
        'parent_id' => $parent_id,
        'isActive' => 1
    ]);

    if (!empty($subcategories)) {
        echo json_encode(['status' => true, 'data' => $subcategories]);
    } else {
        echo json_encode(['status' => false, 'data' => []]);
    }
}

public function save_song() {
    $song_name = trim($this->input->post('song_name'));
    $categories = $this->input->post('category_id');

    // Ensure categories is always an array
    if (!is_array($categories)) {
        $categories = [$categories];  // wrap single value in array
    }

    // Validation: song name required
    if (empty($song_name)) {
        echo json_encode(['status' => false, 'message' => 'Song name is required.']);
        return;
    }

    // Validation: at least one category selected
    if (empty($categories) || empty($categories[0])) {
        echo json_encode(['status' => false, 'message' => 'Please select at least one category.']);
        return;
    }

    // Get the last selected category (deepest level)
    $final_category_id = end($categories);

    // Validation: ensure category_id is valid (not empty)
    if (empty($final_category_id)) {
        echo json_encode(['status' => false, 'message' => 'Please select a valid category.']);
        return;
    }

    // Prepare insert data
    $data = [
        'category_id' => $final_category_id,
        'title'       => $song_name,
        'description' => $this->input->post('song_lyrics', FALSE),
        'isActive'    => 1,
        'created_on'  => date('Y-m-d H:i:s')
    ];

    // Save to DB
    $insert_id = $this->general_model->insert('songs', $data);

if ($insert_id) {
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => true,
            'message' => 'Song saved successfully!',
            'song_id' => $insert_id
        ]));
} else {
    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => false,
            'message' => 'Failed to save song.'
        ]));
}

}



}