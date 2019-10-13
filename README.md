# Syndication for [Flarum](https://flarum.org)

Brings RSS and Atom feeds to Flarum.

### Installation

```bash
composer require amaurycarrade/flarum-ext-syndication
```

### Usage

This extension adds the following feeds to Flarum:

- `/atom`: feed with the last discussions with activity (the `/all` page as an Atom feed);
- `/adom/d`: feed with the newly created discussions in the forum;
- `/atom/t/tag`: feed with the last discussions in the given tag (the `/t/tag` page as an Atom feed);
- `/atom/t/tag/d`: feed with the newly created discussions in the given tag;
- `/atom/d/21-discussion-slug`: feed with the recent posts in the given discussion.

You can replace `atom` by `rss` in the URLs above to get RSS feeds instead. The tags-related feeds are only available if the tags extension is installed and enabled.

Feeds are linked in the pages for autodiscovery and listing in the browser (at least in Firefox, as Chrome, Vivaldi, and a lot of others doesn't support syndication feeds out of the box). This said, they are not dynamically updated as the page change (except when fully reloaded), becuse of this [Firefox bug](https://bugzilla.mozilla.org/show_bug.cgi?id=380639). If a workaround is found, they will be.
