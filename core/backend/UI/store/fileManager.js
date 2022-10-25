Ext.define('App.core.backend.UI.store.fileManager', 
{
    extend: 'Ext.data.Store',
    model: 'App.core.backend.UI.model.fileManager',
    
    proxy: {
        type: 'ajax',
        url : 'index.php',
        extraParams: {
            controller: 'core\\backend\\controller\\fileManager', 
            method: 'getFiles'
        },
        reader: {
            type: 'json',
            rootProperty: 'files'
        }
    }
});