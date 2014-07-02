#!/bin/bash


# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
#                             CUSTOM SIDEBARS PRO                             #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #


FOLDER=custom-sidebars     # This will never change!
ARCHIVE=$FOLDER-pro        # This will never change!
VER=2.0                    # Update this to match the current release version.


# ----- This will create the archive / DON'T CHANGE THIS -----------------------

python /usr/local/bin/git-archive-all --force-submodules --prefix $FOLDER/ ~/Desktop/$ARCHIVE-$VER.zip

# ------------------------------------------------------------------------------
# Depends on:
# https://github.com/Kentzo/git-archive-all
