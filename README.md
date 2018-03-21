## Laravel Debugbar
[![Packagist License](https://poser.pugx.org/barryvdh/laravel-debugbar/license.png)](http://choosealicense.com/licenses/mit/)
[![Latest Stable Version](https://poser.pugx.org/barryvdh/laravel-debugbar/version.png)](https://packagist.org/packages/barryvdh/laravel-debugbar)
[![Total Downloads](https://poser.pugx.org/barryvdh/laravel-debugbar/d/total.png)](https://packagist.org/packages/barryvdh/laravel-debugbar)

### Note for v3: Debugbar is now enabled by requiring the package, but still needs APP_DEBUG=true by default!

### For Laravel < 5.5, please use the [2.4 branch](https://github.com/barryvdh/laravel-debugbar/tree/2.4)!

This is a package to integrate [PHP Debug Bar](http://phpdebugbar.com/) with Laravel 5.
It includes a ServiceProvider to register the debugbar and attach it to the output. You can publish assets and configure it through Laravel.
It bootstraps some Collectors to work with Laravel and implements a couple custom DataCollectors, specific for Laravel.
It is configured to display Redirects and (jQuery) Ajax Requests. (Shown in a dropdown)
Read [the documentation](http://phpdebugbar.com/docs/) for more configuration options.

![Screenshot](https://cloud.githubusercontent.com/assets/973269/4270452/740c8c8c-3ccb-11e4-8d9a-5a9e64f19351.png)




<h1>Ministry Platform API Wrapper</h1>
<p>A PHP wrapper to access the Ministry Platform (MP) REST API.  This version is updated to
include the new changes to oAuth authentication rolling out in early 2018. Note that using the API inplies a knowledge of the 
MP data model. The API gives you access to each table in the database. It is up to you to pull the right data (or POST the right data) 
to the tables and then make any connections.</p>

<p>For example, you could create groups using the API and then you could create Participants using 
the API. After that is done, you can create Group Participants to add your participants to a group. Because Group 
Participants depends on the IDs of the group and the participant, the order in which you add the data is important.</p>

<p>This new version is available as a Composer Package...  More details on that as I figure out the process to include
the package and make use of it in my code.</p>

