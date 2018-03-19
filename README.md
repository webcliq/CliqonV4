# CliqonV4 #

**Main production repository for Cliqon Version 4**

This holds the uninstalled production system for download to create a new application or website.
## Quick Install Notes ##
Quick Install Notes Download and uncompress or clone this repository. Copy to the hosting directory of your choice.

Browse to the domain or subdomain that contains the instance of Cliqon 4. You will be presented with the opening page of the Install Wizard. Follow the instructions on the Wizard to install Cliqon.

If you need to undertake a manual install, visit it the /docs sub-directory, where you will find the current version of the manual in PDF form.
## About Cliqon ##
Cliqon is not just another content management system or blogging system, it already supports these functions and facilities but it is much more than that. It is designed to be used by developers to make their work flow faster, easier and more productive. It does not use pre-structured themes and templates, standard HTML pages are made dynamic and multilingual using the embedded Razr server-side templating system or client-side templates based on the Mustache system and Vue JS.

This approach to website design makes the system highly extendable and flexible. It allows the developer to work with any design that a graphic designer can consider, with paid and downloaded themes and templates. With Cliqon you can work with tools such as Pingendo or the embedded Grapes JS.
## This Version ##
Version 4 of Cliqon uses the latest, stable and most popular technologies. The client-side code is mostly written with Vue JS. However some popular and widely respected Javascript components are used for tables, trees, calendars and galleries. The administrative system leverages the power and flexibility of Bootstrap 4 via the popular CoreUI administrative template. The server-side code is written in PHP scripting that is compatible from PHP 5.6 up to PHP 7.2. It has been tried and tested on Linux+Apache, Windows+Apache and Windows+IIS; it works with all of them. The little tweaks that are needed are fully documented.

***MVC and Object Orientated***

The framework follows the Model-View-Controller (MVC) pattern. The vast majority of the client and server side coding belong to Methods within Classes. In that sense Cliqon can be described as Object Orientated Programming (OOP). However, as the primary purpose of the framework is to provide a development environment for PHP and Javascript programmers of many levels, it does not include patterns and methodologies that some might find intimidating and obscure. The emphasis is on creating an intuitive system that many can use and extend.

***Caching***

Wherever possible, all database and file activities are cached so that it takes advantage of server based caching systems and is very fast. Javascript files are aggregated into minified libraries and loaded in to browser local storage for reuse.

***Database***

For many years, Cliqon has used client-server databases such as MySQL, Postgres and MSSQL. It does this through the use of the PHP PDO driver environment and the ORM environment from RedbeanPHP. However, in the latest version, Cliqon has introduced one element from the NoSQL database environment that many developers find attractive. This database pattern can be loosely described as a document database or an enhanced entity-attribute-value (EAV) model. It is a hybrid pattern where tables are defined by what they store, not what they do. Columns in the table are divided into static variable character columns for items such as type, category and order, that are frequently indexed and one column that contains virtually everything else in JSON format, including multilingual text, images and configuration information in the TOML format. Webcliq sincerely believe that this EAV hybrid model leverages the best from both infrastructures – client-server and NoSQL – and also provides for scalability into multiple server models. As a result of the projects that Webcliq has undertaken with Cliqon in the area of classified advertising and directory, Cliqon has always encompassed the concept of multiple levels of access by users to common elements of the administrative system. The Access Control Levels system in Version 4 has been enhanced to provide control at a very detailed level throughout the system. Not only can Users be precluded from seeing menu entries or having access to Classes and Methods but individual fields on a form and columns on a report can be excluded for certain users.

***Administration system***

Cliqon contains a comprehensive administration system that can be extended via system of modules and plugins to undertake virtually any application task that can be envisaged. Administration contains a configurable menu system, support for a wide variety of tables, trees and lists to display data, data processing forms for the management of infrastructure, templates, layout and user content. Reports can be configured with the report generator. The utilities section includes functions to import and export data, create site maps and upgrade the system with updates from the central server. Updates to the code are controlled by the developer and are not completely automated to allow for changes to the code that have been made by developers. This version continues to support the reading and writing of data via views, reports and data entry forms entirely on an AJAX basis without page refreshes using Session handling for security. However this version also introduces the concept of JSON Web Tokens and the possibility for OAUTH2. This opens up the possibility for an entirely API based data access regime where the clients include non browser based Apps on smart devices, including the internet of things.

***Installation***

Cliqon provides a fully automated installation process but also documents a completely manual installation where automation is not desired or possible. Cliqon systems can be cloned from development to production or from system to system with minor configuration changes. For presentational websites, only the Views directory and a configuration file are unique to a given website. Whilst processes such as Composr are catered for, the system is self-contained and needs no external resources.

***Community***

The launch of an Open Source program requires the creation of a Community. Webcliq will encourage the provision of plugins and modules from contributors and partners. The support site has already been launched that includes facilities for contributors to sign up for Cliqon, upload their contributions and receive payment via PayPal when their paid contributions are downloaded and paid for. Arrangements have been made to provide a forum system which will be used to provide Community support and a vehicle for announcements. Webcliq have introduced an Issue tracking system for genuine bugs in the Cliqon code, a Question and Answer system based on Q2A to provide a bespoke alternative to Stackoverflow deal with matters related to the use of Cliqon that are not specifically non-performant Cliqon code and finally a task and project management for issues and requests that are accepted as enhancements.

To provide support to the community around Cliqon, Webcliq have also introduced a Content Delivery Network and a replacement to the much loved but now defunct Hotscripts.Com, at Hotscripts.Org to support Community contributions that are not directly connected with Cliqon.

***Support***

Staff at Webcliq will provide support to the Community of Developers as best as it can, bearing in mind that much of this support will be free of charge. However the Webcliq Company will be pleased to offer Paid Support by the hour and by project. It will also be happy to undertake project work on a paid basis using the Cliqon infrastructure.

***Documentation***

Cliqon has a separate site that provides a Documentation Wiki. Work on the Wiki is well advanced but it remains a work in progress.
