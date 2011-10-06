<?php

/**
 * Classe de ajuda ao HTML
 *
 * @author Italo
 */
class HtmlHelper {

    /**
     * Cria a tag de link "<a></a>"
     * @param string $href
     * @param array $link_attr
     * @return string 
     */
    public static function link($href, $link_attr = array()) {

        if (is_array($link_attr)) {
            $target = isset($link_attr['target']) ? $link_attr['target'] : '_self';
            $text = isset($link_attr['text']) ? $link_attr['text'] : null;
            $title = isset($link_attr['title']) ? $link_attr['title'] : null;
            $link_css = isset($link_attr['css']) ? $link_attr['css'] : null;

            if (is_array($link_css)) {
                $link = "<a href='$href' target='$target' class='" . implode(' ', $link_css) . "' title='$title'>$text</a>";
            } else {
                $link = "<a href='$href' target='$target'  class='$link_css' title='$title'>$text</a>";
            }
        } else {
            $link = "<a href='$href'><a>";
        }
        return $link;
    }

    /**
     * Cria a tag de imagem "<img/>"
     * @param string $src
     * @param array $img_attr
     * @return string 
     */
    public static function image($src, $img_attr = null) {
        if (is_array($img_attr)) {
            $img_css = isset($img_attr['css']) ? $img_attr['css'] : null;

            if (is_array($img_css))
                $link = "<img src='" . Mapper::getWebrootPath($src) . "' class='" . implode(' ', $img_css) . "'/>";
            else
                $link = "<img src='" . Mapper::getWebrootPath($src) . "' class='" . $img_css . "'/>";
        } else {
            $link = "<img src='" . Mapper::getWebrootPath($src) . "'/>";
        }
        return $link;
    }

    /**
     * Cria a tag de imagem com o link "<a><img/></a>"
     * @param string $src
     * @param string $href
     * @param array $link_attr
     * @param array $img_attr
     * @return string 
     */
    public static function imagelink($src, $href, $link_attr = null, $img_attr = null) {
        if (is_array($link_attr))
            $link_attr = array_merge(array('text' => self::image($src, $img_attr)), $link_attr);
        else
            $link_attr = array('text' => self::image($src, $img_attr));

        return self::link($href, $link_attr);
    }

    /**
     * Cria a tag de stylesheets (css) "<link rel='stylesheet' />"
     * @param array $url
     * @param string $directory
     * @return string 
     */
    public static function stylesheet($url = array(), $directory = null) {
        if ($directory === null)
            $directory = 'css/';

        if (is_array($url)) {
            $link = "";
            foreach ($url as $tag) {
                $link .= "<link href='" . Mapper::getWebrootPath($directory . $tag) . "'rel='stylesheet' type='text/css'/>";
            }
        } else {
            $link = "<link href='" . Mapper::getWebrootPath($directory . $url) . "'rel='stylesheet' type='text/css'/>";
        }
        return $link;
    }

    /**
     * Cria a tag de script "<script></script>"
     * @param array $url
     * @param string $directory
     * @return string 
     */
    public static function script($url = array(), $directory = null) {
        if ($directory === null)
            $directory = 'js/';

        if (is_array($url)) {
            $link = "";
            foreach ($url as $tag) {
                $link .= "<script type='text/javascript' src='" . Mapper::getWebrootPath($directory . $tag) . "'></script>";
            }
        } else {
            $link = "<script type='text/javascript' src='" . Mapper::getWebrootPath($directory . $url) . "'></script>";
        }
        return $link;
    }

    /**
     * Cria a tag do favicon "<link type='image/gif' rel='shortcut icon'/>"
     * @param string $url
     * @return string 
     */
    public static function favicon($url) {
        $link = "<link type='image/gif' rel='shortcut icon' href='" . Mapper::getWebrootPath($url) . "' />";
        return $link;
    }

}

?>
