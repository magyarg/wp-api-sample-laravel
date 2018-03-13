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
     * @param Integer $category
     * @return Object $formattedObjects
     */
    public function posts($category = null) {
        if (!is_null($category)) {
            $posts = collect($this->getResponse($this->url . 'posts?categories=' . $category));
        } else {
            $posts = collect($this->getResponse($this->url . 'posts'));
        }
        $formattedObjects = [];
        foreach($posts as $post) {
            $formattedObjects[] = (is_null($category)) ? $this->extractPost($post, null, true) : $this->extractPost($post, $category, true);
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
    protected function extractPost($post, $categoryId = null,  $locale = false) {
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

        $bufferCategoryId = (!isset($post->categories[0]) && !is_null($categoryId)) ? $categoryId : $post->categories[0];
        $ext->category = $this->getCategory($bufferCategoryId);

        // Active Field extra attributes
        $ext->extraAttributes = (!is_null($post->acf)) ? $post->acf : [];

        // Gallery like extract
        $ext->gallery = $this->getGallery($ext->extraAttributes);

        // Extending the translations
        // $ext->translations = ($locale == true) ? $this->getTranslations($post->id) : [];

        return $ext;
    }

    /**
     * Extracts the images into a more human readable
     * format.
     * @param Object $attributes
     * @return Object
     */
    public function getGallery($attributes) {
        $mapAttributes = (!is_null($attributes) && !empty($attributes)) ? $attributes : [];
        $mapDone = false;
        $attributePrefix = 'image_';
        $attributeIndex = 1;
        $galleryContainer = [];

        while (!$mapDone) {
            $currentProperty = $attributePrefix . $attributeIndex;
            if (isset($mapAttributes->$currentProperty) && isset($mapAttributes->$currentProperty->sizes)) {
                $galleryContainer[] = $mapAttributes->$currentProperty->sizes;
                $attributeIndex++;
            } else {
                $mapDone = true;
            }
        }

        return $galleryContainer;
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
        if ($id == 0) {
            $media = [];
            $media['thumbnail']['source_url'] = '';
            $media['medium']['source_url'] = '';
            $media['medium_large']['source_url'] = '';
            $media['large']['source_url'] = '';
            $media['full']['source_url'] = '';

            return json_decode(json_encode($media));
        }
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

