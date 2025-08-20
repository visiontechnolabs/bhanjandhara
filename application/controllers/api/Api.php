<?php

defined('BASEPATH') or exit('No direct script access allowed');


class Api extends CI_Controller
{
    
    public function __construct()
    {

        parent::__construct();

        $this->load->model('general_model');

        $this->load->helper(['url', 'form']);


        header("Access-Control-Allow-Origin: *"); 

        header("Content-Type: application/json; charset=UTF-8");
        $this->load->library('email');


    }

   public function get_category()
{
    $categories = $this->db
        ->where('isActive', 1)
        ->get('categories')
        ->result_array();

    $result = [];

    foreach ($categories as $cat) {
        // Count subcategories
        $subcat_count = $this->db
            ->where('main_category_id', $cat['id'])
            ->where('isActive', 1)
            ->count_all_results('subcategories');

        // Count songs directly under this category
        $song_count = $this->db
            ->where('category_id', $cat['id'])
            ->count_all_results('songs');

        $item = [
            'id'    => $cat['id'],
            'name'  => $cat['name'],
            'image' => base_url($cat['image']),
            'has_subcategories' => ($subcat_count > 0)
        ];

        // Add counts depending on what is found
        if ($subcat_count > 0) {
            $item['total_subcategories'] = $subcat_count;
        }
        if ($song_count > 0) {
            $item['total_songs'] = $song_count;
        }

        $result[] = $item;
    }

    echo json_encode([
        'status' => true,
        'code' => 200,
        'data' => $result
    ]);
}



  public function getSubCategories()
{
    // Read raw JSON input
    $raw_input = file_get_contents('php://input');
    $input_data = json_decode($raw_input, true);  // decode JSON to array

    $category_id = isset($input_data['categoryId']) ? $input_data['categoryId'] : null;

    // Validate input
    if (empty($category_id)) {
        echo json_encode([
            'code' => 400,
            'status' => false,
            'message' => 'Category ID is required'
        ]);
        return;
    }

    // Fetch subcategories
    $conditions = ['main_category_id' => $category_id];
    $subcategories = $this->general_model->getAll('subcategories', $conditions);

    if (!empty($subcategories)) {
        $result = [];
        foreach ($subcategories as $subcat) {
            // Count songs in songs table by sub_category_id
            $song_count = $this->general_model->getCount('songs', ['sub_category_id' => $subcat->id]);
            $result[] = [
                'sub_category_id' => $subcat->id,
                'sub_category_name' => $subcat->title, // assuming 'name' is the column in subcategories table
                'total_song' => !empty($song_count) ? $song_count : 0
            ];
        }

        echo json_encode([
            'code' => 200,
            'status' => true,
            'data' => $result
        ]);
    } else {
        echo json_encode([
            'code' => 400,
            'status' => false,
            'message' => 'No subcategories found'
        ]);
    }
}


public function getSong() {
    // Read raw JSON input
    $raw_input = file_get_contents('php://input');
    $input_data = json_decode($raw_input, true);  // decode JSON to array

    $category_id = isset($input_data['categoryId']) ? $input_data['categoryId'] : null;

    // Validate input
    if (empty($category_id)) {
        echo json_encode([
            'code'    => 400,
            'status'  => false,
            'message' => 'Category ID is required',
            'data'    => []
        ]);
        return;
    }

    // Fetch songs by category_id
    $conditions = ['category_id' => $category_id];
    $songs = $this->general_model->getAll('songs', $conditions);

    if (!empty($songs)) {
        $result = [];
        foreach ($songs as $song) {
            $result[] = [
                'title' => $song->title 
            ];
        }

        echo json_encode([
            'code'   => 200,
            'status' => true,
            'data'   => $result
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'code'    => 400,
            'status'  => false,
            'message' => 'No songs found for this category',
            'data'    => []
        ]);
    }

   
}
 public function get_sub_song(){
        
    $raw_input = file_get_contents('php://input');
    $input_data = json_decode($raw_input, true);  

    $category_id = isset($input_data['categoryId']) ? $input_data['categoryId'] : null;

    // Validate input
    if (empty($category_id)) {
        echo json_encode([
            'code'    => 400,
            'status'  => false,
            'message' => 'Category ID is required',
            'data'    => []
        ]);
        return;
    }

    // Fetch songs by category_id
    $conditions = ['sub_category_id' => $category_id];
    $songs = $this->general_model->getAll('songs', $conditions);

    if (!empty($songs)) {
        $result = [];
        foreach ($songs as $song) {
            $result[] = [
                'title' => $song->title // wrap each song name as { "title": "..." }
            ];
        }

        echo json_encode([
            'code'   => 200,
            'status' => true,
            'data'   => $result
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'code'    => 400,
            'status'  => false,
            'message' => 'No songs found for this category',
            'data'    => []
        ]);
    }

    }






}