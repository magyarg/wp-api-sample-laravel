<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \GuzzleHttp\Client;
use App\Http\Helpers\wpApi;

class FrontendController extends Controller {

    protected $api;

    /**
     * Class constructor. This method will be called first
     * on every new instance.
     */
    public function __construct() {
        $this->api = new wpApi(env('WP_API_ENDPOINT'));
    }

    /**
     * Returns a list (collection) of posts from the
     * WP API service.
     * @return Response|Collection
     */
    public function index() {
        // Fetch all posts
        $posts = $this->api->posts();

        // Fetch posts by category id
        // $posts = $this->api->posts(5);

        return view('index', compact('posts'));
    }

    /**
     * Returns a single item based on the $slug
     * parameter.
     * @param String $slug
     * @return Object
     */
    public function show($slug) {
        $post = $this->api->post($slug);
        if ($post && !empty($post)) {
            return view('show', compact('post'));
        }
        return redirect('/')->withErrors('A bejegyzés nem létezik!');
    }

    /**
     * Custom query result
     */
    public function customQuery() {
        $response = $this->api->query('categories');
        dd($response);
    }
}
