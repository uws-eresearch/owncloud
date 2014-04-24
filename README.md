CrateIt 
=======

This project is a plugin to OwnCloud that provides a way for researchers to package, store and publish their data.  This repository is forked from the UWS eResearch repository: https://github.com/uws-eresearch/apps 

### Developer Setup

#### Download Packages Required
Download Vagrant and Virtual Box

#### Set up environment

     git clone git@github.com/IntersectAustralia/owncloud.git
     git submodule init
     git submodule update
     vagrant up
     
#### Check your setup
     
     vagrant ssh
     sudo su -
     ls /var/www/html/owncloud/apps
     
You should see the directories crate_it and file_previewer

#### Access owncloud
The server should be started already, go to "http://localhost:8080/owncloud", and create an account by entering a username and password.
    
### QA Setup

#### Install PHP 
If you don't have PHP5.4 already, do the following to install (this is for CentOS 5.4):

    sudo rpm -Uvh http://mirror.webtatic.com/yum/el5/latest.rpm
    sudo yum install php54w
    sudo yum install php54w-xml
    sudo yum install php54w-mbstring
    
    TODO

### To update production site

    TODO
