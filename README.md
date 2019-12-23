# Tags for TYPO3 v9+

Allow editing and adding lightweight tags for any kind of record to identify records easily.

Characteristics of tags:
- Non-translateable - the same tag for all languages
- Reuse tags by suggesting existing tags


## Installation

Use `composer req b13/tax` or download the package from the official TYPO3 Extension Repository.

You need TYPO3 v9 or later for this extension to work.

## Configuration

For enabling tags in your TCA table of TYPO3, configure it like this in your database table (`Configuration/TCA/tx_my_table.php`).

    'keywords' => [
        'label' => 'Keywords',
        'config' => (new \B13\Tax\TcaHelper)->buildFieldConfiguration('tx_my_table', 'keywords')
    ]

As all tags are stored in `sys_tag` and all its relations within `sys_tag_mm`, you're on your own whatever
you want to do with tags in your system, however multiple functionality might be added later-on.

## Known bugs
* JavaScript does not care about the ordering of the tags in a list, which would be really cool. This stems
from the fact that the JavaScript library (originally taken from Bootstrap-TagsInput)

## Missing features
* Permission handling: Make certain tags "read-only" for editors, so they can not remove specific tags from a record.
* Allow to only search for tags on a per-pid basis
* Allow to configure the "pid"
* Allow numeric tag names
* Limit max number of tags for a specific field
* Use LLL labels

## License

The extension is licensed under GPL v2+, same as the TYPO3 Core. For details see the LICENSE file in this repository.

## Credits

This extension was created by [Benni Mack](https://github.com/bmack) in 2019 for [b13 GmbH](https://b13.com).

* Typeahead functionality from https://github.com/bassjobsen/Bootstrap-3-Typeahead
* https://github.com/hrobertson/bootstrap-tagsinput/ for Bootstrap Tags-Input

For Bootstrap 4, we might use https://github.com/Nodws/bootstrap4-tagsinput
