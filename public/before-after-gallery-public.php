<?php
class BeforeAfterGalleryPublic {
    private $version;
    protected $layouts = array();

    public function __construct($version) {
        $this->version = $version;
    }

    public function enqueueScripts() {
        wp_enqueue_style(
            'twentytwenty-css',
            plugins_url('css/twentytwenty.css', __FILE__),
            array(),
            $this->version
        );

        wp_enqueue_style(
            'before-after-gallery-public-css',
            plugins_url('css/main.css', __FILE__),
            array(),
            $this->version
        );

        wp_enqueue_script(
            'jquery-event-move-js',
            plugins_url('js/jquery.event.move.js', __FILE__),
            array(),
            $this->version,
            true
        );

        wp_enqueue_script(
            'jquery-twentytwenty-js',
            plugins_url('js/jquery.twentytwenty.js', __FILE__),
            array(),
            $this->version,
            true
        );

        wp_enqueue_script(
            'before-after-gallery-public-js',
            plugins_url('js/main.js', __FILE__),
            array(),
            $this->version,
            true
        );
    }

    public function handleShortCode($params) {
        if (!isset($params['id'])) {
            return '';
        }

        // load galleries
        $galleries = get_option('before-after-gallery', array());

        // validate ID
        if (!isset($galleries[$params['id']]) || !count($galleries[$params['id']]['images'])) {
            return '';
        }

        $gallery = $galleries[$params['id']];
        $images = $gallery['images'];

        // sort images by weight
        uasort($images, array($this, 'sortByWeight'));

        // load images data
        $rows = array();
        foreach ($images as $imageID => $image) {
            $rows[] = $this->getHTML('images-row', array(
                '{id}'         => $imageID,
                '{before_url}' => $image['before_url'],
                '{after_url}'  => $image['after_url'],
                '{thumb_url}'  => $image['thumb_url'],
            ));
        }

        // output HTML
        $output = $this->getHTML('images', array(
            '{rows}'            => implode("\n", $rows),
            '{id}'              => $params['id'],
            '{name}'            => $gallery['name'],
            '{thumb}'           => $gallery['thumb_url'],
        ));

        return $output;
    }

    protected function sortByWeight($a, $b) {
        if ($a['sort_id'] == $b['sort_id']) {
            return 0;
        }
        return ($a['sort_id'] > $b['sort_id']) ? -1 : 1;
    }

    protected function getHTML($name, $map) {
        // build file path
        $file = dirname(__FILE__) . '/partials/' . $name . '.phtml';

        // load layout
        $layout = '';
        if (isset($this->layouts[$name])) {
            // load from cache
            $layout = $this->layouts[$name];
        } else if (file_exists($file)) {
            // load from file
            $layout = file_get_contents($file);

            // store in cache
            $this->layouts[$name] = $layout;
        }

        // process values map and return output
        return str_replace(array_keys($map), $map, $layout);
    }
}
