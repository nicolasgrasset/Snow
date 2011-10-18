Is it any good? Yes.


Introduction to Snow
=============

2011-10-18 - version 0.9.1 with the introduction of Snow::app()

2010-12-10 - Initial version


Introduction
------------

This document is an introduction documentation to Snow, a PHP framework originally developed and maintained by RIVER.


What is Snow?
-------------

At heart, Snow is a lightweight PHP framework for the application layer of web services, platforms and websites.  It was first created and named in March 2010, although its architecture was inspired from previous projects built over a period of ten years. Snow is more defined by its concept than its code.

Simple, clean and clear
-----------------------

Snow core framework shall be documentable within two A4 pages. There are many PHP framework available, all require some learning about structure and configuration: Snow should be the easiest to start working with. Any PHP developer should be able to become productive with Snow after an hour of introduction, not days.

While the code should be optimized and scalable, the architecture should be clear. The assumption is that an obscure compressed configuration would not gain enough performance to be meaningful, but a clear structure would make support and development more efficient.

Lightweight, scalable
---------------------

Snow shall fit any PHP application. It is therefor important never to have a deployment too heavy or too complex for any project, but the structure should also be the most secure and reliable possible. Snow is loading its component dynamically so that an empty script virtually has no overload to run a specific script, but complex applications should still load all necessary libraries as fast as they would if built without Snow.

Designed to power the application layer of web services, Snow can be deployed on multiple servers or virtual servers and interact with external data stores or databases.

Reusable and extendable
-----------------------

Snow is often used to solve common web development problems such as database interaction, inter-connections with Facebook or Twitter, serving web pages and REST/SOAP API, etc. It is therefore important for developers to be able to reuse common libraries, public SDK or even rely on other framework such as Zend or Symphony.

Applications are built in modules which can be re-used and updated, and are therefor backward compatible. Snow core, by comparison, is more rarely updated and focuses on delivering a code relevant to 90% of every Snow project deployments.

Structure
---------

Snow has very little structure to let teams of developers organize their deployments as they see fit while following core concepts.

Invocation
----------

Snow file structure is fairly simple. Snow core can, by default, be loaded from Apache or in a Command Line Interface. Apache relies on a .htaccess file redirecting all traffic to a single index.php file loading the engine, CLI scripts can load the engine with a six lines of code.

Snow Context
------------

Snow solves one of the main PHP language omissions, context, using a single global object to solve all environment and configuration execution store. The context is created and destroyed within a single script execution and will typically contain a database access object, a user object and will know the current web server address and local directory structure.

Some common feature have been added for websites to handle web page titles or headers. However, Snow never binds to one solution, so logging, localization and databases are all supported by external classes.

Configuration
-------------

All configuration is done centrally in the file "config.inc.php" which is loaded at startup. It can contain logic and will be run from the Snow Context scope. It uses a key/value format where values can be any PHP value.

Definition in /config.inc.php is done with the method define where the first argument is the key and the second is the value to be stored against it:
 
    $this->define( 'site', 'river');

Using configuration values anywhere in the code can be done using getConfig where the first argument is the key and the second is the default value:

    Snow::app()->getConfig( 'site', null);

Models and application
----------------------
All the application code shall be stored under /plugins and will be loaded automatically when used thanks to a naming convention on classes. Code should always be part of a plugin directory (ex. facebook) and class names should be formed as follow:
 
Class	Directory	Filename
snow_pluginName_fileName	/plugins/pluginName	fileName.php
snow_facebook_client	/plugins/facebook	client.php
snow_facebook	/plugins/facebook	facebook.php
snow_facebook_clients_soap	/plugins/facebook/clients	soap.php

Note: when a file name is the same as the plugin name, then the class name does not need to repeat that name twice as in "*snow_facebook*".

Files should contain only one class and optimally no script to be fully object oriented. It is common practice to create models of database or datastore objects as Data Access Objects.

When built, plugins should either be project specific or generic. A Facebook plugin must not contain any project specific logic in any of its files. Instead a project plugin should be created to extend facebook classes if necessary.

Finally, any update to any generic plugin should always be backward compatible which implies a comprehensive modification of parameter using default values and maintaining the order, not disabling any interface methods, etc.

Sites
-----

Websites and static content should always be located under /sites. In the config, a specific site should be specified corresponding to the name of a folder under /sites.

While the sites subfolder follow nearly no requirement, a few files will be expected by Snow for most site:
 * /sites/YOUR_SITE/init.php
 * /sites/YOUR_SITE/inc/header.php
 * /sites/YOUR_SITE/inc/footer.php

The most important one is init.php. Its content will be run just after loading Snow and before any other logic for all script. This is typically where website will have the database logic and user management logic. It should only contain content relevant to all web page, interface or script.

The two other files are expected for all websites and contain the header and footer logic (PHP, HTML, ...) for every web pages. Note that these two files will always be loaded after the execution of the page itself.

Controllers
-----------

Web services and applications will use URL controllers which are each corresponding to a file placed under /content. Files placed directly under /content are web pages and will be placed within header and footer as previously described. Directories can also be created and need to be defined in the config file as static directories or in the site init file as smart directories. In both case, the content type can be set to anything by the controller itself.

URL	Type	File
http://localhost/	web page	/content/index.php
http://localhost/contact	web page	/content/contact.php
http://localhost/contact/stockholm	web page	/content/contact.php
http://localhost/rss/news.xml	RSS Feed	/content/rss/news.xml.php
http://localhost/api/submit	REST API	/content/api/submit.php

When accessing a controller based on a sub-folder such as http://localhost/contact/stockholm, where contact is not a special directory,  the controller will be able to use the path to serve specific content; in this case related to /stockholm. To do so, the PHP code can call the Snow Context getContent method with the index of the path starting at 0 for the script name, and an optional default value in case the path is shorter than assumed:
 
    $city = Snow::app()->getContent( 1 ); 
    $area = Snow::app()->getContent( 2, "central" );

Controller can technically output any type of content directly, although web pages will expect to match content encoding and format set by the header. Also, it is common practice to rely on templates (see below) to output content.

Additional common features
--------------------------

Additional features have been developed for Snow with a set of default handling classes that are made to be replaced if necessary depending on the project.

Templates
---------

Following the MVC architecture, templates where added to separated views from the controllers and be reusable. With the default implementation, templates are placed in the folder /templates in PHP files and loaded from anywhere in the code using the file name without the php extension as first parameter and an optional second parameter to pass additional values named $content_id from the template scope: 

    Snow::app()->loadTemplate( "user_list", array( 1, 4, 5) );

Localization
------------

A first implementation of localization has been implemented using Unix/PHP gettext with .po/.mo files. Translation files are stored in the folder /locales, the current locale is known by Snow Context and can be implicitly or explicitly set by the query path (ex: www.mysite.com/fr), the domain name (ex: www.mysite.de), using browser preferences, etc.

When relying on Snow localization, text output should be done using PHP double underscore function:

    __( "Hello world!" );

More details are provided in a separate document.

Logging
-------

All plugins and controller should rely on logging for debugging, quality assurance and monitoring. The default implementation of logging relies on the default PHP logging function and can be configured to write in any file on the system.

Logging instruction should be made with a log level from 1 to 5 (Information, Debug, Information, Error, Critical). On different environments, logging level can be capped to only register events beyond a certain level at a global level or a plugin level.

To log an event, developers can rely on Snow Context anywhere in the code using the log method with a message string and a log level integer:

    Snow::app()->log( "Could not start server", 5 );
	

