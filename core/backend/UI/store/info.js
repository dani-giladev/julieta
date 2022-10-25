Ext.define('App.core.backend.UI.store.info', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.info',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\main', 
            method: 'getSelectedMenuInfo'
        },
        reader: {
            type: 'json',
            rootProperty: 'data.results',
            totalProperty: 'data.total'
        }
    }
});