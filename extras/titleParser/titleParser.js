var consolrTitleParser = {};
(function() {
    var format = '<p><strong>$who</strong> <em>at the $where_loc, $where_city ($when)</em></p>';
    var titleRE = /^(.*?)\s([-\u2013|~@]|attends|arrives)/;

    var months = ['', 'January',
                  'February',
                  'March',
                  'April',
                  'May',
                  'June',
                  'July',
                  'August',
                  'September',
                  'October',
                  'November',
                  'December'];
    var monthsShort = [];
    monthsShort['jan'] = 'January';
    monthsShort['feb'] = 'February';
    monthsShort['mar'] = 'March';
    monthsShort['apr'] = 'April';
    monthsShort['may'] = 'May';
    monthsShort['jun'] = 'June';
    monthsShort['jul'] = 'July';
    monthsShort['aug'] = 'August';
    monthsShort['sep'] = 'September';
    monthsShort['oct'] = 'October';
    monthsShort['nov'] = 'November';
    monthsShort['dec'] = 'December';

    var cities = [];
    cities['LA'] = 'Los Angeles';
    cities['L.A'] = 'Los Angeles';
    cities['L.A.'] = 'Los Angeles';
    cities['NY'] = 'New York';
    cities['N.Y.'] = 'New York';
    cities['NYC'] = 'New York City';

    var trimLeft = /^\s+/, trimRight = /\s+$/;

    function trim(text) {
        return text == null ? "" :
            text.toString().replace(trimLeft, "").replace(trimRight, "");
    }

    /**
     * Fill parseInfo with day, month, year, matched
     */
    function parseDate(title, parseInfo) {
        var day;
        var month;
        var year;
        var yearStr;

        // handle dates in the form Jan 10, 2010 or January 10 2010 or Jan 15
        m = title.match(/[-,]\s+\(?(jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)[^0-9]*([0-9]*)[^0-9]*([0-9]*)\)?.*$/i);
        if (m && m[1]) {
            day = parseInt(m[2], 10);
            month = monthsShort[m[1].toLowerCase()];
            if (m.length == 4 && m[3]) {
                year = parseInt(m[3], 10);
                yearStr = m[3];
            } else {
                year = new Date().getFullYear();
                yearStr = '' + year;
            }
        } else {
            // handle dates in the form dd/dd/dd?? or (dd/dd/??)
            var m = title.match(/\(?([0-9]{2}).([0-9]{1,2}).([0-9]{2,4})\)?$/);
            if (m && m[1]) {
                day = parseInt(m[1], 10);
                month = parseInt(m[2], 10);
                year = parseInt(m[3], 10);
                yearStr = m[3];
                if (month > 12) {
                    var tmp = month;
                    month = day;
                    day = tmp;
                }
                month = months[month];
            }
        }
        // day can be not present for example "New York City, January 11"
        parseInfo.day  = day;
        parseInfo.month = month;
        parseInfo.year = year < 2000 ? '20' + yearStr : yearStr;
        parseInfo.matched = m;
    };

    /**
     * parseInfo is {who, when, where_loc, where_city, tags};
     */
    this.parseTitle = function(title, parseInfo) {
        parseInfo.who = '***';
        parseInfo.where_loc = '***';
        parseInfo.where_city = '***';
        parseInfo.tags = '***';

        title = title.replace(/\u2013/g, '-')
                    .replace(/\u2018/g, '\'')
                    .replace(/\u2019/g, '\'')
                    .replace(/\u201C/g, '"')
                    .replace(/\u201D/g, '"')
                    .replace(/í/g, 'i');

        var m = title.match(titleRE);
        var start = 0;
        if (m && m[1]) {
          parseInfo.who = m[1];
          start = m.index + m[0].length;
        }
        parseDate(title, parseInfo);
        var loc = parseInfo.matched ? title.substring(start, parseInfo.matched.index) : title.substring(start);
        // city names can be multi words so allow whitespaces
        m = loc.match(/\s*(.*?)\s+in\s+([a-z. ]*)/i);
        if (m && m[1]) {
            parseInfo.where_loc = m[1];
            var city = trim(m[2]);
            parseInfo.where_city = cities[city.toUpperCase()];
            if (typeof(parseInfo.where_city) == 'undefined') {
                parseInfo.where_city = city;
            }
        } else {
            parseInfo.where_loc = loc;
        }
        parseInfo.where_loc = parseInfo.where_loc.replace(/[^a-z]*$/i, '');

        var when = '';
        if (!isNaN(parseInfo.day)) {
            when = parseInfo.day + ' ';
        };
        when += parseInfo.month + ', ' + parseInfo.year;

        parseInfo.who = trim(parseInfo.who);
        parseInfo.where_loc = trim(parseInfo.where_loc);
        parseInfo.where_city = trim(parseInfo.where_city);
        parseInfo.when = trim(when);
        parseInfo.tags = parseInfo.who + ', ' + trim(parseInfo.where_loc.replace(/[0-9]*(st|nd|rd|th)?/, '').replace(/"|'/g, ''));

        return format
            .replace('$who', parseInfo.who)
            .replace('$where_loc', parseInfo.where_loc)
            .replace('$where_city', parseInfo.where_city)
            .replace('$when', when);
    };
    
    this.fill = function() {
        var url = document.getElementById('url');
        var caption = document.getElementById('caption');
        var tags = document.getElementById('tags');

        if (!url || !caption || !tags) return;
        var parseInfo = {};
        var title = url.value.replace(/(\r\n|\r|\n)+/g, "");
        caption.value = this.parseTitle(title, parseInfo);
        tags.value = parseInfo.tags;
    };
}).apply(consolrTitleParser);
consolrTitleParser.fill();