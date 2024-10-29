<?php
class BeforeAfterGalleryLoader {
    protected $actions = array();
    protected $filters = array();
    protected $shortCodes = array();

    public function addAction($hook, $component, $callback) {
        $this->actions = $this->add($this->actions, $hook, $component, $callback);
    }

    public function addFilter($hook, $component, $callback) {
        $this->filters = $this->add($this->filters, $hook, $component, $callback);
    }

    public function addShortCode($shortCode, $component, $callback) {
        $this->shortCodes = $this->add($this->shortCodes, $shortCode, $component, $callback);
    }

    private function add($hooks, $hook, $component, $callback) {
        $hooks[] = array(
            'hook'      => $hook,
            'component' => $component,
            'callback'  => $callback,
        );

        return $hooks;
    }

    public function run() {
        foreach ($this->shortCodes as $hook) {
            add_shortcode($hook['hook'], array($hook['component'], $hook['callback']));
        }

        foreach ($this->filters as $hook) {
            add_filter($hook['hook'], array($hook['component'], $hook['callback']));
        }

        foreach ($this->actions as $hook) {
            add_action($hook['hook'], array($hook['component'], $hook['callback']));
        }
    }
}
