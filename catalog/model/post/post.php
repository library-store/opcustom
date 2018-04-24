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
    public function getPost($post_id)
    {
        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "post_review r1 WHERE r1.post_id = p.post_id AND r1.status = '1' GROUP BY r1.post_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "post_review r2 WHERE r2.post_id = p.post_id AND r2.status = '1' GROUP BY r2.post_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "post p LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id) WHERE p.post_id = '" . (int)$post_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1'");

        if ($query->num_rows) {
            return array(
                'post_id'       => $query->row['post_id'],
                'name'             => $query->row['name'],
                'description'      => $query->row['description'],
                'meta_title'       => $query->row['meta_title'],
                'meta_description' => $query->row['meta_description'],
                'meta_keyword'     => $query->row['meta_keyword'],
                'tag'              => $query->row['tag'],
                'image'            => $query->row['image'],
                'rating'           => round($query->row['rating']),
                'reviews'          => $query->row['reviews'] ? $query->row['reviews'] : 0,
                'sort_order'       => $query->row['sort_order'],
                'status'           => $query->row['status'],
                'date_added'       => $query->row['date_added'],
                'date_modified'    => $query->row['date_modified'],
                'viewed'           => $query->row['viewed']
            );
        }
    }

    /**
     * Get list bài viết
     * @param array $data
     * @return array
     */
    public function getPosts($data = array())
    {
        $sql = "SELECT p.post_id, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "post_review r1 WHERE r1.post_id = p.post_id AND r1.status = '1' GROUP BY r1.post_id) AS rating";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " FROM " . DB_PREFIX . "category_post_path cp LEFT JOIN " . DB_PREFIX . "post_to_category p2c ON (cp.category_id = p2c.category_id)";
            } else {
                $sql .= " FROM " . DB_PREFIX . "post_to_category p2c";
            }

            $sql .= " LEFT JOIN " . DB_PREFIX . "post p ON (p2c.post_id = p.post_id)";
        } else {
            $sql .= " FROM " . DB_PREFIX . "post p";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1'";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                }
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            $sql .= ")";
        }

        $sql .= " GROUP BY p.post_id";

        $sort_data = array(
            'pd.name',
            'p.image',
            'rating',
            'p.sort_order',
            'p.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                $sql .= " ORDER BY LCASE(" . $data['sort'] . ")";
            } elseif ($data['sort'] == 'p.price') {
                $sql .= " ORDER BY (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
            } else {
                $sql .= " ORDER BY " . $data['sort'];
            }
        } else {
            $sql .= " ORDER BY p.sort_order";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC, LCASE(pd.name) DESC";
        } else {
            $sql .= " ASC, LCASE(pd.name) ASC";
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

        $post_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $post_data[$result['post_id']] = $this->getPost($result['post_id']);
        }

        return $post_data;
    }

    /**
     * Get tổng bài viết
     * @return int
     */
    public function getTotalPosts($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT p.post_id) AS total";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " FROM " . DB_PREFIX . "category_post_path cp LEFT JOIN " . DB_PREFIX . "post_to_category p2c ON (cp.category_id = p2c.category_id)";
            } else {
                $sql .= " FROM " . DB_PREFIX . "post_to_category p2c";
            }

            if (!empty($data['filter_filter'])) {
                $sql .= " LEFT JOIN " . DB_PREFIX . "post_filter pf ON (p2c.post_id = pf.post_id) LEFT JOIN " . DB_PREFIX . "post p ON (pf.post_id = p.post_id)";
            } else {
                $sql .= " LEFT JOIN " . DB_PREFIX . "post p ON (p2c.post_id = p.post_id)";
            }
        } else {
            $sql .= " FROM " . DB_PREFIX . "post p";
        }

        $sql .= " LEFT JOIN " . DB_PREFIX . "post_description pd ON (p.post_id = pd.post_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1'";

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }

            if (!empty($data['filter_filter'])) {
                $implode = array();

                $filters = explode(',', $data['filter_filter']);

                foreach ($filters as $filter_id) {
                    $implode[] = (int)$filter_id;
                }

                $sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                }
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            $sql .= ")";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }
}