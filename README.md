#Motive

* Motive is a top secret project
* Motive is not a real name.

### Table of Contents  
[Environment Setup](#envsetup)  
[Required Software](#required-software)  
[Install Apache and PHP](#apache-and-php)  
[Install Neo4j](#neo4j)  
[Install Composer](#composer)  
[Install Slim PHP / Neo4jPHP](#slim-and-neo4j)  
[Download Motive](#motive)  
[Apache Setup](#apache)  
[Enable URL Rewrite](#rewrite)  


<a name="evnsetup"/>
##Environment Setup

<a name="required-software"/>
**Required Software**

* `Apache 2.2.22`
* `PHP 5.3.10`
* `Neo4j Server 1.8.2`
* [`Composer`](http://getcomposer.org/download/)
* [`Neo4jPHP`](https://github.com/jadell/Neo4jPHP)
* [`Slim PHP`](http://www.slimframework.com/)

<a name="apache-and-php"/>
##Install Apache and PHP

May already be installed, if not, please use your OS standard way of installing software packages.

<a name="neo4j"/>
##Neo4j

Can be acquired from: http://www.neo4j.org/download
Once installed, start the Neo4j server: sudo {NEO4J Home}/bin/neo4j start

<a name="composer"/>
##Composer

Motive uses PHP Composer to download and initialize PHP libraries.

Install PHP Composer for your operating system: http://getcomposer.org/download/

<a name="slim-and-neo4j"/>
##Slim PHP / Neo4jPHP

<a name="motive"/>
##Download Motive

`git clone git@github.com:emuneee/motive.git`

Run PHP composer to download Neo4jPHP, Slim PHP, and PHPSec libraries

From /motive/api run:

`php composer.phar install`

-or-

`composer install`

<a name="apache"/>
##Apache Setup

1. Configure `Apache 2.2.22` to point to the Motive application by updating /motive/motive.conf
	* Open /motive/motive.conf and update the `DocumentRoot` and `Directory` with your own paths
		* `DocumentRoot` should point to .../motive/api
	* Copy motive.conf to your APACHE_HOME/users directory
		* `cp /motive/motive.conf <APACHE_HOME>/users`
2. In your Apache configuration file at `<APACHE_HOME>/httpd.conf`, find the directory access configuration `<Directory />`, and update:
	* Comment out `AllowOverride none`
	* Add in `AllowOverride all`
3. Uncomment the PHP5 module in your Apache configuration file at APACHE_HOME/httpd.conf
	* `LoadModule php5_module libexec/apache2/libphp5.so`

<a name="rewrite"/>
##Enable URL Rewrite

This step is necessary for us to expose our API.


```
mkdir /etc/apache2/mods-enabled #If directory does not already exist
cd /etc/apache2/mods-enabled
ln -s ../mods-available/rewrite.load rewrite.load
```

<a name="test"/>
##Test Your Setup

1.  Visit http://localhost/index.php.  If the Slim PHP page loads, your setup is correct.  If not, check your Apache logs for errors.
