/**
 * @author Fábio Paiva <paiva.fabiofelipe@gmail.com>
 * @author Benjamin Bunse <b.bunse@pso-vertrieb.de>
 * @license The MIT License (MIT) | Copyright (c) 2014 Fabio Felipe Paiva <paiva.fabiofelipe@gmail.com> | Copyright (c) 2015 Benjamin Bunse <b.bunse@pso-vertrieb.de>
 */
var zf2DoctrineAutocomplete = {
    init: function(selector) {
        $(selector)
                .find("input[data-zf2doctrineacinit='zf2-doctrine-autocomplete']")
                .each(function() {
                    var _class = $(this).data('zf2doctrineacclass');
                    var _select_warning_message = $(this).data('zf2doctrineacselectwarningmessage');
                    var _property = $(this).data('zf2doctrineacproperty');
                    var _allow_persist = $(this).data('zf2doctrineacallowpersist');
                    var _target_class = $(this).data('zf2doctrineactargetclass');
                    var _search_fields = $(this).data('zf2doctrineacsearchfields');
                    var _order_by = $(this).data('zf2doctrineacorderby');

                    if (_class == null)
                        return;
                    /*
                     * remove initializers to prevent initialize again
                     */
                    $(this).data('zf2doctrineacinit', null);
                    $(this).data('zf2doctrineacclass', null);
                    $(this).data('zf2doctrineacproperty', null);
                    /*
                     * Wrap
                     */
                    $(this).wrap('<div class="wrap-zf2-doctrine-autocomplete"></div>');
                    var _clone = $(this).clone(true);
                    $(this)
                            .attr('name', $(this).attr('name') + '[id]')
                            .attr('type', 'hidden')
                            .addClass('zf2-doctrine-autocomplete-id');
                    _clone.attr('name', _clone.attr('name') + '[' + _property + ']');
                    if ($(this).data('zf2doctrineacid')) {
                        $(this).val($(this).data('zf2doctrineacid'));
                        $(this).data('zf2doctrineacid', null);
                    }
                    $(this).parent().append(_clone);
                    $(this).parent().append('<p class="zf2-doctrine-autocomplete-msg"></p>');
                    /*
                     * autocomplete
                     */
                    var cache = {};
                    $(_clone).autocomplete({
                        minLength: 2,
                        appendTo: $(this).parent(),
                        source: function(request, response) {
                            var term = request.term;
                            if (term in cache) {
                                response(cache[ term ]);
                                return;
                            }
                            request.select_warning_message = _select_warning_message;
                            request.property = _property;
                            request.target_class = _target_class;
                            request.search_fields = _search_fields;
                            request.order_by = _order_by;

                            $.getJSON('/zf2-doctrine-autocomplete/' + _class,
                                    request,
                                    function(data, status, xhr) {
                                        cache[ term ] = data;
                                        response(data);
                                    });
                        },
                        change: function(event, ui) {
                            if (ui.item == null) {
                                $(this).parent()
                                            .find('.zf2-doctrine-autocomplete-id')
                                            .val('');
                                if (!_allow_persist) {
                                    $(this).val('');                                    
                                    $(this).parent().find('.zf2-doctrine-autocomplete-msg')
                                            .html(_select_warning_message ? _select_warning_message : 'Select in list').addClass('bg-warning');
                                }
                            }
                        },
                        select: function(event, ui) {
                            $(this).parent()
                                    .find('.zf2-doctrine-autocomplete-id')
                                    .val('');
                            $(this).parent().find('.zf2-doctrine-autocomplete-msg')
                                    .html('').removeClass('bg-warning');
                            if (ui.item.value != null) {
                                $(this).val(ui.item.label);
                                $(this).parent()
                                        .find('.zf2-doctrine-autocomplete-id')
                                        .val(ui.item.value);
                                return false;
                            }

                        }

                    });
                    /* FIX from: http://stackoverflow.com/a/5931812 */
                    $(_clone).data("autocomplete")._resizeMenu = function () {
                        var ul = this.menu.element;
                        ul.outerWidth(this.element.outerWidth());
                    }
                });
    }
};

$(document).ready(function() {
    if (!$.isFunction($.fn.autocomplete)) {
        console.log('Required jQuery UI Autocomplete');
        return false;
    }
    zf2DoctrineAutocomplete.init('body');
});