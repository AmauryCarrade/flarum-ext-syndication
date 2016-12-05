'use strict';

System.register('amaurycarrade/flarum-ext-syndication/main', ['flarum/extend', 'flarum/components/Page', 'flarum/components/DiscussionPage', 'flarum/components/IndexPage', 'flarum/components/UserPage', 'flarum/tags/components/TagsPage'], function (_export, _context) {
    "use strict";

    var extend, Page, DiscussionPage, IndexPage, UserPage, TagsPage;


    function updateFeedsLinks() {
        console.log("CHANGED", app.current.props.routeName, app.current);

        resetFeedsToDefault();

        switch (app.current.props.routeName) {
            case 'index':
            case 'tags':
                break;

            case 'tag':
                console.log(app.history.getCurrent());
                addFeedLink('atom' + currentPath(), 'Last activity in tag');
                addFeedLink('atom' + currentPath() + '/d', 'Last discussions in tag');
                break;

            case 'discussion.near':
            case 'discussion':
                break;
        }
    }

    function resetFeedsToDefault() {
        clearFeedLinks();

        addFeedLink('atom', 'Last activity');
        addFeedLink('atom/d', 'Last discussions');
    }

    function currentPath() {
        return document.location.pathname;
    }

    function addFeedLink(url, title, type) {
        var feed_link_element = document.createElement('link');
        feed_link_element.setAttribute('rel', 'alternate');
        feed_link_element.setAttribute('type', type == 'rss' ? 'application/rss+xml' : 'application/atom+xml');
        feed_link_element.setAttribute('title', title);
        feed_link_element.setAttribute('href', app.forum.attribute('baseUrl') + '/' + url);

        $('head').append(feed_link_element);
    }

    function clearFeedLinks() {
        $('link[rel="alternate"][type="application/rss+xml"]').remove();
        $('link[rel="alternate"][type="aapplication/atom+xml"]').remove();
    }
    return {
        setters: [function (_flarumExtend) {
            extend = _flarumExtend.extend;
        }, function (_flarumComponentsPage) {
            Page = _flarumComponentsPage.default;
        }, function (_flarumComponentsDiscussionPage) {
            DiscussionPage = _flarumComponentsDiscussionPage.default;
        }, function (_flarumComponentsIndexPage) {
            IndexPage = _flarumComponentsIndexPage.default;
        }, function (_flarumComponentsUserPage) {
            UserPage = _flarumComponentsUserPage.default;
        }, function (_flarumTagsComponentsTagsPage) {
            TagsPage = _flarumTagsComponentsTagsPage.default;
        }],
        execute: function () {

            app.initializers.add('amaurycarrade/flarum-ext-syndication', function (app) {
                console.log(app.current, app.booted, app);

                extend(DiscussionPage.prototype, 'view', updateFeedsLinks);
                extend(IndexPage.prototype, 'view', updateFeedsLinks);
                extend(TagsPage.prototype, 'view', updateFeedsLinks);
                extend(UserPage.prototype, 'view', updateFeedsLinks);
            });
        }
    };
});