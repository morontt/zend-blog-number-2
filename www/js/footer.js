SyntaxHighlighter.all();

$(function () {
    if (document.head) {
        var href_cache = {};

        $('a.reactive').hover(function () {
            var keys = $(this).attr('href').match(/^[^#]+/);
            var url = keys[0];

            if (href_cache[url] === undefined) {
                href_cache[url] = true;

                var link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = url;
                document.head.appendChild(link);
            }
        });
    }
});
