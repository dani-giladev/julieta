Ext.define('App.core.backend.UI.store.globalConfig', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.globalConfig',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\backend', 
            method: 'getGlobalConfig'
        },
        reader: {
            type: 'json',
            rootProperty: 'data.results',
            totalProperty: 'data.total'
        }
    }
});