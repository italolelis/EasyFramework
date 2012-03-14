<?

App::import('Vendors', 'Github/GithubAutoloader');

class GithubComponent implements IComponent {

    protected $controller;
    protected $gitClient;

    public function load() {
        $instance = new self;
        return $instance;
    }

    public function initialize(&$controller) {
        $this->controller = $controller;

        GithubAutoloader::register();
        $this->gitClient = new GithubClient();
    }

    public function shutdown(&$controller) {
        
    }

    public function startup(&$controller) {
        
    }

    public function getRepo() {
        return $this->gitClient->getRepoApi();
    }

}