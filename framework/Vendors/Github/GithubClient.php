<?php

class GithubClient {

    protected $repoApi;

    public function __construct() {
        $this->repoApi = new GithubRepo();
    }

    /**
     * Get the repo API
     *
     * @return  Github_Api_Repo  the repo API
     */
    public function getRepoApi() {
        return $this->repoApi;
    }

}