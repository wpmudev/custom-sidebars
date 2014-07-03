#!/bin/bash


# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
#                             CUSTOM SIDEBARS PRO                             #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

VER=2.0                      # Update this to match the current release version.
FOLDER=custom-sidebars       # This will never change!

DEST=${1-~/Desktop}          # Optionally define where the archive is saved.
ARCHIVE=$FOLDER-pro-$VER.zip # This will never change!

if [ $# -gt 1 ]; then
	ARCHIVE=$2
fi


# ----- This will create the archive / DON'T CHANGE THIS -----------------------

cd ..
python /usr/local/bin/git-archive-all --force-submodules --prefix $FOLDER/ "$DEST"/$ARCHIVE

# ------------------------------------------------------------------------------
# Depends on:
# https://github.com/Kentzo/git-archive-all
