<?php

/**
 * Created by PhpStorm.
 * User: TA-MEDIA
 * Date: 4/10/2018
 * Time: 12:00 AM
 */
class ControllerPostPost extends Controller
{
    public function index()
    {
        // Load model
        $this->load->model('post/post');
        $this->load->model('post/category');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        if (isset($this->request->get['path_post'])) {
            $path = '';

            $parts = explode('_', (string)$this->request->get['path_post']);

            $category_id = (int)array_pop($parts);

            foreach ($parts as $path_id) {
                if (!$path) {
                    $path = $path_id;
                } else {
                    $path .= '_' . $path_id;
                }

                $category_info = $this->model_post_category->getCategory($path_id);

                if ($category_info) {
                    $data['breadcrumbs'][] = array(
                        'text' => $category_info['name'],
                        'href' => $this->url->link('product/category', 'path_post=' . $path)
                    );
                }
            }

            // Set the last category breadcrumb
            $category_info = $this->model_post_category->getCategory($category_id);

            if ($category_info) {
                $url = '';

                if (isset($this->request->get['sort'])) {
                    $url .= '&sort=' . $this->request->get['sort'];
                }

                if (isset($this->request->get['order'])) {
                    $url .= '&order=' . $this->request->get['order'];
                }

                if (isset($this->request->get['page'])) {
                    $url .= '&page=' . $this->request->get['page'];
                }

                if (isset($this->request->get['limit'])) {
                    $url .= '&limit=' . $this->request->get['limit'];
                }

                $data['breadcrumbs'][] = array(
                    'text' => $category_info['name'],
                    'href' => $this->url->link('post/category', 'path_post=' . $this->request->get['path_post'] . $url)
                );
            }

        }

        if (isset($this->request->get['post_id'])) {
            $post_id = (int)$this->request->get['post_id'];
        } else {
            $post_id = 0;
        }

        $post_info = $this->model_post_post->getPost($post_id);

        if ($post_info) {
            //Hiển thị chi tiết nội dung

            $this->document->setTitle($post_info['meta_title']);
            $this->document->setDescription($post_info['meta_description']);
            $this->document->setKeywords($post_info['meta_keyword']);

            $data['heading_title'] = $post_info['name'];

            echo '<pre>';
            print_r($post_info);
            echo '</pre>';

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('post/post', $data));
        } else {
            //Màn hình post not found

            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('post/post', $data));
        }

    }
}