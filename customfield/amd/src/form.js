define(['jquery', 'core/str', 'core/notification', 'core/ajax', 'core/templates', 'core/sortable_list'], function($, str, notification, ajax, templates, sortableList) {

    var confirmDelete = function(id, type, component, area, itemid) {
        str.get_strings([
            {'key': 'delete'},
            {'key': 'confirmdelete', component: 'core_customfield'},
            {'key': 'yes'},
            {'key': 'no'},
        ]).done(function(s) {
            notification.confirm(s[0], s[1], s[2], s[3], function() {
                switch (type) {
                    case 'field':
                        var func = 'core_customfield_delete_entry';
                        break;
                    case 'category':
                        var func = 'core_customfield_delete_category';
                        break;
                }

                var promises = ajax.call([
                    {methodname: func, args:{id: id}},
                    {methodname: 'core_customfield_reload_template', args:{component: component, area: area, itemid: itemid}}
                ]);
                promises[1].done(function(response) {
                    templates.render('core_customfield/customfield',response).done(function(html, js) {
                        $('[data-region="list-page"]').replaceWith(html);
                        templates.runTemplateJS(js);
                    }).fail(function() {
                        // Deal with this exception (I recommend core/notify exception function for this).
                    });
                }).fail(function() {
                    // Do something with the exception.
                });
            });
        }).fail(notification.exception);
    };
    var move = function(id, handler, direction) {
        switch (direction) {
            case 'category_up':
                var func = 'core_customfield_move_up_category';
                break;
            case 'category_down':
                var func = 'core_customfield_move_down_category';
                break;
            case 'field_up':
                var func = 'core_customfield_move_up_field';
                break;
            case 'field_down':
                var func = 'core_customfield_move_down_field';
                break;
        }
        var promises = ajax.call([
            {methodname: func, args:{id: id, handler: handler}},
            {methodname: 'core_customfield_reload_template', args:{handler: handler}}
        ]);
        promises[1].done(function(response) {
            templates.render('core_customfield/customfield',response).done(function(html, js) {
                $('[data-region="list-page"]').replaceWith(html);
                templates.runTemplateJS(js);
            }).fail(function() {
                // Deal with this exception (I recommend core/notify exception function for this).
            });
        }).fail(function() {
            // Do something with the exception.
        });
    };
    return {
        init: function() {
            var component = $('#customfield_catlist').attr('data-component'),
                area = $('#customfield_catlist').attr('data-area'),
                itemid = $('#customfield_catlist').attr('data-itemid');
            $(".confirm_delete").on('click', function(e) {
                confirmDelete($(this).attr('data-id'), 'field', component, area, itemid);
                e.preventDefault();
            });
            $(".confirm_delete_category").on('click', function(e) {
                confirmDelete($(this).attr('data-id'), 'category', component, area, itemid);
                e.preventDefault();
            });
            $(".move_up_field").on('click', function(e) {
                var handler = $('#customfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'field_up');
                e.preventDefault();
            });
            $(".move_down_field").on('click', function(e) {
                var handler = $('#customfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'field_down');
                e.preventDefault();
            });
            $(".move_up_category").on('click', function(e) {
                var handler = $('#customfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'category_up');
                e.preventDefault();
            });
            $(".move_down_category").on('click', function(e) {
                var handler = $('#customfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'category_down');
                e.preventDefault();
            });

            var sectionName = function(element) {
                return element.closest('[data-category-name]').attr('data-category-name');
            };

            // Sort category.
            sortableList.init({
                listSelector: '#customfield_catlist',
                moveHandlerSelector: '.movecategory',
                elementNameCallback: function(el) {
                    console.log('elementnamecallback');console.log(el);return sectionName(el);
                }
            });
            $('[data-category-name]').on('sortablelist-drop sortablelist-dragstart sortablelist-drag sortablelist-dragend', function(evt, info) {
                if ( evt.type == 'sortablelist-dragend' && info.dropped ) {
                    alert('CATEGORY');
                }
                console.log('Category event ' + evt.type);
                console.log(info);
                evt.stopPropagation(); // Important for nested lists to prevent multiple targets.
            });

            // Sort activities.
            sortableList.init({
                listSelector: '#customfield_catlist .fieldslist tbody',
                moveHandlerSelector: '.movefield',
                destinationNameCallback: function(parentElement, afterElement) {
                    if (!afterElement.length) {
                        return str.get_string('totopofsection', 'moodle', sectionName(parentElement));
                   } else if (afterElement.attr('data-field-name')) {
                        return str.get_string('afterresource', 'moodle', afterElement.attr('data-field-name'));
                    } else {
                        return '';
                    }
                }
            });
            $('[data-field-name]').on(
                'sortablelist-drop sortablelist-dragstart sortablelist-drag sortablelist-dragend',
                function (evt, info
                ) {
                    console.log('Field event ' + evt.type);
                    console.log(info);

                    if (evt.type == 'sortablelist-dragend' && info.dropped) {
                        ajax.call([
                            {
                                methodname: 'core_customfield_drag_and_drop',
                                args: {
                                    from: info.draggedElement[0].dataset.fieldId,
                                    to: info.targetList.prevObject[0].dataset.fieldId
                                }
                            },
                        ]);
                    }
                evt.stopPropagation(); // Important for nested lists to prevent multiple targets.
            });
        }
    };
});