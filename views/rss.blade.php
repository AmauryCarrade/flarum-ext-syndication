<?xml version="1.0" encoding="UTF-8" ?>

<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/">

    <channel>
        <title><![CDATA[{!! $title !!}]]></title>
        <description><![CDATA[{!! $description !!}]]></description>
        <link><![CDATA[{!! $link !!}]]></link>
        <pubDate>{{ $pubDate->format(DateTime::RSS) }}</pubDate>
        <ttl>1800</ttl>

        @foreach ($entries as $entry)
        <item>
            <title><![CDATA[{!! $entry['title'] !!}]]></title>
            <description><![CDATA[{!! $entry['description'] !!}]]></description>
            <content:encoded><![CDATA[{!! $entry['content'] or $entry['description'] !!}]]></content:encoded>
            <link>{{ $entry['permalink'] }}</link>
            <guid isPermaLink="true">{{ $entry['permalink'] }}</guid>
            <pubDate>{{ $entry['pubdate'] }}</pubDate>
        </item>
        @endforeach

    </channel>

</rss>
