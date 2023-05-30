import jQuery from 'typeahead';

var defaultOptions = {
    tagClass: function(item) {
        return 'badge badge-info';
    },
    focusClass: 'focus',
    itemValue: function(item) {
        return item.value || item;
    },
    itemText: function(item) {
        return item.name || item;
    },
    itemTitle: function(item) {
        return null;
    },
    freeInput: true,
    addOnBlur: true,
    maxTags: undefined,
    maxChars: undefined,
    confirmKeys: ['Enter', ','],
    confirmOnPaste: false,
    deleteKeys: ['Backspace', 'Delete'],
    delimiter: ',',
    delimiterRegex: null,
    cancelConfirmKeysOnEmpty: false,
    onTagExists: function(item, $tag) {
        $tag.hide().fadeIn();
    },
    trimValue: true,
    allowDuplicates: false,
    triggerChange: true,
    caseSensitive: true
};

/**
 * Constructor function
 */
function TagsInput(element, items, receiverUrl) {
    let options = {
        items: items,
        itemValue: function(item) {
            return item.value || item;
        },
        typeahead: {
            minLength: 2,
            source: function(query) {
                let url = receiverUrl + '&q=' + query;
                return jQuery.getJSON(url);
            }
        }
    };
    if (typeof element == 'string') {
        element = document.getElementById(element);
    }
    this.isInit = true;
    this.itemsArray = [];

    this.$element = jQuery(element);
    this.$element.hide();

    this.isSelect = (element.tagName === 'SELECT');
    this.multiple = (this.isSelect && element.hasAttribute('multiple'));
    this.objectItems = options && options.itemValue;
    this.placeholderText = element.hasAttribute('placeholder') ? this.$element.attr('placeholder') : '';
    this.inputSize = Math.max(1, this.placeholderText.length);

    this.$container = jQuery('<div class="bootstrap-tagsinput"></div>');
    this.$input = jQuery('<input type="text" placeholder="' + this.placeholderText + '"/>').appendTo(this.$container);

    this.$element.before(this.$container);

    this.build(options);
    this.isInit = false;
}

