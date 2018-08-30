define(['jquery', 'core/str', 'core/notification', 'core/ajax', 'core/templates'], function($, str, notification, ajax, templates) {

    var confirmDeleteRow = function(id, handler) {
        str.get_strings([
            {'key': 'delete'},
            {'key': 'confirmdelete', component: 'core_cfield'},
            {'key': 'yes'},
            {'key': 'no'},
        ]).done(function(s) {
                notification.confirm(s[0], s[1], s[2], s[3], function() {
                    var promises = ajax.call([
                        {methodname: 'core_cfield_delete_entry', args:{id: id}},
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
    var confirmDeleteCategory = function(id, handler) {
        str.get_strings([
            {'key': 'delete'},
            {'key': 'confirmdeletecategory', component: 'core_cfield'},
            {'key': 'yes'},
            {'key': 'no'},
        ]).done(function(s) {
                notification.confirm(s[0], s[1], s[2], s[3], function() {
                    var promises = ajax.call([
                        {methodname: 'core_cfield_delete_category', args:{id: id}},
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
    var moveupfield = function(id, handler) {
        var promises = ajax.call([
            {methodname: 'core_cfield_move_up_field', args:{id: id}},
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
    var movedownfield = function(id, handler) {
        var promises = ajax.call([
            {methodname: 'core_cfield_move_down_field', args:{id: id}},
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
                confirmDeleteRow($(this).attr('data-id'), handler);
                e.preventDefault();
            });
            $(".confirm_delete_category").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                confirmDeleteCategory($(this).attr('data-id'), handler);
                e.preventDefault();
            });
            $(".move_up_field").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                moveupfield($(this).attr('data-id'), handler);
                e.preventDefault();
            });
            $(".move_down_field").on('click', function(e) {
                var handler = $('#cfield_catlist').attr('data-handler');
                movedownfield($(this).attr('data-id'), handler);
                e.preventDefault();
            });
        }
    };
});