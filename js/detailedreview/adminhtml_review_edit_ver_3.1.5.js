document.observe("dom:loaded", function() {
    'use strict';

    var $pros = $('pros'),
        $cons = $('cons');

    updateState($pros);
    updateState($cons);

    bindClick($pros);
    bindClick($cons);

    function updateState($multiSelect) {
        if ($multiSelect) {
            $multiSelect.select('option').forEach(function (item) {
                if (item.selected == true) {
                    disableItem(getOpposite(item.parentElement.id), item.text)
                }
            });
        }
    }

    function bindClick($multiSelect) {
        if ($multiSelect) {
            $multiSelect.observe('click', function () {
                validateProsConsOptions(this.id);
            });
        }

    }

    function validateProsConsOptions(type) {
        var $selectedItems = [];

        $(type).select('option:selected').each(function (item) {
            $selectedItems.push(item.text);
        });

        $(getOpposite(type)).select('option').each(function (item) {
            item.disabled = $selectedItems.indexOf(item.text) > -1;
        })
    }

    function disableItem(type, text) {
        $(type).select('[option:contains(' + text + ')')[0].disabled = true;
    }

    function getOpposite(type) {
        if (type == 'cons' || type == 'pros') {
            return type == 'cons' ? 'pros' : 'cons';
        }

        return false;
    }

    Validation.addAllThese([
        ['validate-youtube-url', 'Please enter a valid Youtube URL.', function (v) {
            v = (v || '').replace(/^\s+/, '').replace(/\s+$/, '');
            return Validation.get('IsEmpty').test(v) || /^(?:https?:\/\/)?(?:(?:www|m)\.)?(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/i.test(v)
        }],

        ['not-url', 'You can not use URL here.', function (v) {
            return Validation.get('IsEmpty').test(v) ||  !(/(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/.test(v))
        }],
        ['pros-cons', 'Please use only letters or numbers, separated by comma.', function (v) {
            return Validation.get('IsEmpty').test(v) ||  /^([a-zA-Z0-9 ,!\#\$%&'\*\+\/=\?\^_`\{\|\}~-]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+$/.test(v)
        }],
    ]);
});
