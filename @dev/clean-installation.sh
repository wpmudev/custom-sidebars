#!/usr/bin/bash
clear

if [ $# -lt 3 ]; then
	echo "usage: $0 <wp-url> <wp-dir> <db-name> <db-user> <db-pass> [db-host] [wp-version]"
	echo ""
	echo "<wp-url>"
	echo "        This is the URL where your new testing installation will be"
	echo "        setup. You can login at this URL with user/pass 'test'/'test'"
	echo ""
	echo "<wp-dir>"
	echo "        This directory will be deleted and a new Wordpress installation"
	echo "        is created at this location."
	echo ""
	echo "<db-name>"
	echo "        This database will be deleted and created from scratch."
	echo ""
	echo "<db-user> <db-pass>"
	echo "        User must exist and have permission to DELETE and "
	echo "        CREATE databases."
	echo ""
	echo "<db-host>"
	echo "        Default: localhost"
	echo ""
	echo "<wp-version>"
	echo "        Default: latest; can also be a version number like 3.1"
	exit 1
fi

WP_URL=$1
WP_USER=test
WP_PASS=test
WP_DIR=$2
DB_NAME=$3
DB_USER=$4
DB_PASS=$5
DB_HOST=${6-localhost}
WP_VERSION=${7-latest}
CUR_DIR="$( pwd )"

# Display a sumary of all parameters for the user.
show_infos() {
	echo "WordPress URL:     $WP_URL"
	echo "WordPress User:    $WP_USER"
	echo "WordPress Pass:    $WP_PASS"
	echo "WordPress Dir:     $WP_DIR"
	echo "WordPress version: $WP_VERSION"
	echo "DB Host:           $DB_HOST"
	echo "DB Name:           $DB_NAME"
	echo "DB User:           $DB_USER"
	echo "DB Pass:           $DB_PASS"
	echo "Current Dir:       $CUR_DIR"
	echo "------------------------------------------"
}

# Remove the existing WordPress folder and create a new empty folder.
create_dir() {
	if [ -d "$WP_DIR" ]; then
		if [ -L "$WP_DIR" ]; then
			rm "$WP_DIR"
		else
			rm -rf "$WP_DIR"
		fi
		echo "- Removed old WordPress directory"
	fi

	mkdir -p "$WP_DIR"
	cd "$WP_DIR"
	echo "- Created new WordPress directory"
}

# Download WordPress core files
install_wp() {
	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	echo "- Download and install WordPress files (version '$WP_VERSION') ..."
	curl -s -o "$WP_DIR"/wordpress.tar.gz http://wordpress.org/${ARCHIVE_NAME}.tar.gz
	tar --strip-components=1 -zxmf "$WP_DIR"/wordpress.tar.gz -C "$WP_DIR"
	rm  "$WP_DIR"/wordpress.tar.gz
	echo "- Installation finished"
}

# Drop old Database and create a new, empty WordPress database.
install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [[ "$DB_SOCK_OR_PORT" =~ ^[0-9]+$ ]] ; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	mysqladmin drop $DB_NAME --force --silent --user="$DB_USER" --password="$DB_PASS"$EXTRA
	echo "- Old database droped"

	mysqladmin create $DB_NAME --force --silent --user="$DB_USER" --password="$DB_PASS"$EXTRA
	echo "- New database created"

	if [ -f "$WP_DIR"/wp-config.php ]; then
		rm "$WP_DIR"/wp-config.php
	fi

	cd "$WP_DIR"
	wp core config --dbhost=$DB_HOST --dbname=$DB_NAME --dbuser="$DB_USER" --dbpass="$DB_PASS" --skip-check
	wp core install --url=$WP_URL --title="Testing installation" --admin_user=$WP_USER --admin_password=$WP_PASS --admin_email=test@example.com
}

install_plugin() {
	if [ -f "$CUR_DIR"/archive.sh ]; then
		cd "$CUR_DIR"
		"$CUR_DIR"/archive.sh "$CUR_DIR" plugin.zip
	fi
}

show_infos
#create_dir
#install_wp
#install_db
install_plugin

echo "There you go: $WP_URL is a fresh and clean WordPress installation!"
echo ""