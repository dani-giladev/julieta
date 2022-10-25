Ext.define('App.core.backend.UI.store.modules', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.module',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\main', 
            method: 'getModules'
        },
        reader: {
            type: 'json',
            rootProperty: 'data.results',
            totalProperty: 'data.total'
        }
    }
});