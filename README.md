Easy Framework
=======

[![EasyFramework](http://easyframework.net/images/logo.png)](http://www.easyframework.net)

EasyFramework is a PHP framework for small to medium web applications. It's as simple and concise as possible, trying to stand out of your way.

This repository is for EasyFramework developers. All users should download EasyFramework from our [official site](http://www.easyframework.net).

Instalation
----------------
Go to [tags](https://github.com/LellysInformatica/EasyFramework/tags/) section to download the stable version of the framework.

Rename the folder to _easyframework_ and put into your web server.

Now you can access http://localhost/easyframework/requirements to see if your web server support EasyFW.

If everything went ok you can [start developing](http://easyframework.net/docs/1.x) your apps.

Creating Apps
----------------
You can put your apps outside the easyframework folder and point to it. To do this create your app and then go to 
_App/webroot/index.php_ chage the depp of yout root folder around line 31.

To use apps outside the easyframework folder use this:

`$easy = "../../../easyframework/framework/Easy/bootstrap.php"`

To use apps inside the easyframework folder use:

`$easy = "../../easyframework/framework/Easy/bootstrap.php"`

How to help
----------------
1. Find and [report bugs](https://github.com/LellysInformatica/EasyFramework/issues) and help us fix them
2. Fork our repository and start writing some code! Take a look at the [Roadmap](https://github.com/LellysInformatica/EasyFramework/wiki/Roadmap) to see where we're going.
3. Help us enhance our documentation

See [our wiki](https://github.com/LellysInformatica/EasyFramework/wiki/) for more information on development.
