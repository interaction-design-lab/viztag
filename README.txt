viztag

let multiple coders add tags and comments to the unit of analysis: a
qualitative coding tool

install
-------

viztag requires PHP\>=5.3.2 and depends on the [silex microframework][].
To install silex, simply run `composer install` from the viztag project
root; [composer][] will install everything described in
`./composer.json` under `/vendor`.

You can then upload the entire project, including `/vendor`, to your
webserver.

Files needed to create the database and fill it with sample data are in
`/fixtures`.

[silex microframework]: http://silex.sensiolabs.org
[composer]: http://github.com/composer/composer

use
---

Coder work flow is as follows:

1. coder logs in (`/login`)
2. coder heads to `/tag` where they are presented with a random status image
3. coder can apply multiple tags (see below for details), and add an optional comment
4. on submit, tag(s) and comment are saved, and `/tag` is reloaded
5. coder logs out (`/logout`)

Tags are namespaced, in the form `<namespace>:<tag>`; in the future we may
allow values as well a la `<namespace>:<predicate>=<value>`, but not now. The
tagging interface should let coders type in tags, offer autocomplete, and
displays many common tags in a list on a sidebar.

[visualsearch]: http://documentcloud.github.com/visualsearch

future
------

- keep documentation (this doc) up to date (phil, ben)
- silex documentation read (ben)
- front end (ben)
- hook them up (ben)
