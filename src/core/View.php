<?php

namespace Core;

class View {
    protected $layout = 'main';
    protected $sections = [];
    protected $currentSection = null;
    protected $data = [];

    public function __construct() {
        // Initialize with default data
        $this->data = [
            'title' => 'LMS Recovery Analytics',
            'content' => ''
        ];
    }

    public function render($view, $data = []) {
        // Merge passed data with default data
        $this->data = array_merge($this->data, $data);
        
        // Extract data to make variables available in view
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        // Include the view file
        $viewFile = BASEPATH . "/src/views/{$view}.php";
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$view}");
        }
        
        include $viewFile;
        $this->data['content'] = ob_get_clean();
        
        // If using layout
        if ($this->layout) {
            $layoutFile = BASEPATH . "/src/views/layouts/{$this->layout}.php";
            if (!file_exists($layoutFile)) {
                throw new \Exception("Layout file not found: {$this->layout}");
            }
            
            // Extract data again for layout
            extract($this->data);
            
            ob_start();
            include $layoutFile;
            return ob_get_clean();
        }
        
        return $this->data['content'];
    }

    public function setLayout($layout) {
        $this->layout = $layout;
        return $this;
    }

    public function getLayout() {
        return $this->layout;
    }

    public function section($name) {
        $this->currentSection = $name;
        ob_start();
    }

    public function endSection() {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }

    public function getSection($name) {
        return $this->sections[$name] ?? '';
    }

    public function extend($layout) {
        $this->layout = $layout;
    }
}
