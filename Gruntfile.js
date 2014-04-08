module.exports = function(grunt) {

require('load-grunt-tasks')(grunt);

// Project configuration.
grunt.initConfig({
  pkg: grunt.file.readJSON('package.json'),

  exec: {
    update_po:
    {
    options: {                      // Options
                stdout: true
            },
      cmd: 'tx pull -a --minimum-perc=100'
    }
  },

  po2mo: {
    files: {
      src: 'languages/*.po',
      expand: true,
    },
  }

});

// Default task(s).
grunt.registerTask( 'default', [ 'exec', 'po2mo' ] );

};