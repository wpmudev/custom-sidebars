#!/bin/bash


# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #
#                                                                             #
#                            CREATE EXPORT ARCHIVE                            #
#                                                                             #
# # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # # #

if [ -f local.config.sh ]; then
	. local.config.sh
else
	if [ -f config.sh ]; then
		. config.sh
	else
		echo "There must be a config.sh or local.config.sh file in the current directory."
		exit 1;
	fi
fi

# Allow the output filename to be overwritten
if [ $# -gt 0 ]; then
	EXPORT_ARCHIVE=$1
fi

cd ..
python /usr/local/bin/git-archive-all --force-submodules --prefix $FOLDER/ "$EXPORT_ARCHIVE"

# ------------------------------------------------------------------------------
# Depends on:
# https://github.com/Kentzo/git-archive-all
