viztag
======

let multiple coders add tags and comments to the unit of analysis: a
qualitative coding tool.

install
-------

On the server, viztag requires PHP\>=5.3.2 and depends on the [silex
microframework][].  To install silex, simply run `composer install` from
the viztag project root; [composer][] will install everything described
in `./composer.json` under `/vendor`. It's best to set all this up on
your localhost, and then upload the project, including `/vendor`, to the
production server.

Files needed to create the database and fill it with sample data are in
`/fixtures`.

On the front end, viztag uses some fantastic open-source projects:
[bootstrap][] for layout and CSS, bootstrap's [typeahead][] project for
tag autosuggest, ...

[silex microframework]: http://silex.sensiolabs.org
[composer]: http://github.com/composer/composer
[bootstrap]: http://twitter.github.com/bootstrap
[typeahead]: http://twitter.github.com/bootstrap/javascript.html#typeahead

configure
---------

coming soon...

use
---

Coder work flow is as follows:

1. coder logs in (`/login`)
2. coder heads to `/tag` where they are presented with a random status
   image
3. coder applies multiple tags (see below for details), and add an
   optional comment
4. on submit, tag(s) and comment are saved, and `/tag` is reloaded
5. coder logs out (`/logout`)

Tags are namespaced, in the form `<namespace>:<tag>`. The tagging interface
requires coders to select one tag per namespace; we use a list of select
elements. Note that if you pass a GET param `l33t=1` to `/tag`, you can also
type these categories into a [typeahead][]-enabled text input box.

future
------

- add 1 pull-down per namespace (force response per namespace)
- add null entry for each category
- views for coder descrepancy
- views for all images for tag set (1 or more tags)
- views for all tags per image (done, /taggings)
- rename verastatuses / vs_id to statuses, status_id

- keep documentation (this doc) up to date (phil, ben)
- silex documentation read (ben)
- dump db table tags into json list e.g. ["namespace:tag",
  "namespace:tag"] etc. at `/tags` (ben)
- front end (ben)
- hook them up (ben)
