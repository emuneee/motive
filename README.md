#motive

* Motive is a top secret project
* Motive is not a real name.


##Environment Setup

**Required Software**

* `Apache 2.2.22`
* `PHP 5.3.10`
* `Neo4j Server 1.8.2`
* [`Composer`](http://getcomposer.org/download/)
* [`Neo4jPHP`](https://github.com/jadell/Neo4jPHP)
* [`Slim PHP`](http://www.slimframework.com/)


###Install Apache and PHP

May already be installed, if not, please use your OS standard way of installing software packages.


###Neo4j

Can be acquired from: http://www.neo4j.org/download
Once installed, start the Neo4j server: sudo {NEO4J Home}/bin/neo4j start


###Composer

Motive uses PHP Composer to download and initialize PHP libraries.

Install PHP Composer for your operating system: http://getcomposer.org/download/


###Slim PHP / Neo4jPHP


###Download Motive

`git clone git@github.com:emuneee/motive.git`

Run PHP composer to download Neo4jPHP and Slim PHP libraries

From /motive/api run:

`php composer.phar install`

-or-

`composer install`

###Apache Setup

1. Configure `Apache 2.2.22` to point to the Motive application by updating /motive/motive.conf
	* Open /motive/motive.conf and update the `DocumentRoot` and `Directory` with your own paths
	* Copy motive.conf to your APACHE_HOME/users directory
		* `cp /motive/motive.conf <APACHE_HOME>/users`
2. In your Apache configuration file at `<APACHE_HOME>/httpd.conf`, find the directory access configuration `<Directory />`, and update:
	* Comment out `AllowOverride none`
	* Add in `AllowOverride all`
3. Uncomment the PHP5 module in your Apache configuration file at <APACHE_HOME>/httpd.conf
	* `LoadModule php5_module libexec/apache2/libphp5.so`

###Enabled URL Rewrite

	cd /etc/apache2/mods-enabled #Apache modules folder
	ln -s ../mods-available/rewrite.load rewrite.load
