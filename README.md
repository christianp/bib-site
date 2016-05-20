# bib-site

This is a set of PHP scripts which provide an interface to browse and edit a .bib file. Everything happens in the .bib file - no SQL database or anything like that needed.

Even if you don't need that, you might like `bib-parse.php`, which does a better job of parsing .bib files (or at least, *my* .bib files) than any other PHP parser I found.

I use this to run [read.somethingorotherwhatever.com](http://read.somethingorotherwhatever.com)

## Installation

Clone this repository somewhere on your web server. Copy `config.json.dist` to `config.json` and set it up. You'll need to change `bibfile`,`root_url` and `password`. Use PHP's `crypt()` function to hash your password. (My web host seems to run an old version of PHP that doesn't have the good hashing functions)

### Bookmarklet

Add the following bookmarklet to save the page you're looking at:

```
javascript:(function(){var site = 'http://read.somethingorotherwhatever.com'; var d = {'title':document.title,'url':window.location};var p=[];for(var x in d){p.push(x+'='+encodeURIComponent(d[x]))};var url = site+'/new?'+p.join('&');window.open(url)})()
```

(Remember to change `site` to your bib-site address)

## Customisation

Make a copy the `templates` folder and change your config to point to that. The templates are rendered using [twig](http://twig.sensiolabs.org/).
