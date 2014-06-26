CrateIt 
=======

This project is a plugin to OwnCloud that provides a way for researchers to package, store and publish their data.  This repository is forked from the UWS eResearch repository: https://github.com/uws-eresearch/apps 

### Developer Setup

#### Download Packages Required

Download Vagrant and Virtual Box

#### Set up environment

```
$ git clone git@github.com:IntersectAustralia/owncloud.git
$ cd owncloud
$ git submodule init
$ git submodule update
$ vagrant up
```

#### Check your setup

```
$ vagrant ssh
$ sudo su -
$ ls /var/www/html/owncloud/apps
```

You should see the directories crate_it and file_previewer

#### Access owncloud

The server should be started already, go to [http://localhost:8080/owncloud](http://localhost:8080/owncloud) and create an account by entering a username and password.

NOTE

If you go to the Crate It page and you cannot see any folders/files, or the metadata section on the right hand side, that the cr8it config file  (/var/www/html/owncloud/data/cr8it_config.json) is not set up properly. 
Please contact the Intersect team for more information on how to configure this file.

Once you have changed the cr8it_config.json file, be sure the restart the server:
```
$ vagrant ssh
$ sudo su -
$ service httpd restart
```
    
#### Installing Test Frameworks

Download and install composor

```
$ curl http://getcomposer.org/installer | php
```

Install components

```
$ php -d detect_unicode=Off composer.phar install --prefer-source -v
```

Start java selenium server

```
# Assuming you are inside the owncloud directory (where you cloned the project)
$ cd apps/crate_it
$ bin/selenium/start.sh

# Now you can run tests as follows:
$ bin/behat features/your_behat_test.feature
```
