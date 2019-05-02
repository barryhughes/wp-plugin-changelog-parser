# WordPress Plugin Changelog Parser [![Build Status](https://travis-ci.com/barryhughes/wp-plugin-changelog-parser.svg?branch=master)](https://travis-ci.com/barryhughes/wp-plugin-changelog-parser)

A very simple tool that can parses a WordPress plugin readme.txt file and returns the changelog data. All you need to 
do, in theory, is pass the path to the readme file.

```php
use Harry_Bewes\WordPress\Changelog_Parser;

require 'vendor/autoload.php';

$changelog = ( new Changelog_Parser( '/path/to/plugin/readme.txt' ) )->get_changelog(); 
```

In return, you will receive an array-representation of the changelog. Each key is the version number and the value is an
object with properties `date` and `entries` (which is an array of individual changelog entries).

```
[
    # An associative array where each key is the version number and
    # each value is a stdClass object
    '2.0' => {
        # The date property will always be set and will always be a string,
        # but may be empty
        'date'    => '2019-07-01',
        
        # The entries property will always be set and will always be an array,
        # but may be empty
        'entries' => [
            'Added support for foo',
            'Fixed bar',
        ]
    },
    # more versions...
]
```

### Caveats & Notes

* This is a quick-and-dirty get-the-job-done sort of library
* It expects changelog files to be formatted in a very specific way, see the example in the test directory
* Use at your own risk!
* License: [GPL-3.0](https://www.gnu.org/licenses/gpl-3.0.en.html)
