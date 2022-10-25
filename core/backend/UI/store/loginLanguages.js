Ext.define('App.core.backend.UI.store.loginLanguages', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.language',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\login', 
            method: 'getLanguages'
        },
        reader: {
            type: 'json',
            rootProperty: 'data.results',
            totalProperty: 'data.total'
        }
    }
});