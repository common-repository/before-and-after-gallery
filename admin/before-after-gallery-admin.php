<?php
class BeforeAfterGalleryAdmin {
    private $version;
    protected $layouts = array();

    public function __construct($version) {
        $this->version = $version;
    }

    public function enqueueScripts() {
        wp_enqueue_style('thickbox');

        wp_enqueue_style(
            'before-after-gallery-admin-css',
            plugins_url('css/main.css', __FILE__),
            array(),
            $this->version
        );

        wp_enqueue_script('media-upload');
        wp_enqueue_script('thickbox');

        wp_enqueue_script(
            'before-after-gallery-media-uploader-functions-js',
            plugins_url('js/media.uploader.functions.js', __FILE__),
            array(),
            $this->version,
            true
        );

        wp_enqueue_script(
            'before-after-gallery-admin-js',
            plugins_url('js/main.js', __FILE__),
            array(),
            $this->version,
            true
        );
    }

    public function adminMenu() {
        add_options_page('Before and After Gallery', 'Before and After Gallery', 'manage_options', 'before-after-gallery', array($this, 'showSettingsPage'));
    }

    public function adminInit() {
        register_setting('before-after-gallery-options', 'before-after-gallery');

        if (!isset($_GET['page']) || $_GET['page'] !== 'before-after-gallery') {
            return;
        }

        // handle params
        $action = !empty($_GET['action']) ? $_GET['action'] : '';
        $galleryID = isset($_GET['gallery_id']) ? $_GET['gallery_id'] : '';
        $imageID = isset($_GET['image_id']) ? $_GET['image_id'] : '';

        // load settings
        $galleries = get_option('before-after-gallery');

        // initial cleanup
        if (!is_array($galleries)) {
            $galleries = array();
            update_option('before-after-gallery', $galleries);
        }

        // route actions
        switch ($action) {
            case 'save_gallery':
                // get gallery
                if ($galleryID !== '' && isset($galleries[$galleryID])) {
                    $gallery = $galleries[$galleryID];
                } else {
                    $gallery = array(
                        'name' => '',
                        'images' => array(),
                    );
                }

                // update values
                $gallery['name']            = $_POST['name'];
                $gallery['thumb_url']       = $_POST['thumb_url'];

                // update settings
                if ($galleryID !== '') {
                    $galleries[$galleryID] = $gallery;
                } else {
                    $galleries[] = $gallery;

                    // get ID
                    end($galleries);
                    $galleryID = key($galleries);
                }

                // save changes
                update_option('before-after-gallery', $galleries);

                // redirect to edit page
                wp_redirect('?page=before-after-gallery&action=edit_gallery&gallery_id=' . $galleryID);
                die();

                break;

            case 'delete_gallery':
                unset($galleries[$galleryID]);

                // save changes
                update_option('before-after-gallery', $galleries);

                wp_redirect('?page=before-after-gallery');
                die();

                break;

            case 'save_image':
                if (!isset($galleries[$galleryID])) {
                    wp_redirect('?page=before-after-gallery');
                    die();
                }

                // get image
                if ($imageID !== '' && isset($galleries[$galleryID]['images'][$imageID])) {
                    $image = $galleries[$galleryID]['images'][$imageID];
                } else {
                    $image = array(
                        'before_url' => '',
                        'thumb_url'  => '',
                        'after_url'  => '',
                        'sort_id'    => 0,
                    );
                }

                // update values
                $image['before_url'] = $_POST['before_url'];
                $image['thumb_url']  = $_POST['thumb_url'];
                $image['after_url']  = $_POST['after_url'];
                $image['sort_id']    = $_POST['sort_id'];

                // update settings
                if ($imageID !== '') {
                    $galleries[$galleryID]['images'][$imageID] = $image;
                } else {
                    $galleries[$galleryID]['images'][] = $image;

                    // get ID
                    end($galleries[$galleryID]['images']);
                    $imageID = key($galleries[$galleryID]['images']);
                }

                // save changes
                update_option('before-after-gallery', $galleries);

                // redirect to edit page
                wp_redirect('?page=before-after-gallery&action=edit_image&gallery_id=' . $galleryID . '&image_id=' . $imageID);
                die();

                break;

            case 'delete_image':
                unset($galleries[$galleryID]['images'][$imageID]);

                // save changes
                update_option('before-after-gallery', $galleries);

                wp_redirect('?page=before-after-gallery&action=edit_gallery&gallery_id=' . $galleryID);
                die();

                break;

            default:
                break;
        }
    }

