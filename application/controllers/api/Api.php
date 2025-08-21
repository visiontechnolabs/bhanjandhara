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
    // Fetch top-level categories
    $categories = $this->db
        ->where('isActive', 1)
        ->where('parent_id', NULL)
        ->get('categories')
        ->result_array();

    $result = [];

    foreach ($categories as $cat) {
        // Get subcategories
        $subcategories = $this->db
            ->where('isActive', 1)
            ->where('parent_id', $cat['id'])
            ->get('categories')
            ->result_array();

        if (!empty($subcategories)) {
            // If there are subcategories
            $subcat_count = count($subcategories);
            $subcat_ids   = array_column($subcategories, 'id');

            $song_count = 0;
            if (!empty($subcat_ids)) {
                $song_count = $this->db
                    ->where_in('category_id', $subcat_ids)
                    ->count_all_results('songs');
            }

            $result[] = [
                'id'                  => $cat['id'],
                'name'                => $cat['name'],
                'image'               => base_url($cat['image']),
                'has_subcategories'   => true,
                'total_subcategories' => $subcat_count,
                'total_songs'         => $song_count
            ];

        } else {
            // If no subcategories
            $song_count = $this->db
                ->where('category_id', $cat['id'])
                ->count_all_results('songs');

            $result[] = [
                'id'                => $cat['id'],
                'name'              => $cat['name'],
                'image'             => base_url($cat['image']),
                'has_subcategories' => false,
                'total_songs'       => $song_count
            ];
        }
    }

    echo json_encode([
        'status' => true,
        'code'   => 200,
        'data'   => $result
    ]);
}




public function getSubCategories()
{
    // Get category ID from GET params
    $parent_id = $this->input->get('id');

    // Validate input
    if (empty($parent_id)) {
        echo json_encode([
            'code'    => 400,
            'status'  => false,
            'message' => 'Category ID is required',
            'data'    => []
        ]);
        return;
    }

    // Fetch direct subcategories of this parent
    $subcategories = $this->db
        ->where('isActive', 1)
        ->where('parent_id', $parent_id)
        ->get('categories')
        ->result_array();

    if (empty($subcategories)) {
        echo json_encode([
            'code'    => 400,
            'status'  => false,
            'message' => 'No subcategories found',
            'data'    => []
        ]);
        return;
    }

    $result = [];

    foreach ($subcategories as $subcat) {
        // Fetch children of this subcategory
        $child_subcats = $this->db
            ->where('isActive', 1)
            ->where('parent_id', $subcat['id'])
            ->get('categories')
            ->result_array();

        if (!empty($child_subcats)) {
            // If this subcategory has further children
            $child_count = count($child_subcats);
            $child_ids   = array_column($child_subcats, 'id');

            // Count songs inside all child subcategories
            $song_count = 0;
            if (!empty($child_ids)) {
                $song_count = $this->db
                    ->where_in('category_id', $child_ids)
                    ->count_all_results('songs');
            }

            $result[] = [
                'id'                  => $subcat['id'],
                'name'                => $subcat['name'],
                'image'               => !empty($subcat['image']) ? base_url($subcat['image']) : '',
                'has_subcategories'   => true,
                'total_subcategories' => $child_count,
                'total_songs'         => $song_count
            ];
        } else {
            // No child categories → count songs directly in this subcategory
            $song_count = $this->db
                ->where('category_id', $subcat['id'])
                ->count_all_results('songs');

            $result[] = [
                'id'                => $subcat['id'],
                'name'              => $subcat['name'],
                'image'             => !empty($subcat['image']) ? base_url($subcat['image']) : '',
                'has_subcategories' => false,
                'total_songs'       => $song_count
            ];
        }
    }

    echo json_encode([
        'code'   => 200,
        'status' => true,
        'data'   => $result
    ]);
}



public function getSong()
{
    // Get category ID from GET params
    $category_id = $this->input->get('id');

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
                'id'          => $song->id,
                'title'       => $song->title,
                // 'description' => $song->description,
                // 'created_on'  => $song->created_on
            ];
        }

        echo json_encode([
            'code'   => 200,
            'status' => true,
            'data'   => $result
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'code'    => 404,
            'status'  => false,
            'message' => 'No songs found for this category',
            'data'    => []
        ]);
    }
}

public function song_details()
{
    // Get song ID from GET params
    $song_id = $this->input->get('id');

    // Validate input
    if (empty($song_id)) {
        echo json_encode([
            'code'    => 400,
            'status'  => false,
            'message' => 'Song ID is required',
            'data'    => []
        ]);
        return;
    }

    // Fetch song details
    $song = $this->general_model->getOne('songs', ['id' => $song_id]);

    if (!empty($song)) {
        $result = [
            'id'          => $song->id,
            'title'       => $song->title,
            'description' => $song->description,
            'category_id' => $song->category_id,
            'created_on'  => $song->created_on
        ];

        echo json_encode([
            'code'    => 200,
            'status'  => true,
            'message' => 'Song details fetched successfully',
            'data'    => $result
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'code'    => 404,
            'status'  => false,
            'message' => 'Song not found',
            'data'    => []
        ]);
    }
}





}