<?php

/**
 * Created by PhpStorm.
 * User: TA-MEDIA
 * Date: 4/10/2018
 * Time: 5:23 PM
 */
class ModelPostPost extends Model
{
    /**
     * Get thông tin bài viết
     * @param $id
     * @return array
     */
    public function getPost($id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "post p LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id) WHERE p.post_id = $id");
        if ($query->num_rows) {
            return array(
                'post_id'          => $query->row['post_id'],
                'language_id'      => $query->row['language_id'],
                'name'             => $query->row['name'],
                'description'      => $query->row['description'],
                'description'      => $query->row['description'],
                'tag'              => $query->row['tag'],
                'meta_title'       => $query->row['meta_title'],
                'meta_description' => $query->row['meta_description'],
                'meta_keyword'     => $query->row['meta_keyword'],
            );
        }
    }
}