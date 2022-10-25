/**
 * The main application class. An instance of this class is created by app.js when it
 * calls Ext.application(). This is the ideal place to handle application launch and
 * initialization details.
 */
Ext.Loader.setPath({
    'App': 'app',
    'App.modules': 'app/plugins/modules',
    'App.core': 'app/plugins/core'
}).setConfig({
//    enabled: true//,
//    disableCaching: false
});

Ext.define('App.Application', {
    extend: 'Ext.app.Application',
    
    name: 'App',

    requires: [
        'App.controllers'
    ],

    stores: [
        // TODO: add global / shared stores here
    ],
    
    launch: function () {

        App.app.getController('App.core.backend.UI.controller.init').initialize();

        // Remove loading mask
        /*setTimeout(function()
        {
            Ext.get('loading').remove();
            Ext.get('loading-mask').fadeOut({remove: true});
        }, 250);*/
    },
    
    trans: function(id, lang_store)
    {
        return App.app.getController('App.core.backend.UI.controller.init').trans(id, lang_store);
    },

    onAppUpdate: function () {
        Ext.Msg.confirm('Application Update', 'This application has an update, reload?',
            function (choice) {
                if (choice === 'yes') {
                    window.location.reload();
                }
            }
        );
    }
});
