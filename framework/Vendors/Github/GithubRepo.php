<?php

class GithubRepo {

    protected $username;
    protected $repo;
    protected $http;

    function __construct() {
        $this->http = new GithubHttp();
    }

    public function setRepoAccessInfo($username, $repo) {

        $this->username = $username;

        $this->repo = $repo;
    }

    public function getDownloads() {
        $url = urlencode($this->username) . "/" . urlencode($this->repo) . "/downloads";
        return $this->http->get($url);
    }

    public function getTags() {
        $url = urlencode($this->username) . "/" . urlencode($this->repo) . "/tags";
        return $this->http->get($url);
    }

    public function getRepoInfo() {
        $url = urlencode($this->username) . "/" . urlencode($this->repo);
        return $this->http->get($url);
    }

}
