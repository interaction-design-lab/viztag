viztag
======

let multiple coders add tags and comments to the unit of analysis: a
qualitative coding tool

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

use
---

Coder work flow is as follows:

1. coder logs in (`/login`)
2. coder heads to `/tag` where they are presented with a random status
   image
3. coder can apply multiple tags (see below for details), and add an
   optional comment
4. on submit, tag(s) and comment are saved, and `/tag` is reloaded
5. coder logs out (`/logout`)

Tags are namespaced, in the form `<namespace>:<tag>`; in the future we
may allow values as well a la `<namespace>:<predicate>=<value>`, but not
now. The tagging interface should let coders type in tags, offer
autocomplete via [typeahead][], and displays many common tags in a
list on a sidebar.

future
------

- keep documentation (this doc) up to date (phil, ben)
- silex documentation read (ben)
- dump db table tags into json list e.g. ["namespace:tag",
  "namespace:tag"] etc. at `/tags` (ben)
- front end (ben)
- hook them up (ben)
