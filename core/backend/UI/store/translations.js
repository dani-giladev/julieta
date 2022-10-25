Ext.define('App.core.backend.UI.store.translations', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.translation',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\backend', 
            method: 'getClientTranslations'
        },
        reader: {
            type: 'json',
            rootProperty: 'data.results',
            totalProperty: 'data.total'
        }
    }
});