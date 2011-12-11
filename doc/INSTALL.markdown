

Requirement
===========

* Apache with mod_rewrite 
      a2enmod rewrite && apache2ctl restart

* APC is required if you want to enable the progress bar during file upload. 
  See 'Installing and configuring APC' section.

* php5-curl for CAS authentication

* php5-ldap if you use a LDAP server

Installation
============

* Download FileZ sources :

  * from Git
      git clone git://github.com/UAPV/FileZ.git filez_web_root

  * from SVN
      svn checkout http://svn.github.com/UAPV/FileZ.git filez_web_root

  * from a package
      tar -xvvf filez-2.0.tar.gz
      cp filez-2.0/* filez_web_root

* Open your web browser on your FileZ web root and follow instructions

* Enjoy !

Upgrade from Filez-1.x
======================

Filez-2 allows you to use your the same database as your previous installation.
Actually your database schema will be updated so you should backup your database,
filez source files, and keep the uploaded files.

All you have to do is remove all files from your old filez install (keep uploaded
files) and copy filez-2 files instead.

Then, just follow "Installation" paragraph and fill the configuration form with
your filez-1 database settings.


Advanced configuration
======================

if you need to edit your configuration afterward, all the configuration is stored
in config/filez.ini. The following paragraphs will help you to edit this file
manually.


General Configuration
---------------------

The "[app]" section contains common options :

- "use_url_rewriting" (boolean) : Not tested with "false" yet
- "upload_dir" (Absolute path)  : Upload directory (writtable by the web server)
- "log_dir"    (Absolute path)  : Log directory (writtable by the web server)
- "filez1_compat" (boolean)     : Enable the filez-1.x support for downloading previously uploaded files
- "max_file_lifetime" (integer) : Maximum lifetime of the file on the server before being delete
- "default_file_lifetime (int)  : Default lifetime
- "min_hash_size" (integer)     : Minimum number of characters in the hash
- "max_hash_size" (integer)     : Maximum number of characters in the hash
- "default_locale" (string)     : Default locale used when Filez can find the prefered user locale or when there is no corresponding i18n for the user locale.


Database
--------

    [db]
    dsn      = "mysql:host=your_sql_host;dbname=your_filez_database"
    user     = filez
    password = password

It hasn't been tested yet but you should be able to connect with another
database driver than mysql. Check php doc for the dsn syntax corresponding to
your driver : <http://www.php.net/manual/en/pdo.drivers.php>.


Email
-----

Filez use an smtp server to send notification mails. You have configure it in
the "[email]" section :

    [email]
    from_email=filez@univ-avignon.fr
    from_name=Filez
    host=smtp.univ-avignon.fr
    ; auth=login ; possible values = crammd5, login, plain
    ; port=25
    ; username=user
    ; password=pwd


Configure Security strategy
---------------------------

Filez let you choose between 3 authentication strategies :

- CAS + LDAP
- LDAP only
- Database

In any case, don't forget to describe the user repository schema by following
the paragraph called "User attribute translation".

### CAS Server

Install dependencies :

    apt-get install curl php5-curl

Edit config/filez.ini with the following settings :

    [app]
    auth_handler_class = Fz_Controller_Security_Cas
    user_factory_class = Fz_User_Factory_Ldap
    
    [auth_options]
    cas_server_host = url.of-your-cas-server.com

You can also add these options :

* 'cas_server_port' 443 by default
* 'cas_server_path' Path where your web server is responding (ex 'cas' if your
   cas server is located at url.of-your-cas-server.com/cas/)

Follow the section named "Ldap Identification" to configure the Ldap server.

### Database

You can use an existing database with Filez. You just need to configure database
connection and describe the table containing your users.

Edit config/filez.ini with the following line in the '[app]' section :

    auth_handler_class = Fz_Controller_Security_Internal
    user_factory_class = Fz_User_Factory_Database

If your user table is located on the same database as the filez table, use the
following setting :

    [user_factory_options]
    db_use_global_conf = true

Otherwise you will need to configure another connection :

    [user_factory_options]
    db_server_dsn         = "mysql:host=localhost;dbname=filez"
    db_server_user        = filez
    db_server_password    = filezpwd

Finally describe where and how the username/password are stored :

    [user_factory_options]
    db_table              = user_tabme
    db_password_field     = password_field
    db_username_field     = username_field
    db_password_algorithm = SHA1

"db_password_algorithm" describe the method used to encrypt the password. There
is several possible values that should suit your needs :

- "MD5"
- "SHA1"
- PHP Function name ex: "methodName"
- PHP Static method ex: "ClassName::Method"
- Plain SQL ex: "SHA1(CONCAT(salt, :password))"

If you use a PHP callback, just put the file containing your function under the
'lib/' directory.


### LDAP server

#### Ldap Authentication

Edit config/filez.ini with the following line in the '[app]' section :

    auth_handler_class = Fz_Controller_Security_Internal

#### Ldap Identification

Ldap connection is defined under the '[user_factory_options]' section of
filez.ini. By default, Filez use an anonymous connection, the only mandatory
parameter is 'host'. But you may need some additionnal configuration, a list of
all possible options can be found here :
<http://framework.zend.com/manual/en/zend.ldap.api.html>

Example configuration :

    [user_factory_options]
    host = ldap.univ-avignon.fr
    useSsl = false
    baseDn = "ou=people,dc=univ-avignon,dc=fr"
    bindRequiresDn = true

### User attribute translation

In order to make the application schema agnostic with differents user storage
facilities, each user attributes is translated from its original name to the
application name. The syntax is as follow : application_name = original_name
and must be placed under the "[user_attributes_translation]" section.

These attributes are required by filez :

- firstname
- lastname
- email
- id

For our Ldap repository, the configuration looks like this :

    [user_attributes_translation]
    firstname = givenname
    lastname  = sn
    email     = mail
    id        = uid


Notes
=====

Installing and configuring APC
------------------------------

### On debian :

    apt-get install php-apc
    echo "apc.rfc1867 = On"   >> /etc/php5/apache2/conf.d/apc.ini
    apache2ctl restart

### For other distributions :

Install the following packages 'build-essential', 'php5-dev', 'php-pear', 'apache2-prefork-dev'
  
Build and install APC extension :

    pecl install apc

Enable extension and specific option :

    echo "extension = apc.so" >> /etc/php5/apache2/conf.d/apc.ini
    echo "apc.rfc1867 = On"   >> /etc/php5/apache2/conf.d/apc.ini
    apache2ctl restart


Installing and configuring PECL::UploadProgress
-----------------------------------------------

Install required tools for building UploadProgress. On debian :

    apt-get install php5-dev

Build and install APC extension :

    pecl install uploadprogress

Enable extension and specific option :

    echo "extension = uploadprogress.so" >> /etc/php5/apache2/conf.d/uploadprogress.ini
    apache2ctl restart

RHEL / CentOS with PHP version 5.1
----------------------------------

Follow this howto to enable the "filter_var()" function of PHP :

http://www.cyberciti.biz/faq/rhel-cento-linux-install-php-pecl-filter/

You may also need to install the php json extension :

http://linuxchunks.com/2010/04/php-fatal-error-call-to-undefined-function-json_encode/


Apache virtual host example
---------------------------

<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName  filez-test.univ-avignon.fr
    php_admin_value post_max_size       750M
    php_admin_value upload_max_filesize 750M
    php_admin_value max_execution_time  1200
    php_admin_value upload_tmp_dir "/media/data/tmp"

        DocumentRoot /var/www/filez-test.univ-avignon.fr
        <Directory />
                Options FollowSymLinks
                AllowOverride All
        </Directory>
        <Directory /var/www/filez-test.univ-avignon.fr>
                Options Indexes FollowSymLinks MultiViews
                AllowOverride All
                Order allow,deny
                allow from all
        </Directory>

        ErrorLog  /var/log/apache2/filez-error.log
        CustomLog /var/log/apache2/filez-access.log combined
        LogLevel warn
</VirtualHost>


Nginx virtual host example
--------------------------

upload_progress fzuploads 1m;
server {
  listen *:80;
  server_name filez-test.univ-avignon.fr;
  root /usr/local/www/filez;
  location / {
    index index.php;
    rewrite ^/filez(/.+)$ /filez/index.php$1 last;
  }
  # infos de progression des uploads (http://wiki.nginx.org/HttpUploadProgressModule)
  location /upload/progress {
    rewrite ^/upload/progress/(\w+)$ /upload/progress/?X-Progress-ID=$1;
    report_uploads fzuploads;
    upload_progress_content_type 'application/json';
    upload_progress_template starting '{"total":$uploadprogress_length,"current":0,"done":0}';
    upload_progress_template uploading '{"total":$uploadprogress_length,"current":$uploadprogress_received,"done":0}';
    upload_progress_template done '{"total":$uploadprogress_length,"current":$uploadprogress_received,"done":1}';
    upload_progress_template error '{"error":$uploadprogress_status}';
  }
  location ~ ^/index.php$ {
    sendfile on;
    fastcgi_read_timeout 120;
    fastcgi_index  index.php;
    fastcgi_param  HTTPS        "on";
    fastcgi_pass   127.0.0.1:9099;
    fastcgi_split_path_info ^(.+\.php)(/.*)$;
    fastcgi_param  SCRIPT_FILENAME    $document_root$fastcgi_script_name;
    fastcgi_param  PATH_INFO          $fastcgi_path_info;
    fastcgi_param  PATH_TRANSLATED    $document_root$fastcgi_path_info;
    client_max_body_size 3500m;
    upload_max_file_size 3000m;
    include fastcgi_params;
    track_uploads fzuploads 60s;
  }
}


Authenticating against an ORACLE OIDDAS LDAP server
---------------------------------------------------

    [user_factory_options]
    baseDn = "cn=Users,dc=domain,dc=fr"
    host = "serveur.domain.fr"
    useSsl = false
    bindRequiresDn = true
    username = "cn=p.nom,cn=Users,dc=domain,dc=fr"
    password = "password"
    accountFilterFormat = "(&(objectclass=*)(uid=%s))"
    
    [user_attributes_translation] firstname = "givenname"
    lastname = "sn"
    email = "mail"
    id = "cn"

    
Required Ports to install FileZ on FreeBSD (8.2)
------------------------------------------------

* lang/php5
* www/php5-session
* textproc/php5-simplexml
* databases/php5-pdo
* databases/php5-pdo_mysql
* devel/php5-json
* converters/php5-iconv
* security/php5-filter
* devel/pecl-uploadprogress (if you choose this method for handling upload progress)
* net/openldap24-client/ (pour l'auth ldap)
* net/php5-ldap (pour l'auth ldap)
* databases/mysql51-server | databases/mariadb-server
* databases/mysql51-client | databases/mariadb-client
* www/apache22 | www/nginx