TagsInput.prototype = {
    constructor: TagsInput,

    /**
     * Adds the given item as a new tag. Pass true to dontPushVal to prevent
     * updating the elements val()
     */
    add: function(item, dontPushVal, options) {
        var self = this;

        if (self.options.maxTags && self.itemsArray.length >= self.options.maxTags)
            return;

        // Ignore falsey values, except false
        if (item !== false && !item)
            return;

        // Trim value
        if (typeof item === "string" && self.options.trimValue) {
            item = jQuery.trim(item);
        }

        // Throw an error when trying to add an object while the itemValue option was not set
        if (typeof item === "object" && !self.objectItems)
            throw("Can't add objects when itemValue option is not set");

        // Ignore strings only containg whitespace
        if (item.toString().match(/^\s*$/))
            return;

        // If SELECT but not multiple, remove current tag
        if (self.isSelect && !self.multiple && self.itemsArray.length > 0)
            self.remove(self.itemsArray[0]);

        if (typeof item === "string" && this.$element[0].tagName === 'INPUT') {
            var delimiter = (self.options.delimiterRegex) ? self.options.delimiterRegex : self.options.delimiter;
            var items = item.split(delimiter);
            if (items.length > 1) {
                for (var i = 0; i < items.length; i++) {
                    this.add(items[i], true);
                }

                if (!dontPushVal)
                    self.pushVal(self.options.triggerChange);
                return;
            }
        }

        var itemValue = self.options.itemValue(item),
            itemText = self.options.itemText(item),
            tagClass = self.options.tagClass(item),
            itemTitle = self.options.itemTitle(item);

        // Ignore items already added
        var existing = jQuery.grep(self.itemsArray, function(item) { return self.options.caseSensitive ?  self.options.itemValue(item) === itemValue : self.options.itemValue(item).toLowerCase() === itemValue.toLowerCase();} )[0];
        if (existing && !self.options.allowDuplicates) {
            // Invoke onTagExists
            if (self.options.onTagExists) {
                var $existingTag = jQuery(".tag", self.$container).filter(function() { return jQuery(this).data("item") === existing; });
                self.options.onTagExists(item, $existingTag);
            }
            return;
        }

        // if length greater than limit
        if (self.items().toString().length + item.length + 1 > self.options.maxInputLength)
            return;

        // raise beforeItemAdd arg
        var beforeItemAddEvent = jQuery.Event('beforeItemAdd', { item: item, cancel: false, options: options});
        self.$element.trigger(beforeItemAddEvent);
        if (beforeItemAddEvent.cancel)
            return;

        // register item in internal array and map
        self.itemsArray.push(item);

        // add a tag element

        var $tag = jQuery('<span class="tag ' + htmlEncode(tagClass) + (itemTitle !== null ? ('" title="' + itemTitle) : '') + '">' + htmlEncode(itemText) + '<span data-role="remove"></span></span>');
        $tag.data('item', item);
        self.findInputWrapper().before($tag);
        $tag.after(' ');

        // Check to see if the tag exists in its raw or uri-encoded form
        var escapedItemValue = escapeString(itemValue);
        var optionExists = (
            jQuery('option[value="' + encodeURIComponent(itemValue) + '"]', self.$element).length ||
            jQuery('option[value="' + htmlEncode(escapedItemValue) + '"]', self.$element).length
        );

        // add <option /> if item represents a value not present in one of the <select />'s options
        if (self.isSelect && !optionExists) {
            var $option = jQuery('<option selected>' + htmlEncode(itemText) + '</option>');
            $option.data('item', item);
            $option.attr('value', escapedItemValue);
            self.$element.append($option);
        }

        if (!dontPushVal)
            self.pushVal(self.options.triggerChange);

        // Add class when reached maxTags
        if (self.options.maxTags === self.itemsArray.length || self.items().toString().length === self.options.maxInputLength)
            self.$container.addClass('bootstrap-tagsinput-max');

        // If using typeahead, once the tag has been added, clear the typeahead value so it does not stick around in the input.
        if (jQuery('.typeahead', self.$container).length) {
            self.$input.typeahead('val', '');
        }

        if (this.isInit) {
            self.$element.trigger(jQuery.Event('itemAddedOnInit', { item: item, options: options }));
        } else {
            self.$element.trigger(jQuery.Event('itemAdded', { item: item, options: options }));
        }
    },

    /**
     * Removes the given item. Pass true to dontPushVal to prevent updating the
     * elements val()
     */
    remove: function(item, dontPushVal, options) {
        var self = this;

        if (self.objectItems) {
            if (typeof item === "object")
                item = jQuery.grep(self.itemsArray, function(other) { return self.options.itemValue(other) ==  self.options.itemValue(item); } );
            else
                item = jQuery.grep(self.itemsArray, function(other) { return self.options.itemValue(other) ==  item; } );

            item = item[item.length-1];
        }

        if (item) {
            var beforeItemRemoveEvent = jQuery.Event('beforeItemRemove', { item: item, cancel: false, options: options });
            self.$element.trigger(beforeItemRemoveEvent);
            if (beforeItemRemoveEvent.cancel)
                return;

            jQuery('.tag', self.$container).filter(function() { return jQuery(this).data('item') === item; }).remove();
            jQuery('option', self.$element).filter(function() { return jQuery(this).data('item') === item; }).remove();
            if(jQuery.inArray(item, self.itemsArray) !== -1)
                self.itemsArray.splice(jQuery.inArray(item, self.itemsArray), 1);
        }

        if (!dontPushVal)
            self.pushVal(self.options.triggerChange);

        // Remove class when reached maxTags
        if (self.options.maxTags > self.itemsArray.length)
            self.$container.removeClass('bootstrap-tagsinput-max');

        self.$element.trigger(jQuery.Event('itemRemoved',  { item: item, options: options }));
    },

    /**
     * Removes all items
     */
    removeAll: function() {
        var self = this;

        jQuery('.tag', self.$container).remove();
        jQuery('option', self.$element).remove();

        while(self.itemsArray.length > 0)
            self.itemsArray.pop();

        self.pushVal(self.options.triggerChange);
    },

    /**
     * Refreshes the tags so they match the text/value of their corresponding
     * item.
     */
    refresh: function() {
        var self = this;
        jQuery('.tag', self.$container).each(function() {
            var $tag = jQuery(this),
                item = $tag.data('item'),
                itemValue = self.options.itemValue(item),
                itemText = self.options.itemText(item),
                tagClass = self.options.tagClass(item);

            // Update tag's class and inner text
            $tag.attr('class', null);
            $tag.addClass('tag ' + htmlEncode(tagClass));
            $tag.contents().filter(function() {
                return this.nodeType == 3;
            })[0].nodeValue = htmlEncode(itemText);

            if (self.isSelect) {
                var option = jQuery('option', self.$element).filter(function() { return jQuery(this).data('item') === item; });
                option.attr('value', itemValue);
            }
        });
    },

    /**
     * Returns the items added as tags
     */
    items: function() {
        return this.itemsArray;
    },

    /**
     * Assembly value by retrieving the value of each item, and set it on the
     * element.
     */
    pushVal: function() {
        var self = this,
            val = jQuery.map(self.items(), function(item) {
                return self.options.itemValue(item).toString();
            });

        self.$element.val(val, true);

        if (self.options.triggerChange)
            self.$element.trigger('change');
    },

    /**
     * Initializes the tags input behaviour on the element
     */
    build: function(options) {
        var self = this;

        self.options = jQuery.extend({}, defaultOptions, options);
        // When itemValue is set, freeInput should always be false
//           if (self.objectItems)
//                self.options.freeInput = false;

        makeOptionItemFunction(self.options, 'itemValue');
        makeOptionItemFunction(self.options, 'itemText');
        makeOptionFunction(self.options, 'tagClass');
        // Bootstrap-3-Typeahead (https://github.com/bassjobsen/Bootstrap-3-Typeahead)
        if (self.options.typeahead) {
            var typeahead = self.options.typeahead || {};

            makeOptionFunction(typeahead, 'source');

            self.$input.typeahead(jQuery.extend({}, typeahead, {
                source: function (query, process) {
                    function processData(data) {
                        if (!data)
                            return;

                        if (jQuery.isFunction(data.success)) {
                            // support for Angular callbacks
                            data.success(process);
                        } else if (jQuery.isFunction(data.then)) {
                            // support for Angular promises
                            data.then(process);
                        } else {
                            // support for functions and jquery promises
                            jQuery.when(data).then(process);
                        }
                    }

                    // Bloodhound (since 0.11) needs three arguments.
                    // Two of them are callback functions (sync and async) for local and remote data processing
                    // see https://github.com/twitter/typeahead.js/blob/master/src/bloodhound/bloodhound.js#L132
                    if (jQuery.isFunction(typeahead.source) && typeahead.source.length === 3) {
                        typeahead.source(query, processData, processData);
                    }
                    else {
                        // data is directly returned by source function
                        processData(typeahead.source(query));
                    }
                },
                updater: function (item) {
                    if (self.objectItems)
                        self.add(item);
                    else
                        self.add(self.options.itemText(item));
                    return '';
                }
            }));
        }

        self.$container.on('click', jQuery.proxy(function(event) {
            if (! self.$element.attr('disabled')) {
                self.$input.removeAttr('disabled');
            }
            self.$input.focus();
        }, self));

        if (self.options.addOnBlur && self.options.freeInput) {
            self.$input.on('focusout', jQuery.proxy(function(event) {
                // HACK: only process on focusout when no typeahead opened, to
                //       avoid adding the typeahead text as tag
                if (jQuery('.typeahead', self.$container).length === 0) {
                    self.add(self.$input.val());
                    self.$input.val('');
                }
            }, self));
        }

        // Toggle the 'focus' css class on the container when it has focus
        self.$container.on({
            focusin: function() {
                self.$container.addClass(self.options.focusClass);
            },
            focusout: function() {
                self.$container.removeClass(self.options.focusClass);
                var $inputWrapper = self.findInputWrapper();
                $inputWrapper.siblings().last().after($inputWrapper);
            },
        });

        self.$container.on('keydown input', 'input', jQuery.proxy(function(event) {
            var $input = jQuery(event.target),
                $inputWrapper = self.findInputWrapper();

            if (self.$element.attr('disabled')) {
                self.$input.attr('disabled', 'disabled');
                return;
            }

            switch (event.key) {
                // BACKSPACE
                case 'Backspace':
                    if(self.options.deleteKeys && keyInList(event, self.options.deleteKeys) > -1){
                        if (doGetCaretPosition($input[0]) === 0) {
                            var prev = $inputWrapper.prev();
                            if (prev.length) {
                                self.remove(prev.data('item'));
                            }
                        }
                    }
                    break;

                // DELETE
                case 'Delete':
                    if(self.options.deleteKeys && keyInList(event, self.options.deleteKeys) > -1) {
                        if (doGetCaretPosition($input[0]) === 0) {
                            var next = $inputWrapper.next();
                            if (next.length) {
                                self.remove(next.data('item'));
                            }
                        }
                    }
                    break;

                // LEFT ARROW
                case 'ArrowLeft':
                    // Try to move the input before the previous tag
                    var $prevTag = $inputWrapper.prev();
                    if ($input.val().length === 0 && $prevTag[0]) {
                        $prevTag.before($inputWrapper);
                        $input.focus();
                    }
                    break;
                // RIGHT ARROW
                case 'ArrowRight':
                    // Try to move the input after the next tag
                    var $nextTag = $inputWrapper.next();
                    if ($input.val().length === 0 && $nextTag[0]) {
                        $nextTag.after($inputWrapper);
                        $input.focus();
                    }
                    break;
                case 'Home':
                    // Try to move the input before the first tag
                    var $firstTag = $inputWrapper.siblings().first();
                    if ($input.val().length === 0 && $firstTag[0]) {
                        $firstTag.before($inputWrapper);
                        $input.focus();
                    }
                    break;
                case 'End':
                    // Try to move the input after the last tag
                    var $lastTag = $inputWrapper.siblings().last();
                    if ($input.val().length === 0 && $lastTag[0]) {
                        $lastTag.after($inputWrapper);
                        $input.focus();
                    }
                    break;
                default:
                // ignore
            }

            // Reset internal input's size
            var textLength = $input.val().length,
                wordSpace = Math.ceil(textLength / 5),
                size = textLength + wordSpace + 1;
            $input.attr('size', Math.max(this.inputSize, $input.val().length));
        }, self));

        self.$container.on('keypress input', 'input', jQuery.proxy(function(event) {
            var $input = jQuery(event.target);

            if (self.$element.attr('disabled')) {
                self.$input.attr('disabled', 'disabled');
                return;
            }

            var text = $input.val(),
                maxLengthReached = self.options.maxChars && text.length >= self.options.maxChars;
            if (self.options.freeInput && (keyInList(event, self.options.confirmKeys) || (self.options.confirmOnPaste && event.originalEvent.type === 'input') || maxLengthReached)) {
                // Only attempt to add a tag if there is data in the field
                if (text.length !== 0) {
                    var items = text.split(self.options.delimiter);
                    for (var i = 0; i < items.length; i++) {
                        self.add(maxLengthReached ? items[i].substr(0, self.options.maxChars) : items[i]);
                    }
                    $input.val('');
                    event.preventDefault();
                }

                // If the field is empty, let the event triggered fire as usual
                if (self.options.cancelConfirmKeysOnEmpty !== false) {
                    event.preventDefault();
                }
            }

            // Reset internal input's size
            var textLength = $input.val().length,
                wordSpace = Math.ceil(textLength / 5),
                size = textLength + wordSpace + 1;
            $input.attr('size', Math.max(this.inputSize, $input.val().length));
        }, self));

        // Remove icon clicked
        self.$container.on('click', '[data-role=remove]', jQuery.proxy(function(event) {
            if (self.$element.attr('disabled')) {
                return;
            }
            self.remove(jQuery(event.target).closest('.tag').data('item'));
        }, self));

        // Only add existing value as tags when using strings as tags
        if (self.options.itemValue === defaultOptions.itemValue) {
            if (self.$element[0].tagName === 'INPUT') {
                self.add(self.$element.val());
            } else {
                jQuery('option', self.$element).each(function() {
                    self.add(jQuery(this).attr('value'), true);
                });
            }
        } else if (self.options.items && self.options.items.length) {
            jQuery(self.options.items).each(function(key, value) {
                self.add(value, true);
            })
        }
    },

    /**
     * Removes all tagsinput behaviour and unregsiter all event handlers
     */
    destroy: function() {
        var self = this;

        // Unbind events
        self.$container.off('keypress', 'input');
        self.$container.off('click', '[role=remove]');

        self.$container.remove();
        self.$element.removeData('tagsinput');
        self.$element.show();
    },

    /**
     * Sets focus on the tagsinput
     */
    focus: function() {
        this.$input.focus();
    },

    /**
     * Returns the internal input element
     */
    input: function() {
        return this.$input;
    },

    /**
     * Returns the element which is wrapped around the internal input. This
     * is normally the $container, but typeahead.js moves the $input element.
     */
    findInputWrapper: function() {
        var elt = this.$input[0],
            container = this.$container[0];
        while(elt && elt.parentNode !== container)
            elt = elt.parentNode;

        return jQuery(elt);
    }
};

