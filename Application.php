<?php

/*
 * This file is part of Twig.
 *
 * (c) Gingdev - 2019
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * @final
 */

use Medoo\Medoo;
use Cocur\Slugify\Slugify;

class Twig_Extension_Application extends Twig_Extension
{
    protected $db;

    protected $uri;

    public function __construct($data, $uri)
    {
        $this->db = new Medoo([
                'database_type' => 'sqlite',
                'database_file' => $data,
            ]);

        $this->uri = $uri;
    }

    public function getFunctions()
    {
        $functions = [
            'ip',
            'get_uri_segments',
            'get_post',
            'get_get',
            'get_cookie',
            'set_cookie',
            'delete_cookie',
            'get_session',
            'set_session',
            'delete_session',
            'request_method',
            'user_agent',
            'redirect',
            'get_data',
            'save_data',
            'get_data_by_id',
            'update_data_by_id',
            'get_data_count',
            'delete_data_by_id',
            'get_url',
            'get_mail',
        ];

        foreach ($functions as $function) {
            $data[] = new Twig_SimpleFunction($function, array($this, $function));
        }

        return $data;
    }

    public function getFilters()
    {
        $filters = [
            'json_decode',
            'slugify_url',
        ];

        foreach ($filters as $filter) {
            $data[] = new Twig_SimpleFilter($filter, array($this, $filter));
        }

        return $data;
    }

    public function get_data($key = null, $per_page = null, $page = null, $order = null)
    {
        $check = $this->checkin([
            'key' => $key,
        ], [
            'per_page' => $per_page,
            'page' => $page,
        ]);
        if ($check['error']) {
            return $check['error'];
        }
        $order = empty($order) ? $order : 'time';
        $per_page = empty($per_page) ? 10 : $check['per_page'];
        $page = empty($page) ? 0 : $check['page'];
        $result = $this->db->select('custom_data', '*', [
            'key' => $key,
            'ORDER' => [$order => 'DESC'],
            'LIMIT' => [$page, $per_page],
        ]);
        if ($result) {
            return $result;
        }
    }

    public function get_data_by_id($key = null, $id = null)
    {
        $check = $this->checkin([
            'key' => $key,
        ], [
            'id' => $id,
        ]);
        if ($check['error']) {
            return $check['error'];
        }
        $id = $check['id'];
        $result = $this->db->select('custom_data', '*', [
            'id' => $id,
            'key' => $key,
        ]);
        if ($result[0]) {
            return $result[0];
        }
    }

    public function save_data($key = null, $data = null)
    {
        $check = $this->checkin([
            'key' => $key,
            'data' => $data,
        ]);
        if ($check['error']) {
            return $check['error'];
        }
        $this->db->pdo->beginTransaction();
        $this->db->insert('custom_data', [
            'key' => $key,
            'data' => $data,
            'time' => time(),
        ]);
        $this->db->pdo->commit();
    }

    public function get_data_count($key = null)
    {
        $check = $this->checkin([
            'key' => $key,
        ]);
        if ($check['error']) {
            return $check['error'];
        }
        $result = $this->db->count('custom_data', [
            'key' => $key,
        ]);

        return $result;
    }

    public function delete_data_by_id($key = null, $id = null)
    {
        $check = $this->checkin([
            'key' => $key,
        ], [
            'id' => $id,
        ]);
        if ($check['error']) {
            return $check['error'];
        }
        $id = $check['id'];
        $result = $this->db->count('custom_data', [
            'id' => $id,
            'key' => $key,
        ]);
        if (1 == $result) {
            $this->db->pdo->beginTransaction();
            $this->db->delete('custom_data', [
                'id' => $id,
            ]);
            $this->db->pdo->commit();
        }
    }

    public function update_data_by_id($key = null, $id = null, $data = null)
    {
        $check = $this->checkin([
            'key' => $key,
            'data' => $data,
        ], [
            'id' => $id,
        ]);
        if ($check['error']) {
            return $check['error'];
        }
        $id = $check['id'];
        $result = $this->db->count('custom_data', [
            'id' => $id,
            'key' => $key,
        ]);
        if (1 == $result) {
            $this->db->pdo->beginTransaction();
            $this->db->update('custom_data', [
                'data' => $data,
            ], [
                'id' => $id,
            ]);
            $this->db->pdo->commit();
        }
    }

    public function request_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function get_uri_segments()
    {
        return $this->uri;
    }

    public function redirect($value = '/')
    {
        if ($value) {
            return header('Location: '.$value);
        }
    }

    public function get_post($string = null)
    {
        if ($string) {
            return $_POST[$string];
        }
    }

    public function get_get($string = null)
    {
        if ($string) {
            return $_GET[$string];
        }
    }

    public function user_agent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    public function set_cookie($name = null, $value = null)
    {
        if ($name && $value) {
            return setcookie($name, $value, time() + 3600 * 24 * 365);
        }
    }

    public function delete_cookie($name = null)
    {
        if ($name) {
            return setcookie($name, '');
        }
    }

    public function get_cookie($name = null)
    {
        if ($name) {
            return $_COOKIE[$name];
        }
    }

    public function set_session($name = null, $value = null)
    {
        if ($name && $value) {
            $_SESSION[$name] = $value;
        }
    }

    public function delete_session($name = null)
    {
        if ($name) {
            unset($_SESSION[$name]);
        }
    }

    public function get_session($name = null)
    {
        if ($name) {
            return $_SESSION[$name];
        }
    }

    public function json_decode($string = null)
    {
        return json_decode($string, true);
    }

    public function slugify_url($url = null)
    {
        $slugify = new Slugify();

        return $slugify->slugify($url);
    }

    public function get_url($url = null)
    {
        $curl = new Curl\Curl();
        $curl->get($url);
        $result = $curl->response;
        $curl->close();

        return $result;
    }

    public function get_mail($loai = null, $count = false)
    {
        $dir = 'upload/mail/'.$loai.'.txt';
        $text = file_get_contents($dir);
        $data = file($dir, FILE_SKIP_EMPTY_LINES);
        if ($count) {
            return count($data);
        }
        $data = $data[rand(0, count($data) - 1)];
        file_put_contents($dir, str_replace($data, '', $text));

        return $data;
    }

    protected function checkin(array $args = [], array $input = [])
    {
        foreach ($args as $key => $value) {
            if (!$value) {
                return ['error' => ucwords($key).' must '.(is_null($value) ? 'be a string!' : 'not be empty!')];
            }
        }
        if ($input) {
            $result = [];
            foreach ($input as $key => $value) {
                $result[$key] = abs(intval($value));
            }

            return $result;
        }
    }

    public function getName()
    {
        return 'application';
    }
}

class_alias('Twig_Extension_Application', 'Twig\Extension\ApplicationExtension', false);
