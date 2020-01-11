module.exports = function (grunt) {
  // From TWBS
  RegExp.quote = function (string) {
    return string.replace(/[-\\^$*+?.()|[\]{}]/g, '\\$&');
  };

  // Project configuration.
  grunt.initConfig({

    // Metadata.
    pkg: grunt.file.readJSON('package.json'),
    banner: '/*!\n' +
    ' * Bootstrap-select v<%= pkg.version %> (<%= pkg.homepage %>)\n' +
    ' *\n' +
    ' * Copyright 2012-<%= grunt.template.today(\'yyyy\') %> SnapAppointments, LLC\n' +
    ' * Licensed under <%= pkg.license %> (https://github.com/snapappointments/bootstrap-select/blob/master/LICENSE)\n' +
    ' */\n',

    // Task configuration.

    clean: {
      css: 'dist/css',
      js: 'dist/js',
      docs: 'docs/docs/dist'
    },

    eslint: {
      options: {
        configFile: 'js/.eslintrc.json'
      },
      gruntfile: {
        options: {
          'envs': [
            'node'
          ]
        },
        src: 'Gruntfile.js'
      },
      main: {
        src: 'js/*.js'
      },
      i18n: {
        src: 'js/i18n/*.js'
      }
    },

    concat: {
      options: {
        stripBanners: true,
        sourceMap: true
      },
      main: {
        src: 'js/<%= pkg.name %>.js',
        dest: 'dist/js/<%= pkg.name %>.js',
        options: {
          banner: '<%= banner %>\n' + grunt.file.read('js/umd-intro.js'),
          footer: grunt.file.read('js/umd-outro.js')
        }
      },
      i18n: {
        expand: true,
        src: '<%= eslint.i18n.src %>',
        dest: 'dist/',
        options: {
          banner: '<%= banner %>\n' + grunt.file.read('js/umd-intro.js'),
          footer: grunt.file.read('js/umd-outro.js')
        }
      }
    },

    uglify: {
      options: {
        banner: '<%= banner %>',
        output: {
          ascii_only: true
        },
        preserveComments: function (node, comment) {
          return /^!|@preserve|@license|@cc_on/i.test(comment.value);
        }
      },
      main: {
        src: '<%= concat.main.dest %>',
        dest: 'dist/js/<%= pkg.name %>.min.js',
        options: {
          sourceMap: true,
          sourceMapIncludeSources: true,
          sourceMapIn: 'dist/js/<%= pkg.name %>.js.map'
        }
      },
      i18n: {
        expand: true,
        src: 'dist/<%= eslint.i18n.src %>',
        ext: '.min.js'
      }
    },

    less: {
      options: {
        strictMath: true,
        sourceMap: true,
        outputSourceFiles: true,
        sourceMapURL: '<%= pkg.name %>.css.map',
        sourceMapFilename: '<%= less.css.dest %>.map'
      },
      css: {
        src: 'less/bootstrap-select.less',
        dest: 'dist/css/<%= pkg.name %>.css'
      }
    },

    usebanner: {
      css: {
        options: {
          banner: '<%= banner %>'
        },
        src: '<%= less.css.dest %>'
      }
    },

    copy: {
      docs: {
        expand: true,
        cwd: 'dist/',
        src: [
          '**/*'
        ],
        dest: 'docs/docs/dist/'
      }
    },

    cssmin: {
      options: {
        compatibility: 'ie8',
        keepSpecialComments: '*',
        advanced: false
      },
      css: {
        src: '<%= less.css.dest %>',
        dest: 'dist/css/<%= pkg.name %>.min.css'
      }
    },

    csslint: {
      options: {
        'adjoining-classes': false,
        'box-sizing': false,
        'box-model': false,
        'compatible-vendor-prefixes': false,
        'floats': false,
        'font-sizes': false,
        'gradients': false,
        'important': false,
        'known-properties': false,
        'outline-none': false,
        'qualified-headings': false,
        'regex-selectors': false,
        'shorthand': false,
        'text-indent': false,
        'unique-headings': false,
        'universal-selector': false,
        'unqualified-attributes': false,
        'overqualified-elements': false
      },
      css: {
        src: '<%= less.css.dest %>'
      }
    },

    version: {
      js: {
        options: {
          prefix: 'Selectpicker.VERSION = \''
        },
        src: [
          'js/<%= pkg.name %>.js'
        ]
      },
      docs: {
        options: {
          prefix: '<%= pkg.name %>/archive/v',
          replace: '[0-9a-zA-Z\\-_\\+\\.]+)([^/]+(?=\.zip+)'
        },
        src: [
          'README.md',
          'docs/docs/index.md'
        ]
      },
      cdn: {
        options: {
          prefix: 'npm/<%= pkg.name %>@'
        },
        src: [
          'README.md',
          'docs/docs/index.md'
        ]
      },
      nuget: {
        options: {
          prefix: '<version>'
        },
        src: [
          'nuget/bootstrap-select.nuspec'
        ]
      },
      default: {
        options: {
          prefix: '[\'"]?version[\'"]?:[ "\']*'
        },
        src: [
          'docs/mkdocs.yml',
          'package.json'
        ]
      }
    },

    postcss: {
      options: {
        map: true,
        processors: [
          require('autoprefixer')()
        ]
      },
      css: {
        src: '<%= less.css.dest %>'
      }
    },

    compress: {
      zip: {
        options: {
          archive: 'bootstrap-select-<%= pkg.version %>.zip',
          mode: 'zip'
        },
        files: [
          {
            expand: true,
            cwd: 'dist/',
            src: '**',
            dest: 'bootstrap-select-<%= pkg.version %>/'
          }, {
            src: ['bower.json', 'composer.json', 'package.json'],
            dest: 'bootstrap-select-<%= pkg.version %>/'
          }
        ]
      }
    },

    watch: {
      gruntfile: {
        files: '<%= eslint.gruntfile.src %>',
        tasks: 'eslint:gruntfile'
      },
      js: {
        files: ['<%= eslint.main.src %>', '<%= eslint.i18n.src %>'],
        tasks: 'build-js'
      },
      less: {
        files: 'less/*.less',
        tasks: 'build-css'
      }
    }
  });

  // These plugins provide necessary tasks.
  require('load-grunt-tasks')(grunt, {
    scope: 'devDependencies'
  });

  // Version numbering task.
  // to update version number, use grunt version::x.y.z

  // CSS distribution
  grunt.registerTask('build-css', ['clean:css', 'less', 'postcss', 'usebanner:css', 'cssmin']);

  // JS distribution
  grunt.registerTask('build-js', ['clean:js', 'eslint', 'concat', 'uglify']);

  // Copy dist to docs
  grunt.registerTask('copy-docs', ['clean:docs', 'copy:docs']);

  // Build CSS & JS
  grunt.registerTask('build', ['build-css', 'build-js']);

  // Development watch
  grunt.registerTask('dev-watch', ['build', 'watch']);

  // Full distribution
  grunt.registerTask('dist', ['build', 'compress', 'copy-docs']);

  // Default task.
  grunt.registerTask('default', ['build-css', 'build-js']);
};
