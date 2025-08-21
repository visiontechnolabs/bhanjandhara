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
    $page   = (int) $this->input->post('page') ?: 1;
    $search = $this->input->post('search');

    // Fetch ALL categories
    $categories = $this->general_model->get_all_categories($search);

    // Build full tree
    $tree = $this->buildCategoryTree($categories);

    // Flatten tree with level info
    $flatList = $this->flattenCategoryTree($tree);

    // Paginate flat list
    $limit = 10;
    $offset = ($page - 1) * $limit;
    $total = count($flatList);

    $paged = array_slice($flatList, $offset, $limit);

    echo json_encode([
        'data'  => $paged,
        'total' => $total,
        'limit' => $limit,
        'page'  => $page
    ]);
}

private function buildCategoryTree($categories, $parentId = NULL)
{
    $branch = [];
    foreach ($categories as $cat) {
        if ($cat->parent_id == $parentId) {
            $children = $this->buildCategoryTree($categories, $cat->id);
            $cat->children = $children;
            $branch[] = $cat;
        }
    }
    return $branch;
}

private function flattenCategoryTree($tree, $level = 0)
{
    $flat = [];
    foreach ($tree as $node) {
        $item = clone $node;    // copy
        unset($item->children); // remove nested
        $item->level = $level;  // keep indentation info
        $flat[] = $item;

        if (!empty($node->children)) {
            $flat = array_merge($flat, $this->flattenCategoryTree($node->children, $level + 1));
        }
    }
    return $flat;
}



    public function add_category()
    {
    $data['categories'] = $this->getCategoryTree();

        $this->load->view('admin/header');
        $this->load->view('admin/category_form',$data);
        $this->load->view('admin/footer');
    }
   public function save()
{
    $categoryName = $this->input->post('category_title');
    $parentId = $this->input->post('parent_id') ?: NULL;

    // Check if already exists under same parent
    $exists = $this->db
        ->where('name', $categoryName)
        ->where('parent_id', $parentId)
        ->get('categories')
        ->row();

    if ($exists) {
        echo json_encode([
            'status' => 'exists',
            'message' => 'Category already exists under the same parent!'
        ]);
        return;
    }

    // Image upload
    $image = '';
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
    }

    
    $data = [
        'name' => $categoryName,
        'parent_id' => $parentId,
        'image' => $image,
        'created_on' => date('Y-m-d H:i:s')
    ];

    $this->db->insert('categories', $data);

    echo json_encode([
        'status' => 'success',
        'message' => 'Category saved successfully!'
    ]);
}
private function getCategoryTree($parentId = NULL, $prefix = '')
{
    $result = [];
    $categories = $this->db->where('parent_id', $parentId)
                           ->order_by('name', 'ASC')
                           ->get('categories')
                           ->result();

    foreach ($categories as $cat) {
        // Add a display name with indentation
        $cat->display_name = $prefix . $cat->name;
        $result[] = $cat;

        // Fetch children recursively
        $children = $this->getCategoryTree($cat->id, $prefix . '-- ');
        $result = array_merge($result, $children);
    }

    return $result;
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

   
}