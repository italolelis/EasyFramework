<?

App::import('Vendors', 'phpmailer/class.phpmailer');

class EmailComponent implements IComponent {

    public function load() {
        return new PHPMailer();
    }

    public function initialize(&$controller) {
        $this->controller = $controller;
    }

    public function shutdown(&$controller) {
        
    }

    public function startup(&$controller) {
        
    }

}