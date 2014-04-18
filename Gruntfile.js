module.exports = function(grunt) {

// Load multiple grunt tasks using globbing patterns
require('load-grunt-tasks')(grunt);

// Project configuration.
grunt.initConfig({
  pkg: grunt.file.readJSON('package.json'),

    makepot: {
      target: {
        options: {
          domainPath: '/languages/',    // Where to save the POT file.
          mainFile: 'theme-info.php',    // Main project file.
          potFilename: 'theme_info.pot',    // Name of the POT file.
          type: 'wp-plugin',    // Type of project (wp-plugin or wp-theme).
          processPot: function( pot, options ) {
            pot.headers['report-msgid-bugs-to'] = 'https://wp-translations.org/';
            pot.headers['plural-forms'] = 'nplurals=2; plural=n != 1;';
            pot.headers['last-translator'] = 'WP-Translations (http://wp-translations.org/)\n';
            pot.headers['language-team'] = 'WP-Translations (http://www.transifex.com/projects/p/wp-translations/)\n';
            pot.headers['x-poedit-basepath'] = '.\n';
            pot.headers['x-poedit-language'] = 'English\n';
            pot.headers['x-poedit-country'] = 'UNITED STATES\n';
            pot.headers['x-poedit-sourcecharset'] = 'utf-8\n';
            pot.headers['x-poedit-keywordslist'] = '__;_e;__ngettext:1,2;_n:1,2;__ngettext_  noop:1,2;_n_noop:1,2;_c,_nc:4c,1,2;_x:1,2c;_ex:1,2c;_nx:4c,1,2;_nx_noop:4c,1,2;\n';
            pot.headers['x-textdomain-support'] = 'yes\n';
            return pot;
          }
        }
      }
    },

    exec: {
      npmUpdate: {
        command: 'npm update'
      },
      update_po: { // Pull Transifex translation - grunt exec:update_po
        cmd: 'tx pull -a --minimum-perc=100' // Change the percentage as you wish
      }
      tx_push_s: { // Push pot to Transifex - grunt exec:tx_push_s
        cmd: 'tx push -s'
      },
    },

    po2mo: {
      files: {
        src: 'languages/*.po',
        expand: true,
      },
    }

});

// Default task.
grunt.registerTask( 'default', 'exec:npmUpdate' );

// Makepot task
grunt.registerTask( 'Makepot', 'makepot' );

// Makepot and push it on Transifex task(s).
grunt.registerTask( 'MakandPush', [ 'makepot', 'tx_push_s' ] );

// Pull from Transifex and create .mo task(s).
grunt.registerTask( 'tx', [ 'exec:update_po', 'po2mo' ] );

};