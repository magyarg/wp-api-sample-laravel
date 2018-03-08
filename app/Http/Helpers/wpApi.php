<?php
namespace App\Http\Helpers;

use \Exception;

class wpApi {

    protected $url = '';

    /**
     * First thing we set the api endpoint base url
     * as a new instance is created.
     */
    public function __construct($apiEndpoint) {
        if (is_null($apiEndpoint) || strlen($apiEndpoint) == 0) {
            throw new Exception('You have to define the WP_API_ENDPOINT record in your .env file!');
        }
        $this->url = $apiEndpoint;
    }

    /**
     * Returning a new Post object as an array of objects.
     * @return Object $formattedObjects
     */
    public function posts() {
        $posts = collect($this->getResponse($this->url . 'posts'));
        $formattedObjects = [];
        foreach($posts as $post) {
            $formattedObjects[] = $this->extractPost($post, true);
        }
        return $formattedObjects;
    }

    /**
     * Returns a new single extracted Post object
     * based on the $slug paramter.
     * @param String $slug
     * @return Object
     */
    public function post($slug) {
        $post = collect($this->getResponse($this->url . 'posts?slug=' . $slug));
        if ($post && isset($post[0])) {
            return $this->extractPost($post[0]);
        }
        return [];
    }

    /**
     * General query method to reach all the WP API
     * endpoints.
     * @param String $queryEndPoint
     * @return Object
     */
    public function query($queryEndPoint) {
        $query = collect($this->getResponse($this->url . $queryEndPoint));
        return ($query) ? $query : [];
    }

    /**
     * Protected method to poll and parse the
     * endpoint and decode the JSON response.
     * @param String $url
     * @return Object
     */
    protected function getResponse($url) {
        $response = file_get_contents($url, false);
        return json_decode($response);
    }

    /**
     * Extracting a Post response and set a new
     * object with formatted attributes.
     * @param Object $post
     * @param Boolean $locale - Defaults to false
     * @return Ojbect
     */
    protected function extractPost($post, $locale = false) {
        $ext = (object)[];
        $ext->title = $post->title->rendered;
        $ext->author = $this->getAuthor($post->author);
        $ext->slug = $post->slug;
        $ext->featuredMedia = $this->getFeaturedMedia($post->featured_media);
        $ext->sticky = ($post->sticky) ? true : false;
        $ext->excerpt = $post->excerpt->rendered;
        $ext->content = $post->content->rendered;
        $ext->created_at = $post->date;
        $ext->updated_at = $post->modified;
        $ext->category = $this->getCategory($post->categories[0]);

        // Active Field extra attributes
        $ext->extraAttributes = (!is_null($post->acf)) ? $post->acf : [];

        // Extending the translations
        $ext->translations = ($locale == true) ? $this->getTranslations($post->id) : [];

        return $ext;
    }

    /**
     * Return the translation object for a single
     * Post object.
     * @param Integer $id
     * @return Object
     */
    public function getTranslations($id) {
        $availableLanguages = collect($this->getResponse($this->url . 'lang'));
        $translationsPool = [];
        foreach ($availableLanguages as $language) {
            $postInstance = $this->getResponse($this->url . 'posts?id' . $id . '&lang=' . $language->code);
            $post = [];
            if (!empty($postInstance)) {
                $post = [
                    'id' => $postInstance[0]->id,
                    'slug' => $postInstance[0]->slug,
                    'title' => $postInstance[0]->title->rendered
                ];
            }
            $translationsPool[$language->code] = $post;
        }
        return $translationsPool;
    }

    /**
     * Return the featured media object
     * @param Integer $id
     * @return Object
     */
    protected function getFeaturedMedia($id) {
        $media = collect($this->getResponse($this->url . 'media/' . $id));
        if ($media && isset($media['media_details']) && !is_null($media['media_details']->sizes)) {
            return $media['media_details']->sizes;
        } else {
            return [];
        }
    }

    /**
     * Returns the author name
     * @param Integer $id
     * @return String
     */
    protected function getAuthor($id) {
        $user = collect($this->getResponse($this->url . 'users/' . $id));
        return ($user && $user['name']) ? $user['name'] : 'n/a';
    }

    /**
     * Returns the category name
     * @param Integer $id
     * @return String
     */
    protected function getCategory($id) {
        $category = collect($this->getResponse($this->url . 'categories/' . $id));
        return ($category && $category['name']) ? $category['name'] : 'Uncategorized';
    }

}

