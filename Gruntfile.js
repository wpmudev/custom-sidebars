/*global require*/

/**
 * When grunt command does not execute try these steps:
 *
 * - delete folder 'node_modules' and run command in console:
 *   $ npm install
 *
 * - Run test-command in console, to find syntax errors in script:
 *   $ grunt hello
 */

module.exports = function( grunt ) {
	// Show elapsed time at the end.
	require( 'time-grunt' )(grunt);

	// Load all grunt tasks.
	require( 'load-grunt-tasks' )(grunt);

	var buildtime = new Date().toISOString();

	var conf = {

		// Concatenate those JS files into a single file (target: [source, source, ...]).
		js_files_concat: {
			'assets/js/cs-cloning.js':    ['assets/js/src/cs-cloning.js'],
			'assets/js/cs-visibility.js': ['assets/js/src/cs-visibility.js'],
			'assets/js/cs.js':            [
				'assets/js/src/cs.js',
				'assets/js/src/metabox-allow-entry-author.js',
				'assets/js/src/metabox-custom-taxonomies.js',
				'assets/js/src/metabox-roles.js'
			]
		},

		// SASS files to process. Resulting CSS files will be minified as well.
		css_files_compile: {
			'assets/css/cs-cloning.css':    'assets/sass/cs-cloning.scss',
			'assets/css/cs.css':            'assets/sass/cs.scss',
			'assets/css/cs-scan.css':       'assets/sass/cs-scan.scss',
			'assets/css/cs-visibility.css': 'assets/sass/cs-visibility.scss'
		},

		// BUILD branches.
		plugin_branches: {
			exclude_pro: [
				'./README.MD',
				'./README.md',
				'./readme.txt',
				'./screenshot-*',
				'./Gruntfile.js',
				'./package.json',
				'./inc/class-custom-sidebars-checkup-notification.php',
				'./img/heart.png',
				'./img/devman.png',
				'./img/hand-with-heart.png',
				'./assets/css/cs-scan.css',
				'./assets/css/cs-scan.min.css',
				'./assets/sass',
				'.inc/external/wdev-frash'
			],
			exclude_free: [
				'./changelog.txt',
				'./README.MD',
				'./README.md',
				'./Gruntfile.js',
				'./package.json',
				'./inc/external/wpmudev-dashboard',
				'./assets/sass',
				'./languages/*.po',
				'./languages/*.mo'
			],
			include_files: [
				'**',
				'!assets/sass/**',
				'!assets/js/src/**',
				'!assets/js/vendor/**',
				'!assets/img/src/**',
				'!node_modules/**',
				'!build/**',
				'!tests/**',
				'!**/css/src/**',
				'!**/sass/**',
				'!**/js/src/**',
				'!**/js/vendor/**',
				'!**/img/src/**',
				'!**/node_modules/**',
				'!**/**.log',
				'!**/tests/**',
				'!**/release/*.zip',
				'!release/*.zip',
				'!**/release/**',
				'!release/**',
				'!**/Gruntfile.js',
				'!**/package.json',
				'!**/build/**',
				'!.sass-cache/**',
				'!.git/**',
				'!.git',
				'!.log',
			],
			base: 'master',
			pro: 'customsidebars-pro',
			free: 'customsidebars-free',
		},

		// BUILD patterns to exclude code for specific builds.
		plugin_patterns: {
			pro: [
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /\/\* start:pro \*\//g, replace: '' },
				{ match: /\/\* end:pro \*\//g, replace: '' },
				{ match: /\/\* start:free \*[^]+?\* end:free \*\//mg, replace: '' },
			],
			free: [
				{ match: /PLUGIN_VERSION/g, replace: '<%= pkg.version %>' },
				{ match: /BUILDTIME/g, replace: buildtime },
				{ match: /\/\* start:free \*\//g, replace: '' },
				{ match: /\/\* end:free \*\//g, replace: '' },
				{ match: /\/\* start:pro \*[^]+?\* end:pro \*\//mg, replace: '' },
			],
			// Files to apply above patterns to (not only php files).
			files: {
				expand: true,
				src: [
					'**/*.php',
					'**/*.css',
					'**/*.js',
					'**/*.html',
					'**/*.txt',
					'!node_modules/**',
					'!lib/**',
					'!docs/**',
					'!release/**',
					'!Gruntfile.js',
					'!build/**',
					'!tests/**',
					'!.git/**'
				],
				dest: './'
			}
		},

		// Regex patterns to exclude from transation.
		translation: {
			ignore_files: [
				'node_modules/.*',
				'(^.php)',         // Ignore non-php files.
				'inc/external/.*', // External libraries.
				'release/.*',      // Temp release files.
				'tests/.*',        // Unit testing.
			],
			pot_dir: 'languages/', // With trailing slash.
			textdomain: 'custom-sidebars',
		},

		dev_plugin_file: 'customsidebars.php',
		dev_plugin_dir: 'custom-sidebars/'
	};

	// Project configuration
	grunt.initConfig( {
		pkg: grunt.file.readJSON( 'package.json' ),

		// JS - Concat .js source files into a single .js file.
		concat: {
			options: {
				stripBanners: true,
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			scripts: {
				files: conf.js_files_concat
			}
		},


		// JS - Validate .js source code.
		jshint: {
			all: [
				'Gruntfile.js',
				'assets/js/src/**/*.js',
			],
			options: {
				curly:   true,
				eqeqeq:  true,
				immed:   true,
				latedef: true,
				newcap:  true,
				noarg:   true,
				sub:     true,
				undef:   true,
				boss:    true,
				eqnull:  true,
				globals: {
					exports: true,
					module:  false
				}
			}
		},


		// JS - Uglyfies the source code of .js files (to make files smaller).
		uglify: {
			all: {
				files: [{
					expand: true,
					src: ['*.js', '!*.min.js'],
					cwd: 'assets/js/',
					dest: 'assets/js/',
					ext: '.min.js',
					extDot: 'last'
				}],
				options: {
					banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
						' * <%= pkg.homepage %>\n' +
						' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
						' * Licensed GPLv2+' +
						' */\n',
					mangle: {
						except: ['jQuery']
					}
				}
			}
		},


		// CSS - Compile a .scss file into a normal .css file.
		sass:   {
			all: {
				options: {
					'sourcemap=none': true, // 'sourcemap': 'none' does not work...
					unixNewlines: true,
					style: 'expanded'
				},
				files: conf.css_files_compile
			}
		},


		// CSS - Automaticaly create prefixed attributes in css file if needed.
		//       e.g. add `-webkit-border-radius` if `border-radius` is used.
		autoprefixer: {
			options: {
				browsers: ['last 2 version', 'ie 8', 'ie 9'],
				diff: false
			},
			single_file: {
				files: [{
					expand: true,
					src: ['**/*.css', '!**/*.min.css'],
					cwd: 'assets/css/',
					dest: 'assets/css/',
					ext: '.css',
					extDot: 'last',
					flatten: false
				}]
			}
		},


		// CSS - Required for CSS-autoprefixer and maybe some SCSS function.
		compass: {
			options: {
			},
			server: {
				options: {
					debugInfo: true
				}
			}
		},


		// CSS - Minify all .css files.
		cssmin: {
			options: {
				banner: '/*! <%= pkg.title %> - v<%= pkg.version %>\n' +
					' * <%= pkg.homepage %>\n' +
					' * Copyright (c) <%= grunt.template.today("yyyy") %>;' +
					' * Licensed GPLv2+' +
					' */\n'
			},
			minify: {
				expand: true,
				src: ['*.css', '!*.min.css'],
				cwd: 'assets/css/',
				dest: 'assets/css/',
				ext: '.min.css',
				extDot: 'last'
			}
		},


		// WATCH - Watch filesystem for changes during development.
		watch:  {
			sass: {
				files: ['assets/sass/**/*.scss'],
				tasks: ['sass', 'autoprefixer'],
				options: {
					debounceDelay: 500
				}
			},

			scripts: {
				files: ['assets/js/src/**/*.js', 'assets/js/vendor/**/*.js'],
				tasks: ['jshint', 'concat'],
				options: {
					debounceDelay: 500
				}
			}
		},


		// BUILD - Remove previous build version and temp files.
		clean: {
			temp: {
				src: [
					'**/*.tmp',
					'**/.afpDeleted*',
					'**/.DS_Store',
				],
				dot: true,
				filter: 'isFile'
			},
			release_pro: {
				src: [
					'release/<%= pkg.version %>-pro/',
					'release/<%= pkg.name %>-pro-<%= pkg.version %>.zip',
				],
			},
			release_free: {
				src: [
					'release/<%= pkg.version %>-free/',
					'release/<%= pkg.name %>-free-<%= pkg.version %>.zip',
				],
			},
			pro: conf.plugin_branches.exclude_pro,
			free: conf.plugin_branches.exclude_free
		},


		// BUILD - Copy all plugin files to the release subdirectory.
		copy: {
			pro: {
				src: conf.plugin_branches.include_files,
				dest: 'release/<%= pkg.version %>-pro/'
			},
			free: {
				src: conf.plugin_branches.include_files,
				dest: 'release/<%= pkg.version %>-free/'
			},
		},


		// BUILD - Create a zip-version of the plugin.
		compress: {
			pro: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>-pro-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>-pro/',
				src: [ '**/*' ],
				dest: conf.dev_plugin_dir
			},
			free: {
				options: {
					mode: 'zip',
					archive: './release/<%= pkg.name %>-free-<%= pkg.version %>.zip'
				},
				expand: true,
				cwd: 'release/<%= pkg.version %>-free/',
				src: [ '**/*' ],
				dest: conf.dev_plugin_dir
			},
		},

		// BUILD - update the translation index .po file.
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: conf.translation.pot_dir,
					exclude: conf.translation.ignore_files,
					mainFile: conf.dev_plugin_file,
					potFilename: conf.translation.textdomain + '.pot',
					potHeaders: {
						poedit: true, // Includes common Poedit headers.
						'x-poedit-keywordslist': true // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin',
					updateTimestamp: true,
					updatePoFiles: true
				}
			}
		},

        potomo: {
            dist: {
                options: {
                    poDel: false
                },
                files: [{
                    expand: true,
                    cwd: conf.translation.pot_dir,
                    src: ['*.po'],
                    dest: conf.translation.pot_dir,
                    ext: '.mo',
                    nonull: true
                }]
            }
        },

		// BUILD: Replace conditional tags in code.
		replace: {
			pro: {
				options: {
					patterns: conf.plugin_patterns.pro
				},
				files: [conf.plugin_patterns.files]
			},
			free: {
				options: {
					patterns: conf.plugin_patterns.free
				},
				files: [conf.plugin_patterns.files]
			}
		},

		// BUILD: Git control (check out branch).
		gitcheckout: {
			pro: {
				options: {
					verbose: true,
					branch: conf.plugin_branches.pro,
					overwrite: true
				}
			},
			free: {
				options: {
					branch: conf.plugin_branches.free,
					overwrite: true
				}
			},
			base: {
				options: {
					branch: conf.plugin_branches.base
				}
			}
		},

		// BUILD: Git control (add files).
		gitadd: {
			pro: {
				options: {
				verbose: true, all: true }
			},
			free: {
				options: { all: true }
			},
		},

		// BUILD: Git control (commit changes).
		gitcommit: {
			pro: {
				verbose: true,
				options: {
					message: 'Built from: ' + conf.plugin_branches.base,
					allowEmpty: true
				},
				files: { src: ['.'] }
			},
			free: {
				options: {
					message: 'Built from: ' + conf.plugin_branches.base,
					allowEmpty: true
				},
				files: { src: ['.'] }
			},
		},

		checktextdomain: {
			options: {
				text_domain: [ 'custom-sidebars', 'wdev_frash', 'wpmudev' ],
				keywords: [ //List keyword specifications
					'__:1,2d',
					'_e:1,2d',
					'_x:1,2c,3d',
					'esc_html__:1,2d',
					'esc_html_e:1,2d',
					'esc_html_x:1,2c,3d',
					'esc_attr__:1,2d', 
					'esc_attr_e:1,2d', 
					'esc_attr_x:1,2c,3d', 
					'_ex:1,2c,3d',
					'_n:1,2,4d', 
					'_nx:1,2,4c,5d',
					'_n_noop:1,2,3d',
					'_nx_noop:1,2,3c,4d'
				]
			},
			files: {
				src: ['inc/**/*.php', 'views/**/*.php', '!inc/external/wpmu-lib/inc/class-thelib*', ],
				expand: true,
			},
		},

	} );

	// Test task.
	grunt.registerTask( 'hello', 'Test if grunt is working', function() {
		grunt.log.subhead( 'Hi there :)' );
		grunt.log.writeln( 'Looks like grunt is installed!' );
	});

	// Plugin build tasks
	grunt.registerTask( 'build', 'Run all tasks.', function(target) {
		var build = [], i, branch;

		if ( target ) {
			build.push( target );
		} else {
			build = ['pro', 'free'];
		}

		// Run the default tasks (js/css/php validation).
		grunt.task.run( 'default' );

		// Generate all translation files (same for pro and free).
		grunt.task.run( 'makepot' );
		grunt.task.run( 'potomo' );

		for ( i in build ) {
			branch = build[i];
			grunt.log.subhead( 'Update product branch [' + branch + ']...' );

			// Checkout the destination branch.
			grunt.task.run( 'gitcheckout:' + branch );

			// Remove code and files that does not belong to this version.
			grunt.task.run( 'replace:' + branch );
			grunt.task.run( 'clean:' + branch );

			// Add the processes/cleaned files to the target branch.
			grunt.task.run( 'gitadd:' + branch );
			grunt.task.run( 'gitcommit:' + branch );

			// Create a distributable zip-file of the plugin branch.
			grunt.task.run( 'clean:release_' + branch );
			grunt.task.run( 'copy:' + branch );
			grunt.task.run( 'compress:' + branch );

			grunt.task.run( 'gitcheckout:base');
		}
	});

	// Default task.

	grunt.registerTask( 'default', ['clean:temp', 'jshint', 'concat', 'uglify', 'sass', 'autoprefixer', 'cssmin', 'checktextdomain', 'makepot', 'potomo' ] );
	//grunt.registerTask( 'test', ['phpunit', 'jshint'] );

	grunt.task.run( 'clear' );
	grunt.util.linefeed = '\n';
};
