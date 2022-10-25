Ext.define('App.core.backend.UI.store.countries', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.country',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\backend', 
            method: 'getCountriesList'
        },
        reader: {
            type: 'json',
            rootProperty: 'data.results',
            totalProperty: 'data.total'
        }
    }
});