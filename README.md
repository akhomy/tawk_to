CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Requirements
 * Installation
 * Configuration
 * Maintainers

INTRODUCTION
---------------------

Implementation of tawk.to live chat for Drupal 8.
With using this module you can select widget, that will appear on specific
site pages.

REQUIREMENTS
---------------------

- Account on [tawk.to](tawk.to) service

INSTALLATION
---------------------

Download and enable as a normal module.

CONFIGURATION
-------------

* Go to the module settings page `/admin/config/services/tawk_to/widget`,
login into tawk.to with credentials.
* Select widget and widget settings.
* Go to the module extra settings page
`/admin/config/services/tawk_to/exta_settings`, set up if necessary visibility
settings.
* Check that widget is loaded on some site pages.

FAQ
-------------
1.  Question: How to set up own visibility rules?
    Answer: You can implement own condition plugins and configure them in the /admin/config/services/tawk\_to/exta\_settings. See [https://www.drupal.org/project/tawk\_to/issues/3225179#comment-14303926](https://www.drupal.org/project/tawk_to/issues/3225179#comment-14303926)
2.  Question: How to configure CORS for the module?
    Answer:
    The CORS configuration isn't a part of this module. However, I think, some documentation about this could help.

    So, to fix CORS issues you should implement both server and drupal sides:

    *   Enable 'Access-Control-Allow-Origin' on the server, please, check the example documentation for the NGINX - [https://ubiq.co/tech-blog/enable-cors-nginx](https://ubiq.co/tech-blog/enable-cors-nginx)
    *   Configure CORS settings for the Drupal. It could be in you custom services.yml, please, check [https://www.drupal.org/node/2715637](https://www.drupal.org/node/2715637) or by installing and configuring CORS\_UI module - [https://www.drupal.org/project/cors\_ui](https://www.drupal.org/project/cors_ui)

    See [https://www.drupal.org/project/tawk\_to/issues/3222887#comment-14303943](https://www.drupal.org/project/tawk_to/issues/3222887#comment-14303943)

MAINTAINERS
-----------

* Andriy Khomych(andriy.khomych) - https://www.drupal.org/u/andriy-khomych
