Notes on development and testing

=== Internal folders and files

The "@dev*" folders are internal folders, they are not included in the git-archive.
-> check the .gitattributes file for detailled infos which files are excluded

@dev-css .. These are SASS files that should be compiled into /css
@dev-js .. These are js files that are minified into /js

For compilation and minifing files use Prepros (http://alphapixels.com/prepros/)
In case you have the Pro version you can import the prepros.json file which
already contains all the correct project settings.

=== Get Plugin Zip Archive

1. Edit file @dev/archive.sh and make sure the version-number is correct

2. Use terminal to create the zip archive:
   > cd <plugin-dir>/@dev
   > sh ./archive.sh
   This will generate a clean zip archive with all plugin files on your desktop.

=== Testing

- Important test-cases are documented in @dev/testcases.txt

- To setup a fresh WordPress installation with only this plugin installed use
  the script @dev/clean-installation.sh

  Example usage (in terminal)

  sh ./clean-installation.sh \
  http://local.stage /Volumes/Macintosh\ HD2/Sites/wordpress-stage \
  local-stage stage-user stage-pass localhost latest

  Generates this output:

  Current Dir:       /Volumes/Macintosh HD2/Sites/wordpress/wp-content/plugins/custom-sidebars/@dev
  WordPress Dir:     /Volumes/Macintosh HD2/Sites/wordpress-stage
  WordPress URL:     http://local.stage
  WordPress User:    test
  WordPress Pass:    test
  WordPress version: latest
  DB Host:           localhost
  DB Name:           local-stage
  DB User:           stage-user
  DB Pass:           stage-pass
  ------------------------------------------
  - Removed old WordPress directory
  - Created new WordPress directory
  - Download and install WordPress files (version 'latest') ...
  - Installation finished
  Database "local-stage" dropped
  - Created fresh database
  Success: Generated wp-config.php file.
  Success: WordPress installed successfully.
  - Created a clean export of the current plugin
  - Plugin extracted to new WordPress installation

  There you go: http://local.stage is a fresh and clean WordPress installation!