/**
 * Most options support both a string or number as well as a function as
 * option value. This function makes sure that the option with the given
 * key in the given options is wrapped in a function
 */
function makeOptionItemFunction(options, key) {
    if (typeof options[key] !== 'function') {
        var propertyName = options[key];
        options[key] = function(item) { return item[propertyName]; };
    }
}
function makeOptionFunction(options, key) {
    if (typeof options[key] !== 'function') {
        var value = options[key];
        options[key] = function() { return value; };
    }
}
/**
 * HtmlEncodes the given value
 */
var htmlEncodeContainer = jQuery('<div />');
function htmlEncode(value) {
    if (value) {
        return htmlEncodeContainer.text(value).html();
    } else {
        return '';
    }
}

var entityMap = {
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': '&quot;',
    "'": '&#39;',
    "/": '&#x2F;',
    "\\": '&#x5C'
};

function escapeString(str){
    return String(str).replace(/[&<>"'\/\\]/g, function (s) {
        return entityMap[s];
    });
}

/**
 * Returns the position of the caret in the given input field
 * http://flightschool.acylt.com/devnotes/caret-position-woes/
 */
function doGetCaretPosition(oField) {
    var iCaretPos = 0;
    if (document.selection) {
        oField.focus ();
        var oSel = document.selection.createRange();
        oSel.moveStart ('character', -oField.value.length);
        iCaretPos = oSel.text.length;
    } else if (oField.selectionStart || oField.selectionStart == '0') {
        iCaretPos = oField.selectionStart;
    }
    return (iCaretPos);
}

/**
 * Use event.key rather than event.which as which is deprecated.
 */
function keyInList(keyPressEvent, lookupList) {
    return jQuery.inArray(keyPressEvent.key, lookupList) !== -1;
}

export default TagsInput;
