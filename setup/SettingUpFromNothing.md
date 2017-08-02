# Install instructions

This guide should get you up-and-running with 100 % certainty in Windows 7+. Don't skip steps.

## Preparations

1. Install Oracle VM VirtualBox https://www.virtualbox.org
1. Install Vagrant https://www.vagrantup.com
1. Download PuTTY from https://www.chiark.greenend.org.uk/~sgtatham/putty/latest.html
1. Using ``cmd.exe``, go to the directory you want to use for projects, eg. E:\web\
1. Run the command: ``vagrant init ubuntu/trusty32``
1. When it is completed, modify the file ``E:\web\Vagrantfile`` to your liking. For example:
   * config.vm.network "forwarded_port", guest: 5656, host: 5656, host_ip: "127.0.0.1"
   * config.vm.network "forwarded_port", guest: 3306, host: 3306, host_ip: "127.0.0.1"
1. Back in cmd.exe directory E:\web\ run the command: ``vagrant up``
1. It will run for a while, 2-3 minutes
1. Then ssh to 127.0.0.1 in port 2222, using PuTTY
1. username: vagrant, password: vagrant
1. Inside the ssh session, run: ``cd /vagrant``
1. The contents of that directory are the same that you have in ``E:\web\``
1. Normally you should only edit the files in Windows, so that the data stays intact

## Setting up the environment

1. At stock settings, the platform is quite empty, so install these:
1. Git:            ``sudo apt-get install git``
1. Apache:         ``sudo apt-get install apache2``
1. PHP7:           ``sudo add-apt-repository ppa:ondrej/php``
                   ``sudo apt-get update``
                   ``sudo apt-get install php7.1``
1. MariaDB:        ``sudo apt-get install mariadb-server``
1. MySQL-plugin:   ``sudo apt-get install php7.0-mysql``
1. During the above, you will see warnings from Apache about ServerName. Fix it like this:
1. Give the command: ``echo "ServerName localhost" | sudo tee /etc/apache2/conf-available/servername.conf``
1. Then activate it: ``sudo a2enconf servername``
1. And restart Apache for it to take effect: ``sudo service apache2 reload``

## Configuring things

1. Add the project directory to Apache config: ``sudo vim /etc/apache2/apache2.conf``
1. Scroll down the file, until you see some <Directory> things:
1. Add a new one below them, using the below example, and save the file:
   ```
    <Directory /vagrant/vortech-api/>
        Options Indexes FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        allow from all
        Require all granted
    </Directory>
   ```
1. Then add the site to Apache: ``cd /etc/apache2/sites-available``
1. Create your own config there: ``sudo cp 000-default.conf vortech-api.conf``
1. Edit the file: ``sudo vim vortech-api.conf``
1. On the very first row, there is: ``<VirtualHost *:80>``
1. Change the 80 to 5656, which will be the port we use to access the site (http://localhost:5656)
1. Then a bit lower down in the same file, there is: ``DocumentRoot /var/www/html``
1. Replace it with: ``DocumentRoot /vagrant/vortech-api``
1. Save the file and then activate the config: ``sudo a2ensite vortech-api.conf``
1. You can also enable Rewrites (pretty urls, eg. http://localhost/api/1.0/) with: ``sudo a2enmod rewrite``
1. Also, add the custom port from step 30 to the ports Apache listens to: ``sudo vim /etc/apache2/ports.conf``
1. It already contains ``Listen 80``. Add a new entry below it: ``Listen 5656``, and save the file
1. Then you must disable the Apache PHP5 plugin (automatically installed): ``sudo a2dismod php5``
1. And then enable the PHP7 plugin: ``sudo a2enmod php7.1``
1. And install the PHP7 MySQL plugin: ``sudo apt-get install php7.0-mysql``
1. Finally, restart Apache: ``sudo service apache2 reload``

## Start the project

1. Inside Vagrant: ``cd /vagrant/``
1. And then give the command: ``git clone https://github.com/Torniojaws/vortech-api.git``
1. When it is done, you should have the directory: ``/vagrant/vortech-api/``
1. In Windows, use a browser to go to: http://localhost:5656/api/1.0/
1. You should see the API documentation. If so, all is ready!

## Connecting to the Vagrant database from Windows

1. In Vagrant, edit the MySQL config: ``sudo vim /etc/mysql/my.cnf``
1. Comment out these two rows with #:
   * ``# skip-external-locking``
   * ``# bind-address``
1. Save and restart MySQL: ``sudo service mysql restart``
1. Then in your SQL-client (eg. HeidiSQL), add a new session of type: MySQL (SSH tunnel)
1. In the regular settings, use the address: 127.0.0.1 in port 3306
1. Username and Password for it is the one you configured in the project file: ``setup/create_db.sql``
1. In the SSH tunnel tab, point to plink.exe, use 127.0.0.1 as the host, and the port is 2222
1. Username and Password should be: "vagrant"
1. Local port is also 3306
1. Now it should work!

## Running tests

1. To run tests, PHPUnit is used. It needs to be installed also.
1. Run: ``sudo apt-get install phpunit``. This will install an old version, 3.x
1. Then run:
   * ``wget https://phar.phpunit.de/phpunit.phar``
   * ``chmod +x phpunit.phar``
   * ``sudo mv phpunit.phar /usr/local/bin/phpunit``
1. Now you will have the latest stable release of PHPUnit (6.2.3 at the moment)
1. Then finally, install the xdebug driver to generate code coverage reports
1. Run: ``sudo apt-get install php-xdebug``
1. Then try running this in the project root: ``phpunit --whitelist tests --coverage-text tests/``
1. You should get some output and a code coverage report. If so, all is set!
