CrateIt 
=======

This project is a plugin to OwnCloud that provides a way for researchers to package, store and publish their data.  This repository is forked from the UWS eResearch repository: https://github.com/uws-eresearch/apps 

### Developer Setup

#### Download Packages Required

Download Vagrant and Virtual Boxn 4.2

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
    
#### Installing Test Frameworks

Download and install composor

```
$ curl http://getcomposer.org/installer | php
```

Install components

```
$ php -d detect_unicode=Off composer.phar install --prefer-source -v
```

