#!/bin/bash
sudo yum -y remove yum-plugin-priorities #disable the priorities plygin that results in downloading the wrong Owncloud versions
sudo wget -P /etc/yum.repos.d/ http://download.opensuse.org/repositories/isv:ownCloud:community/CentOS_CentOS-6/isv:ownCloud:community.repo
sudo yum -y install owncloud-6.0.2-8.2.noarch
sudo setenforce 0
sudo service iptables stop
sudo service httpd start