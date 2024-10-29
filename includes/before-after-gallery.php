<?php
class BeforeAfterGallery {
    protected $loader;
    protected $version;

    public function __construct() {
        $this->version = '1.0.0';

        $this->loadDependencies();
        $this->definePublicHooks();
        $this->defineAdminHooks();

        $this->loader->run();
    }

    protected function loadDependencies() {
        require_once dirname(dirname(__FILE__)) . '/admin/before-after-gallery-admin.php';
        require_once dirname(dirname(__FILE__)) . '/public/before-after-gallery-public.php';

        require_once dirname(__FILE__) . '/before-after-gallery-loader.php';
        $this->loader = new BeforeAfterGalleryLoader();
    }

    protected function definePublicHooks() {
        $public = new BeforeAfterGalleryPublic($this->version);
        $this->loader->addAction('wp_enqueue_scripts', $public, 'enqueueScripts');
        $this->loader->addAction('wp_enqueue_scripts', $public, 'enqueueScripts');
        $this->loader->addShortCode('before-after-gallery', $public, 'handleShortCode');
    }

    protected function defineAdminHooks() {
        $admin = new BeforeAfterGalleryAdmin($this->version);
        $this->loader->addAction('admin_enqueue_scripts', $admin, 'enqueueScripts');


        $this->loader->addAction('admin_menu', $admin, 'adminMenu');
        $this->loader->addAction('admin_init', $admin, 'adminInit');

        //$this->loader->addAction('add_meta_boxes', $admin, 'addMetaBox');
    }
}