    public function showSettingsPage() {
        // handle params
        $action = !empty($_GET['action']) ? $_GET['action'] : '';
        $galleryID = isset($_GET['gallery_id']) ? $_GET['gallery_id'] : '';
        $imageID = isset($_GET['image_id']) ? $_GET['image_id'] : '';
        $output = '';

        // load settings
        $galleries = get_option('before-after-gallery', array());

        // route actions
        switch ($action) {
            case 'edit_gallery':
                $gallery = array(
                    'name' => '',
                    'images' => array(),
                );

                // load gallery data (if editing)
                if ($galleryID !== '' && isset($galleries[$galleryID])) {
                    $gallery = $galleries[$galleryID];
                }

                // sort images by weight
                uasort($gallery['images'], array($this, 'sortByWeight'));

                // build image rows
                $rows = array();
                foreach ($gallery['images'] as $imageID => $image) {
                    $rows[] = $this->getHTML('images-row', array(
                        '{id}'        => $galleryID,
                        '{image_id}'  => $imageID,
                        '{thumb_url}' => $image['thumb_url'],
                        '{sort_id}'   => $image['sort_id'],
                    ));
                }

                // output HTML
                $output = $this->getHTML('images', array(
                    '{id}'        => $galleryID,
                    '{name}'      => $gallery['name'],
                    '{thumb_url}' => $gallery['thumb_url'],
                    '{rows}'      => implode("\n", $rows),
                ));

                break;

            case 'edit_image':
                // load gallery data (if editing)
                if ($galleryID !== '' && isset($galleries[$galleryID])) {
                    $gallery = $galleries[$galleryID];
                } else {
                    wp_redirect('?page=before-after-gallery');
                    die();
                }

                $image = array(
                    'before_url' => '',
                    'thumb_url'  => '',
                    'after_url'  => '',
                    'sort_id'    => 0,
                );

                // load image data (if editing)
                if ($imageID !== '' && isset($gallery['images'][$imageID])) {
                    $image = $gallery['images'][$imageID];
                }

                // output HTML
                $output = $this->getHTML('image_details', array(
                    '{id}'         => $galleryID,
                    '{image_id}'   => $imageID,
                    '{before_url}' => $image['before_url'],
                    '{thumb_url}'  => $image['thumb_url'],
                    '{after_url}'  => $image['after_url'],
                    '{sort_id}'    => $image['sort_id'],
                ));

                break;

            default:
                // build output
                $rows = array();
                foreach ($galleries as $galleryID => $gallery) {
                    $rows[] = $this->getHTML('galleries-row', array(
                        '{id}'        => $galleryID,
                        '{name}'      => $gallery['name'],
                        '{thumb_url}' => $gallery['thumb_url'],
                    ));
                }

                // output HTML
                $output = $this->getHTML('galleries', array(
                    '{rows}' => implode("\n", $rows),
                ));

                break;
        }

        echo $output;
    }

    public function addMetaBox() {
        add_meta_box(
            'before-after-gallery-admin',
            'Before and After Gallery Manager',
            array($this, 'renderMetaBox'),
            'post',
            'normal',
            'core'
        );
    }

    public function renderMetaBox() {
        require_once dirname(__FILE__) . '/partials/manager.php';
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
