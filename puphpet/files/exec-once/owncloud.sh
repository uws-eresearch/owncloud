#!/bin/bash
sudo yum -y remove yum-plugin-priorities #disable the priorities plygin that results in downloading the wrong Owncloud versions
sudo wget -P /etc/yum.repos.d/ http://download.opensuse.org/repositories/isv:ownCloud:community/CentOS_CentOS-6/isv:ownCloud:community.repo
#enable epel for downloading dependency packages
sudo wget http://dl.fedoraproject.org/pub/epel/6/x86_64/epel-release-6-8.noarch.rpm
sudo wget http://rpms.famillecollet.com/enterprise/remi-release-6.rpm
sudo rpm -Uvh remi-release-6*.rpm epel-release-6*.rpm
#install owncloud
sudo yum -y install owncloud-6.0.2-8.2.noarch
sudo setenforce 0
sudo service iptables stop
sudo service httpd start
# ensure iptables stay stopped
sudo chkconfig iptables off
# ensure httpd starts on boot
sudo chkconfig httpd on