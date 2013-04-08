motive
======

Motive is a top secret project

Motive is not a real name.

{ "first_name": "Evan",
"last_name": "Halley",
"username": "ehalley",
"email_address": "evan.halley@hotmail.com",
"credential": "12345678"}


---Environment Setup---

Required software
Apache 2.2.22
PHP 5.3.10
Neo4j Server 1.8.2
Neo4jPHP (https://github.com/jadell/Neo4jPHP)
Slim PHP (http://www.slimframework.com/)

--Apache and PHP--
May already be installed, if not, please use your OS standard way of installing software packages.

--Neo4j
Can be acquired from: http://www.neo4j.org/download
Once installed, start the Neo4j server: sudo {NEO4J Home}/bin/neo4j start

--Slim PHP / Neo4jPHP--
Motive uses PHP Composer to download and initialize PHP libraries.  Install PHP Composer for your operating system:
http://getcomposer.org/download/

--Download Motive--
git clone git@github.com:emuneee/motive.git

Run PHP composer to download Neo4jPHP and Slim PHP libraries in the root of the Motive GIT source folder:
php composer.phar install

--Apache Setup--
Configure Apache 2.2.22 to point to the {Motive GIT source folder}/api/ by configuring httpd.conf (please see system documentation)

--Enabled URL Rewrite--
cd /etc/apache2/mods-enabled (or where ever your Apache configuration files are)
ln -s ../mods-available/rewrite.load rewrite.load
In httpd.conf for the site corresponding to Motive...

Change AllowOverride none to AllowOverride all

Restart Apache


