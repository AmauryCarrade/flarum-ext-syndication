# Syndication for [Flarum](https://flarum.org)

Brings RSS and Atom feeds to Flarum.

## Note: This fork is no longer maintained. Check [imorland/syndication](https://github.com/imorland/syndication) for a version compatible with flarum 1.0.0 and up.

### Installation

```bash
composer require amaurycarrade/flarum-ext-syndication
```

### Usage

This extension adds the following feeds to Flarum:

- `/atom`: feed with the last discussions with activity (the `/` page as an Atom feed);
- `/adom/discussions`: feed with the newly created discussions in the forum;
- `/atom/t/tag`: feed with the last discussions in the given tag (the `/t/tag` page as an Atom feed);
- `/atom/t/tag/discussions`: feed with the newly created discussions in the given tag;
- `/atom/d/21-discussion-slug`: feed with the recent posts in the given discussion.

You can replace `atom` by `rss` in the URLs above to get RSS feeds instead. The tags-related feeds are only available if the tags extension is installed and enabled.

You can also add `?sort=latest|top|newest|oldest` to the discussions lists feeds to sort the feed, and `?q=<search>` to filter it. Or both using `?sort=<sorting>&q=<search>`.

Feeds are linked in the pages for autodiscovery. This said, they are not dynamically updated as the page change (except when fully reloaded), earlier because of this [Firefox bug](https://bugzilla.mozilla.org/show_bug.cgi?id=380639), and now because even Firefox no longer display RSS feeds in the browser.
