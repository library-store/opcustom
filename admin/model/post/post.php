<?php

class ModelPostPost extends Model
{
    public function addPost($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "post SET status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

        $post_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "post SET image = '" . $this->db->escape($data['image']) . "' WHERE post_id = '" . (int)$post_id . "'");
        }

        foreach ($data['post_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "post_description SET post_id = '" . (int)$post_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', unsign_name = '" . $this->db->escape(str_ascii($value['name'])) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        if (isset($data['post_category'])) {
            foreach ($data['post_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "post_to_category SET post_id = '" . (int)$post_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        // SEO URL
        if (isset($data['post_seo_url'])) {
            foreach ($data['post_seo_url'] as $language_id => $keyword) {
                if (!empty($keyword)) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET language_id = '" . (int)$language_id . "', query = 'post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape(str_slug($keyword)) . "'");
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET language_id = '" . (int)$language_id . "', query = 'post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape(str_slug($data['post_description'][$language_id]['name'])) . "'");
                }
            }
        }

        if (isset($data['post_layout'])) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "post_to_layout SET post_id = '" . (int)$post_id . "', layout_id = '" . (int)$data['post_layout'] . "'");
        }

        $this->cache->delete('post');

        return $post_id;
    }

    public function editPost($post_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "post SET status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE post_id = '" . (int)$post_id . "'");

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "post SET image = '" . $this->db->escape($data['image']) . "' WHERE post_id = '" . (int)$post_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "post_description WHERE post_id = '" . (int)$post_id . "'");

        foreach ($data['post_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "post_description SET post_id = '" . (int)$post_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', unsign_name = '" . $this->db->escape(str_ascii($value['name'])) . "', description = '" . $this->db->escape($value['description']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "post_to_category WHERE post_id = '" . (int)$post_id . "'");

        if (isset($data['post_category'])) {
            foreach ($data['post_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "post_to_category SET post_id = '" . (int)$post_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        // SEO URL
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'post_id=" . (int)$post_id . "'");

        if (isset($data['post_seo_url'])) {
            foreach ($data['post_seo_url'] as $language_id => $keyword) {
                if (!empty($keyword)) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET language_id = '" . (int)$language_id . "', query = 'post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape($keyword) . "'");
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET language_id = '" . (int)$language_id . "', query = 'post_id=" . (int)$post_id . "', keyword = '" . $this->db->escape($data['post_description'][$language_id]['name']) . "'");
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "post_to_layout WHERE post_id = '" . (int)$post_id . "'");

        if (isset($data['post_layout'])) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "post_to_layout SET post_id = '" . (int)$post_id . "', layout_id = '" . (int)$data['post_layout'] . "'");
        }

        $this->cache->delete('post');
    }

    public function copyPost($post_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "post p WHERE p.post_id = '" . (int)$post_id . "'");

        if ($query->num_rows) {
            $data = $query->row;

            $data['viewed'] = '0';
            $data['keyword'] = '';
            $data['status'] = '0';

            $data['post_description'] = $this->getPostDescriptions($post_id);
            $data['post_category'] = $this->getPostCategories($post_id);
            $data['post_layout'] = $this->getPostLayouts($post_id);

            $this->addPost($data);
        }
    }

    public function deletePost($post_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "post WHERE post_id = '" . (int)$post_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "post_description WHERE post_id = '" . (int)$post_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "post_related WHERE post_id = '" . (int)$post_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "post_related WHERE related_id = '" . (int)$post_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "post_to_category WHERE post_id = '" . (int)$post_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "post_to_layout WHERE post_id = '" . (int)$post_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "post_review WHERE post_id = '" . (int)$post_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'post_id=" . (int)$post_id . "'");

        $this->cache->delete('post');
    }

    public function getPost($post_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "post p LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id) WHERE p.post_id = '" . (int)$post_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getPosts($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "post p LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        $sql .= " GROUP BY p.post_id";

        $sort_data = array(
            'pd.name',
            'p.status',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getProductsByCategoryId($category_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "post p LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id) LEFT JOIN " . DB_PREFIX . "post_to_category p2c ON (p.post_id = p2c.post_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

        return $query->rows;
    }

    public function getPostDescriptions($post_id)
    {
        $post_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "post_description WHERE post_id = '" . (int)$post_id . "'");

        foreach ($query->rows as $result) {
            $post_description_data[$result['language_id']] = array(
                'name'             => $result['name'],
                'unsign_name'      => $result['unsign_name'],
                'description'      => $result['description'],
                'meta_title'       => $result['meta_title'],
                'meta_description' => $result['meta_description'],
                'meta_keyword'     => $result['meta_keyword'],
                'tag'              => $result['tag']
            );
        }

        return $post_description_data;
    }

    public function getPostCategories($post_id)
    {
        $post_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "post_to_category WHERE post_id = '" . (int)$post_id . "'");

        foreach ($query->rows as $result) {
            $post_category_data[] = $result['category_id'];
        }

        return $post_category_data;
    }

    public function getPostSeoUrls($post_id)
    {
        $post_seo_url_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'post_id=" . (int)$post_id . "'");

        foreach ($query->rows as $result) {
            $post_seo_url_data[$result['language_id']] = $result['keyword'];
        }

        return $post_seo_url_data;
    }

    public function getPostLayout($post_id)
    {
        $layout_id = 0;
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "post_to_layout WHERE post_id = '" . (int)$post_id . "'");
        if (isset($query->row['layout_id'])) {
            $layout_id = $query->row['layout_id'];
        }

        return $layout_id;
    }

    public function getPostRelated($post_id)
    {
        $product_related_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "post_related WHERE post_id = '" . (int)$post_id . "'");

        foreach ($query->rows as $result) {
            $product_related_data[] = $result['related_id'];
        }

        return $product_related_data;
    }

    public function getTotalPosts($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT p.post_id) AS total FROM " . DB_PREFIX . "post p LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id)";

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getTotalPostsByLayoutId($layout_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "post_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

        return $query->row['total'];
    }
}
