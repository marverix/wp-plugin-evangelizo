#!/bin/bash

# Publish WordPress Plugin
# Copyright 2021 Marek Sierociński
# ISC license
#
# Based on instruction from https://developer.wordpress.org/plugins/wordpress-org/how-to-use-subversion/

PLUGIN_NAME=evangelizo
PLUGIN_TMP_DEST=/tmp/$PLUGIN_NAME
PLUGIN_VERSION=$(cat ./src/readme.txt | grep -Po "(?<=Stable tag: ).+")

echo "Creating tmp..."
rm -rf $PLUGIN_TMP_DEST
svn co -q https://plugins.svn.wordpress.org/$PLUGIN_NAME $PLUGIN_TMP_DEST

echo "Copying source..."
rsync -r -t -o --delete -s ./src/ $PLUGIN_TMP_DEST/trunk
cp LICENSE $PLUGIN_TMP_DEST/trunk/LICENSE
cd $PLUGIN_TMP_DEST; svn add -q --force trunk/*

echo "Copying assets..."
rsync -r -t -o --delete -s ./assets/ $PLUGIN_TMP_DEST/assets
cd $PLUGIN_TMP_DEST; svn add -q --force assets/*

echo "Creating tag ${PLUGIN_VERSION}..."
cd $PLUGIN_TMP_DEST; mkdir -p tags/$PLUGIN_VERSION; cp -R trunk/* tags/$PLUGIN_VERSION; svn add -q tags/$PLUGIN_VERSION

echo "Preparing to publish..."

echo -n "> Enter username: "
read WP_USERNAME
echo -n "> Enter password: "
read WP_PASSWORD

echo "Publishing..."
cd $PLUGIN_TMP_DEST; svn ci -m "Version ${PLUGIN_VERSION}" --username "${WP_USERNAME}" --password "${WP_PASSWORD}"

echo "Done"
