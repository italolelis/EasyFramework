<?

class YoutubeComponent implements IComponent {

    public $url;
    public $id;
    protected $playerVersion = '3';
    protected $controller;

    public function load($url = null) {
        $instance = new self;
        if (!is_null($url)) {
            $instance->url = $url;
            $instance->id = $this->url2id($url);
        }
        return $instance;
    }

    public function getPlayerVersion() {
        return $this->playerVersion;
    }

    public function setPlayerVersion($playerVersion) {
        $this->playerVersion = $playerVersion;
    }

    public function getController() {
        return $this->controller;
    }

    /**
     * Creates an array of videos objects
     * @param array $youtubeUrls The youtube videos urls
     * @param array $options The options like video's size
     * @return array 
     */
    public function createVideoList(Array $youtubeUrls, Array $options = array()) {
        $videos = array();
        foreach ($youtubeUrls as $url) {
            $videos[] = $this->getVideoInfo($url, $options);
        }

        return $videos;
    }

    /**
     * Get an Youtube Video Object
     * @param string $url The video url
     * @param type $options The options like video's size
     * @return \stdClass 
     */
    public function getVideoInfo($url, $options = array()) {
        $video = $this->load($url, $options);

        $videoClass = new Youtube();

        if (!empty($options)) {
            $videoClass->setPlayer($video->player($options['width'], $options['height']));
        }
        $videoClass->setEmbed("http://www.youtube.com/embed/{$video->id}?autoplay=1");
        //getting the thumbnail
        $videoClass->setThumb($video->thumb_url("maior"));
        //seting the video url
        $videoClass->setUrl($video->url);
        //getting some info from the video
        $info = $video->info();
        //getting the video's title
        $videoClass->setTitle(isset($info['title']) ? (string) $info['title'] : null);
        $videoClass->setTime(isset($info['time']) ? (string) $info['time'] : null);
        $videoClass->setDescription(isset($info['description']) ? (string) $info['description'] : null);
        $videoClass->setViews(isset($info['views']) ? (string) $info['views'] : null);

        return $videoClass;
    }

    public function url2id($url = null) {
        $url = is_null($url) ? $this->url : $url;
        parse_str(parse_url($url, PHP_URL_QUERY), $params);
        $id = $params['v'];
        return $id;
    }

    public function thumb_url($tamanho = null) {
        $tamanho = $tamanho == "maior" ? "hq" : "";
        return 'http://i1.ytimg.com/vi/' . $this->id . '/' . $tamanho . 'default.jpg';
    }

    public function thumb($tamanho = null) {
        $tamanho = $tamanho == "maior" ? "hq" : "";
        return '<img src="http://i1.ytimg.com/vi/' . $this->id . '/' . $tamanho . 'default.jpg">';
    }

    public function player($width, $height) {
        return '<object width="' . $width . '" height="' . $height . '">
                <param name="movie" value="http://www.youtube.com/v/' . $this->id . '?version=' . $this->getPlayerVersion() . '&fs=1"></param>
                <param name="allowFullScreen" value="true"></param>
                <param name="allowscriptaccess" value="always"></param>
                <embed src="http://www.youtube.com/v/' . $this->id . '?version=' . $this->getPlayerVersion() . '&fs=1" 
                    type="application/x-shockwave-flash" 
                    allowscriptaccess="always" 
                    allowfullscreen="true" 
                    width="' . $width . '" 
                    height="' . $height . '"></embed>
                </object>';
    }

    /**
     * Get the video's information
     * @return array The information array of the video
     * @throws HttpException 
     */
    public function info() {
        $feedURL = "http://gdata.youtube.com/feeds/api/videos?q={$this->id}&client=ytapi-youtube-search&v=2.1";
        $resultado = $this->getFeed($feedURL);

        $xml = new SimpleXMLElement($resultado);
        return $this->getInfoArray($xml);
    }

    private function getInfoArray(SimpleXMLElement $xml) {
        $info = array();

        foreach ($xml->entry as $entry) {
            $info["title"] = $entry->title;
            //Get the children nodes
            $media = $entry->children('media', true)->group;
            $yt = $media->children('yt', true);
            //Get the description
            $info["description"] = $media->description;
            //Get the video duration
            $attrs = $yt->duration->attributes();
            $info["time"] = strftime('%H:%m', (string) $attrs['seconds']);
            //Get the video views
            $attrs = $entry->children('yt', true)->statistics->attributes();
            $info["views"] = $attrs['viewCount'];
        }
        return $info;
    }

    private function getFeed($feed) {
        $cURL = curl_init($feed);
        curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, true);
        $resultado = curl_exec($cURL);
        curl_close($cURL);
        return $resultado;
    }

    public function initialize(&$controller) {
        $this->controller = $controller;
    }

    public function shutdown(&$controller) {
        
    }

    public function startup(&$controller) {
        
    }

}

class Youtube {

    private $player;
    private $embed;
    private $thumb;
    private $url;
    private $title;
    private $time;
    private $description;
    private $views;

    public function getPlayer() {
        return $this->player;
    }

    public function getThumb() {
        return $this->thumb;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getTime() {
        return $this->time;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getViews() {
        return $this->views;
    }

    public function setPlayer($player) {
        $this->player = $player;
    }

    public function setThumb($thumb) {
        $this->thumb = $thumb;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setTime($time) {
        $this->time = $time;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setViews($views) {
        $this->views = $views;
    }

    public function getEmbed() {
        return $this->embed;
    }

    public function setEmbed($embed) {
        $this->embed = $embed;
    }

}