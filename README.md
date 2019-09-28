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

The difference between this fork and the original is that this fork has tweaked a couple things to ensure the feeds validate.
