define(['jquery'], function ($) {

    var convertFormat = function (format, type) {
        var dateTimeFormat = {
            date: {
                'EEEE': 'DD',
                'EEE': 'D',
                'EE': 'D',
                'E': 'D',
                'D': 'o',
                'MMMM': 'MM',
                'MMM': 'M',
                'MM': 'mm',
                'M': 'mm',
                'yyyy': 'yy',
                'y': 'yyyy',
                'Y': 'yyyy',
                'yy': 'yyyy' // Always long year format on frontend
            },
            time: {
                'a': 'TT'
            }
        };
        var symbols = format.match(/([a-z]+)/ig),
            separators = format.match(/([^a-z]+)/ig),
            self = this,
            convertedFormat = '';

        if (symbols) {
            $.each(symbols, function (key, val) {
                convertedFormat +=
                    (dateTimeFormat[type][val] || val) +
                    (separators[key] || '');
            });
        }

        return convertedFormat;
    }

    return convertFormat;
});