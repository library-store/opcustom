<?php

/**
 * Created by PhpStorm.
 * User: TA-MEDIA
 * Date: 4/10/2018
 * Time: 5:23 PM
 */
class ModelPostCategory extends Model
{
    public function getCategory($category_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "category_post cp LEFT JOIN " . DB_PREFIX . "category_post_description cpd ON (cp.category_id = cpd.category_id) WHERE cp.category_id = $category_id AND cp.status = 1");
        if ($query->num_rows) {
            return array(
                'category_id'      => $query->row['category_id'],
                'image'            => $query->row['image'],
                'parent_id'        => $query->row['parent_id'],
                'sort_order'       => $query->row['sort_order'],
                'name'             => $query->row['name'],
                'description'      => $query->row['description'],
                'meta_title'       => $query->row['meta_title'],
                'meta_description' => $query->row['meta_description'],
                'meta_keyword'     => $query->row['meta_keyword'],
            );
        }
    }
}