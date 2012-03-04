<?php

/* * ************************************************************************************

  class Thread

  version: 1.0

  by:Alex Lau

  email:alex621@gmail.com

  If you have any questions, feel free to ask me.



  In this class, there are six properties

  var $func;  //The function name that you want to call

  var $arg;	//The arguments you want to pass in

  var $thisFileName; //This file's name

  var $fp;	//File pointer

  var $host;	//Host

  var $port;	//Port

  And there are four methods,

  void Thread(string $host, [int $port = 80]) // constructor

  void setFunc(string $func,array $arg)

  $func is a string of the function name

  $arg is an array of the arguments

  Usage:

  $arg = array ( 2, 3);

  $func = "test";

  The method will call test(2,3).

  void start()				  To start the thread

  mixed getreturn()			  To get the return value from the function that called by setFunc

  void setPort()				  To set the port

  void setHost()				  To set the host



  Since serialize() does not support the resource type, this class cannot be used to pass in or return the resource type.

 * ************************************************************************************ */

class Thread {

    var $func;
    var $arg;
    var $thisFileName;
    var $fp;
    var $host;
    var $port;

    function Thread($host, $port="") {

        $this->host = $host;

        if ($port != "") {

            $this->port = $port;
        } else {

            $this->port = 80;
        }

        $this->thisFileName = $_SERVER["SCRIPT_NAME"];
    }

    function setFunc($func, $arg=false) {

        $i = 0;

        $this->arg = "";

        if ($arg) {

            foreach ($arg as $argument) {

                $this->arg .= "&a[]=" . urlencode(serialize($argument));
            }
        }

        $this->func = $func;
    }

    function setPort($port) {

        $this->port = $port;
    }

    function setHost($host) {

        $this->host = $host;
    }

    function start() {

        $this->fp = fsockopen($this->host, $this->port);

        $header = "GET " . $this->thisFileName . "?threadrun=1&f=" . urlencode($this->func) . $this->arg . " HTTP/1.1\r\n";

        $header .= "Host: " . $this->host . "\r\n";

        $header .= "Connection: Close\r\n\r\n";

        fputs($this->fp, $header);
    }

    function getreturn() {

        $flag = false;

        while (!feof($this->fp)) {

            $buffer = fgets($this->fp, 4096);

            if ($flag) {

                $output .= $buffer;
            }

            if (trim($buffer) == "") {

                $flag = true;
            }
        }

        return unserialize(trim($output));
    }

}

if (isset($_GET['threadrun'])) {

    $arg = array();

    if (isset($_GET['a'])) {

        foreach ($_GET['a'] as $argument) {
            if (get_magic_quotes_gpc() == 1)
                $arg[] = unserialize(stripslashes($argument));
            else
                $arg[] = unserialize($argument);
        }
    }
    $return = call_user_func_array($_GET["f"], $arg);

    echo serialize($return);

    exit;
}
?> 