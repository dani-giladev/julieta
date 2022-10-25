Ext.define('App.core.backend.UI.store.languages', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.language',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\backend', 
            method: 'getLanguagesList'
        },
        reader: {
            type: 'json',
            rootProperty: 'data.results',
            totalProperty: 'data.total'
        }
    }
});