#!/bin/bash
for i in *.php # or whatever other pattern...
do
  if ! grep -q Copyright $i
  then
    cat /home/lloyd/UWS/Scripts/copyright.txt $i >$i.new && mv $i.new $i
  fi
done
