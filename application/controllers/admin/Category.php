<?php
class Category extends CI_Controller
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
        $this->load->view('admin/category_view');
        $this->load->view('admin/footer');
    }

    public function fetch_categories()
    {

        $page = $this->input->post('page');
        $search = $this->input->post('search');

        $limit = 10; // items per page
        $offset = ($page - 1) * $limit;

        $categories = $this->general_model->get_categories($limit, $offset, $search);
        $total = $this->general_model->count_categories($search);

        echo json_encode([
            'data' => $categories,
            'total' => $total,
            'limit' => $limit,
            'page' => $page
        ]);
    }

    public function add_category()
    {
        $this->load->view('admin/header');
        $this->load->view('admin/category_form');
        $this->load->view('admin/footer');
    }
    public function save()
    {
        $categoryName = $this->input->post('category_title');

        // Check if already exists
        $exists = $this->db->where('name', $categoryName)->get('categories')->row();

        if ($exists) {
            echo json_encode([
                'status' => 'exists',
                'message' => 'Category already exists!'
            ]);
            return;
        }

        // Image upload
        if (!empty($_FILES['category_image']['name'])) {
            $config['upload_path'] = './uploads/categoryimage/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['file_name'] = time();

            $this->load->library('upload', $config);

            if (!$this->upload->do_upload('category_image')) {
                echo json_encode([
                    'status' => 'error',
                    'message' => $this->upload->display_errors()
                ]);
                return;
            }

            $uploadData = $this->upload->data();
            $image = 'uploads/categoryimage/' . $uploadData['file_name'];
        } else {
            $image = '';
        }

        // Save to DB
        $data = [
            'name' => $categoryName,
            'image' => $image,
            'created_on' => date('Y-m-d H:i:s')
        ];
        // echo "<pre>";
// print_r($data);
// die;
        $this->db->insert('categories', $data);

        echo json_encode([
            'status' => 'success',
            'message' => 'Category saved successfully!'
        ]);
    }
    public function edit_main($id)
    {
        $category = $this->general_model->getOne('categories', ['id' => $id]);

        if (!$category) {
            show_404();
        }

        $data['category'] = $category;
        //    echo "<pre>";
        //    print_r($data['category']);
        //    die;
        $this->load->view('admin/header');
        $this->load->view('admin/edit_main_cat_form', $data);
        $this->load->view('admin/footer');
    }
    public function update_main_cat()
    {
        $id = $this->input->post('id');
        $name = $this->input->post('category_title'); // maps to `name` field in DB
        $isActive = $this->input->post('isActive'); // optional status toggle

        // Fetch old record for image cleanup
        $old = $this->general_model->getOne('categories', ['id' => $id]);

        $data = [
            'name' => $name,
            'isActive' => isset($isActive) ? $isActive : 1, // default to 1 (active) if not set
        ];

        // Handle new image upload
        if (!empty($_FILES['image']['name'])) {
            $config['upload_path'] = './uploads/categoryimage/';
            $config['allowed_types'] = 'jpg|jpeg|png|webp';
            $config['file_name'] = time() . '_' . $_FILES['image']['name'];
            $this->load->library('upload', $config);

            if ($this->upload->do_upload('image')) {
                $uploadData = $this->upload->data();
                $data['image'] = 'uploads/categoryimage/' . $uploadData['file_name'];

                // Delete old image if it exists
                if (!empty($old->image) && file_exists('./' . $old->image)) {
                    unlink('./' . $old->image);
                }
            } else {
                echo json_encode(['status' => false, 'message' => strip_tags($this->upload->display_errors())]);
                return;
            }
        }
        // echo "<pre>";
// print_r($data);
// die;
        // Update the record
        $update = $this->general_model->update('categories', ['id' => $id], $data);

        if ($update) {
            echo json_encode(['status' => true, 'message' => 'Category updated successfully']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Failed to update category']);
        }
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

                $update = $this->general_model->update('categories', $where, $data);


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

    // sub cat start
    public function sub_category()
    {
        $this->load->view('admin/header');
        $this->load->view('admin/sub_category_view');
        $this->load->view('admin/footer');
    }
    public function sub_ajax_list()
    {
        $page = $this->input->post('page') ?: 1;
        $search = trim($this->input->post('search'));
        $limit = $this->input->post('limit') ?: 10;  
        $offset = ($page - 1) * $limit;

        $this->load->model('general_model');

        // Base query
        $this->db->select('s.id, s.title AS subcategory_name, s.isActive, c.name AS category_name');
        $this->db->from('subcategories s');
        $this->db->join('categories c', 'c.id = s.main_category_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('s.title', $search);
            $this->db->or_like('c.name', $search);
            $this->db->group_end();
        }

        $this->db->limit($limit, $offset);
        $data = $this->db->get()->result_array();

        // Count total records
        $this->db->select('COUNT(*) AS total');
        $this->db->from('subcategories s');
        $this->db->join('categories c', 'c.id = s.main_category_id', 'left');

        if (!empty($search)) {
            $this->db->group_start();
            $this->db->like('s.title', $search);
            $this->db->or_like('c.name', $search);
            $this->db->group_end();
        }

        $total = $this->db->get()->row()->total;
        $totalPages = ceil($total / $limit);

        echo json_encode([
            'data' => $data,
            'current_page' => (int) $page,
            'total_pages' => (int) $totalPages,
            'start_index' => $offset + 1,
        ]);
    }
    public function add_sub_category()
    {
        $data['main_categories'] = $this->general_model->getAll('categories');

        $this->load->view('admin/header');
        $this->load->view('admin/sub_category_form', $data);
        $this->load->view('admin/footer');
    }
    public function save_sub_category()
    {
        $subcategory_title = $this->input->post('subcategory_title');
        $main_category_id = $this->input->post('main_category_id');

        if (empty($subcategory_title) || empty($main_category_id)) {
            echo json_encode(['success' => false]);
            return;
        }

        // Check for duplicate
        $exist = $this->general_model->getOne('subcategories', [
            'title' => $subcategory_title,
            'main_category_id' => $main_category_id
        ]);

        if ($exist) {
            echo json_encode(['success' => 'exist']);
            return;
        }

        // Insert data
        $data = [
            'title' => $subcategory_title,
            'main_category_id' => $main_category_id,
            'created_on' => date('Y-m-d H:i:s')
        ];

        $this->general_model->insert('subcategories', $data);
        $insert_id = $this->db->insert_id();

        echo json_encode(['success' => (bool) $insert_id]);
    }
    public function toggle_status_sub_2()
    {
        $id = $this->input->post('id');
        $status = $this->input->post('status');

        if (is_numeric($id)) {
            $this->load->model('general_model');
            $updated = $this->general_model->update('subcategories', ['id' => $id], ['isActive' => $status]);

            echo json_encode([
                'success' => $updated,
                'message' => $status == 1 ? 'Published successfully' : 'Unpublished successfully'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
    }
    public function edit($id)
    {
        $subcategory = $this->general_model->getOne('subcategories', ['id' => $id], true);
        if (!$subcategory) {
            show_404();
        }

        // Fetch all main categories for dropdown (assuming table name is `main_category`)
        $data['main_categories'] = $this->general_model->getAll('categories');
        $data['sub_categories'] = $subcategory;
        // echo "<pre>";
        // print_r($data['main_categories']);
        // echo "<pre>";
        // print_r($data['sub_categories']);
        // die;
        $this->load->view('admin/header');
        $this->load->view('admin/edit_subcat_form', $data);
        $this->load->view('admin/footer');
    }
    public function edit_subcategory(){
$id = $this->input->post('id'); // Hidden field or URL param
    $title = $this->input->post('subcategory_title');
    $main_category_id = $this->input->post('main_category_id');

    // Validate input
    if (empty($title) || empty($main_category_id)) {
        echo json_encode(['status' => false, 'message' => 'Required fields missing.']);
        return;
    }

    // Prepare data
    $data = [
        'title' => $title,
        'main_category_id' => $main_category_id
    ];

    // Update database
    $this->db->where('id', $id);
    $updated = $this->db->update('subcategories', $data);

    if ($updated) {
        echo json_encode(['status' => true, 'message' => 'Subcategory updated successfully!']);
    } else {
        echo json_encode(['status' => false, 'message' => 'Failed to update subcategory.']);
    }

 }
}