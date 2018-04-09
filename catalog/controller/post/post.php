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
        // $this->load->language('post/post');
        echo 'Phung The Anh';

        echo '<pre>';
        print_r($this->request);
        echo '</pre>';
    }
}