<?

App::import('Vendors', 'phpmailer/class.phpmailer');

class EmailComponent extends Component {

    public function load() {
        return new PHPMailer();
    }

    public function initialize(&$controller) {
        $this->controller = $controller;
    }

    public function renderViewBody($action, $controller = true, $layout = false) {
        if ($controller === true) {
            $controller = $this->controller->getName();
        }
        $view = new View($this->controller);
        //Pass the view vars to view class
        foreach ($this->controller->viewVars as $key => $value) {
            $view->set($key, $value);
        }
        return $view->display("{$controller}/{$action}", $layout);
    }

}