# Vortech API
The web host doesn't allow installing Python packages, so the backend needs to be done with PHP instead. So, a RESTful API it is then.

## Starting idea
Create a normal RESTful API with the standard CRUD way for paths and access, eg.
- Create new things: ``POST /news`` with a JSON attached
- Read data: ``GET /albums/:id`` which will return a JSON
- Update existing data: ``PUT /guestbook/:id/comment`` with a JSON attached, and return result JSON
- Delete something: ``DELETE /users/:id`` which will return HTTP status 204

## URL
The URL will most likely be http://www.vortechmusic.com/api/1.0 with future versions being either /1.1 or /2.0

## Auth
Since we cannot install anything on the web host, a less than optimal way must be implemented using user login instead of eg. OAuth2

## Database
All queries will be done using PDO. User passwords will be hashed using a PBFKD2 implementation, which should be very secure.

## Testing
Everything possible will be covered by PHPUnit tests.

## Versions
The versions are locked in place by the host and cannot be changed.
- PHP 5.4.40
- MySQL 5.5.48

## Frontend
The frontend will be in a separate repository. It will be done using ReactJS and Bootstrap.

## Apache setup
1. Edit ``/etc/apache2/apache2.conf`` and add the below
```
<Directory /vagrant/vortech-api>  
    Options Indexes FollowSymLinks MultiViews  
    AllowOverride All  
    Order allow,deny  
    allow from all  
    Require all granted  
</Directory>  
```
1. Edit ``/etc/apache2/ports.conf`` and add the below to it, where ``8081`` is the port you want to use
```
Listen 8081
```
1. Then go to ``/etc/apache2/sites-available/`` directory and ``sudo cp 000-default.conf mysite.conf``
1. Edit the file so it has:
```
ServerName localhost
DocumentRoot /vagrant/vortech-api
```
1. Then enable the site with ``sudo a2ensite mysite.conf``
1. Then enable rewrites (if not yet enabled): ``sudo a2enmod rewrite``
1. And restart Apache for all this to take effect: ``sudo service apache2 restart``
1. If needed, install the MySQL for PHP: ``sudo apt-get install php5-mysql``
1. Enable PDO, if not yet enabled: ``/etc/php5/apache2/php.ini`` and uncomment ``extension=pdo_mysql.so``
1. Restart Apache: ``sudo service apache2 restart``
1. Open the site in a browser: http://localhost:8081/api/1.0/news
