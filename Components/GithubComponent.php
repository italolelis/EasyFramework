<?

App::import('Vendors', 'Github/GithubAutoloader');

class GithubComponent implements IComponent {

    protected $controller;
    protected $gitClient;

    public function load() {
        return new self;
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

    /**
     * Get the RepoApi Object
     * @return GithubRepoApi 
     */
    public function getRepo() {
        return $this->gitClient->getRepoApi();
    }

}