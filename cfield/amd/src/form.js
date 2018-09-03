define(['jquery', 'core/str', 'core/notification', 'core/ajax', 'core/templates'], function($, str, notification, ajax, templates) {

    var confirmDelete = function(id, handler, type) {
        str.get_strings([
            {'key': 'delete'},
            {'key': 'confirmdelete', component: 'core_cfield'},
            {'key': 'yes'},
            {'key': 'no'},
        ]).done(function(s) {
            notification.confirm(s[0], s[1], s[2], s[3], function() {
                switch (type) {
                    case 'field':
                        var func = 'core_cfield_delete_entry';
                        break;
                    case 'category':
                        var func = 'core_cfield_delete_category';
                        break;
                }
                var promises = ajax.call([
                    {methodname: func, args:{id: id, handler: handler}},
                    {methodname: 'core_cfield_reload_template', args:{handler: handler}}
                ]);
                promises[1].done(function(response) {
                    templates.render('core_cfield/cfield',response).done(function(html, js) {
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
                var func = 'core_cfield_move_up_category';
                break;
            case 'category_down':
                var func = 'core_cfield_move_down_category';
                break;
            case 'field_up':
                var func = 'core_cfield_move_up_field';
                break;
            case 'field_down':
                var func = 'core_cfield_move_down_field';
                break;
        }
        var promises = ajax.call([
            {methodname: func, args:{id: id, handler: handler}},
            {methodname: 'core_cfield_reload_template', args:{handler: handler}}
        ]);
        promises[1].done(function(response) {
            templates.render('core_cfield/cfield',response).done(function(html, js) {
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
            $(".confirm_delete").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                confirmDelete($(this).attr('data-id'), handler, 'field');
                e.preventDefault();
            });
            $(".confirm_delete_category").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                confirmDelete($(this).attr('data-id'), handler, 'category');
                e.preventDefault();
            });
            $(".move_up_field").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'field_up');
                e.preventDefault();
            });
            $(".move_down_field").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'field_down');
                e.preventDefault();
            });
            $(".move_up_category").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'category_up');
                e.preventDefault();
            });
            $(".move_down_category").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                move($(this).attr('data-id'), handler, 'category_down');
                e.preventDefault();
            });
        }
    };
});