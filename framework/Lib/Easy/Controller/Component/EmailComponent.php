<?

App::import('Vendors', 'phpmailer/class.phpmailer');

class EmailComponent extends Component {

    public function load() {
        return new PHPMailer();
    }

    public function initialize(&$controller) {
        $this->controller = $controller;
    }

}