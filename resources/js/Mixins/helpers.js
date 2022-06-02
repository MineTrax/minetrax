import Autolinker from 'autolinker';
import DOMPurify from 'dompurify';

export default {
    methods: {
        secondsToHMS(s, showSeconds = false) {
            const d = Math.floor(s / (3600 * 24));
            s  -= d * 3600 * 24;
            const h = Math.floor(s / 3600);
            s  -= h * 3600;
            const m = Math.floor(s / 60);
            s  -= m * 60;
            const tmp = [];
            (d) && tmp.push(d + 'd');
            (d || h) && tmp.push(h + 'h');
            (d || h || m) && tmp.push(m + 'm');

            if (showSeconds) {
                s = Math.round(s);
                tmp.push(s + 's');
            }
            return tmp.join(' ');
        },
        purifyText(text) {
            return DOMPurify.sanitize(text);
        },
        purifyAndLinkifyText(text) {
            const purifiedText = DOMPurify.sanitize(text);
            const autoLinker = new Autolinker( {
                urls : {
                    schemeMatches : true,
                    wwwMatches    : true,
                    tldMatches    : true
                },
                email       : true,
                phone       : true,
                mention     : 'twitter',
                hashtag     : 'twitter',
                stripPrefix : true,
                stripTrailingSlash : true,
                sanitizeHtml: true,
                newWindow   : true,
                truncate : {
                    length   : 0,
                    location : 'end'
                },
                className : 'autolink',
                replaceFn : function( match ) {
                    const tag = match.getType();
                    switch (tag) {
                    case 'mention':
                        return `<a class='autolink autolink-mention' href='/@${match.getMention()}'>@${match.getMention()}</a>`;
                    }
                }
            });
            return autoLinker.link(purifiedText);
        }
    }
};
