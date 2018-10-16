define(['jquery', 'core/str', 'core/notification', 'core/ajax', 'core/templates', 'core/sortable_list'],
    function ($, str, notification, ajax, templates, sortableList) {

        var confirmDelete = function (id, type, component, area, itemid) {
            str.get_strings([
                {'key': 'delete'},
                {'key': 'confirmdelete', component: 'core_customfield'},
                {'key': 'yes'},
                {'key': 'no'},
            ]).done(function (s) {
                notification.confirm(s[0], s[1], s[2], s[3], function () {
                    switch (type) {
                        case 'field':
                            var func = 'core_customfield_delete_entry';
                            break;
                        case 'category':
                            var func = 'core_customfield_delete_category';
                            break;
                    }

                    var promises = ajax.call([
                        {methodname: func, args: {id: id}},
                        {methodname: 'core_customfield_reload_template', args: {component: component, area: area, itemid: itemid}}
                    ]);
                    promises[1].done(function (response) {
                        templates.render('core_customfield/customfield', response).done(function (html, js) {
                            $('[data-region="list-page"]').replaceWith(html);
                            templates.runTemplateJS(js);
                        }).fail(function () {
                            // Deal with this exception (I recommend core/notify exception function for this).
                        });
                    }).fail(function () {
                        // Do something with the exception.
                    });
                });
            }).fail(notification.exception);
        };

        var createNewCategory = function(component, area, itemid) {
            var promises = ajax.call([
                    {methodname: 'core_customfield_create_category', args: {component: component, area: area, itemid: itemid}},
                    {methodname: 'core_customfield_reload_template', args: {component: component, area: area, itemid: itemid}}
                ]),
                categoryid;
            promises[0].then(function(response) {
                categoryid = response;
                return;
            }).catch(notification.exception);
            promises[1].then(function(response) {
                templates.render('core_customfield/customfield', response).then(function(html, js) {
                    $('[data-region="list-page"]').replaceWith(html);
                    templates.runTemplateJS(js);
                    window.location.href = '#category-' + categoryid;
                    return;
                }).catch(notification.exception);
                return;
            }).catch(notification.exception);
        };

        return {
            init: function () {
                var component = $('#customfield_catlist').attr('data-component'),
                    area = $('#customfield_catlist').attr('data-area'),
                    itemid = $('#customfield_catlist').attr('data-itemid');
                $("[data-role=deletefield]").on('click', function (e) {
                    confirmDelete($(this).attr('data-id'), 'field', component, area, itemid);
                    e.preventDefault();
                });
                $("[data-role=deletecategory]").on('click', function (e) {
                    confirmDelete($(this).attr('data-id'), 'category', component, area, itemid);
                    e.preventDefault();
                });
                $('[data-role=addnewcategory]').on('click', function() {
                    createNewCategory(component, area, itemid);
                });

                var categoryName = function (element) {
                    return element
                        .closest('[data-category-id]')
                        .find('[data-inplaceeditable][data-itemtype=category][data-component=core_customfield]')
                        .attr('data-value');
                };

                // Sort category.
                var sortCat = new sortableList('#customfield_catlist', {moveHandlerSelector: '.movecategory [data-drag-type=move]'});
                sortCat.getElementName = function(el) {
                    //console.log('elementnamecallback');
                    //console.log(el);
                    return $.Deferred().resolve(categoryName(el));
                };
                $('[data-category-id]').on(
                    'sortablelist-drop sortablelist-dragstart sortablelist-drag sortablelist-dragend',
                    function(evt, info) {
                        if (evt.type == 'sortablelist-drop' && info.positionChanged) {
                            var promises = ajax.call([
                                {
                                    methodname: 'core_customfield_drag_and_drop_block',
                                    args: {
                                        from: info.element.data('category-id'),
                                        to: info.targetNextElement.data('category-id') || 0
                                    }

                                },
                            ]);
                            promises[0].fail(function() {
                                require(["core/notification"], function(notification) {
                                    str.get_string('errorreloadpage', 'core_customfield').done(function(s) {
                                        notification.addNotification({
                                            message: s,
                                            type: "problem"
                                        });
                                    });
                                });
                            });
                        }
                        evt.stopPropagation(); // Important for nested lists to prevent multiple targets.
                    });

                // Sort activities.
                var sort = new sortableList('#customfield_catlist .fieldslist tbody', {moveHandlerSelector: '.movefield [data-drag-type=move]'});
                sort.getDestinationName = function (parentElement, afterElement) {
                    if (!afterElement.length) {
                        return str.get_string('totopofcategory', 'customfield', categoryName(parentElement));
                    } else if (afterElement.attr('data-field-name')) {
                        return str.get_string('afterfield', 'customfield', afterElement.attr('data-field-name'));
                    } else {
                        return $.Deferred().resolve('');
                    }
                 };
                $('[data-field-name]').on(
                    'sortablelist-drop sortablelist-dragstart sortablelist-drag sortablelist-dragend',
                    function (evt, info
                    ) {
                        if (evt.type === 'sortablelist-drop' && info.positionChanged) {
                            var promises = ajax.call([
                                {
                                    methodname: 'core_customfield_drag_and_drop',
                                    args: {
                                        from: info.element.data('field-id'),
                                        to: info.targetNextElement.data('field-id') || 0,
                                        category: Number(info.targetList.closest('[data-category-id]').attr('data-category-id'))
                                    },
                                },
                            ]);
                            promises[0].fail(function() {
                                require(["core/notification"], function (notification) {
                                    str.get_string('errorreloadpage', 'core_customfield').done(function(s) {
                                        notification.addNotification({
                                            message: s,
                                            type: "problem"
                                        });
                                    });
                                });
                            });
                        }
                        evt.stopPropagation(); // Important for nested lists to prevent multiple targets.
                        // Refreshing fields tables.
                        str.get_string('therearenofields', 'core_customfield').then(function (s) {
                            $('#customfield_catlist').children().each(function () {
                                if (!$(this).find($('.field')).length && !$(this).find($('.nofields')).length) {
                                    $(this).find('tbody').append(
                                        '<tr class="nofields"><td colspan="5">' + s + '</td></tr>'
                                    );
                                }
                                if ($(this).find($('.field')).length && $(this).find($('.nofields')).length) {
                                    $(this).find($('.nofields')).remove();
                                }
                            });
                        }).fail(notification.exception);
                    });

                $('[data-category-id], [data-field-name]').on(
                    'sortablelist-dragstart',
                    function (evt, info) {
                        setTimeout(function () {
                            $('.sortable-list-is-dragged').width(info.element.width());
                        }, 501);
                    }
                );

            }
        };
    });
