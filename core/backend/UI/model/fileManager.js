Ext.define('App.core.backend.UI.model.fileManager', {
    extend: 'Ext.data.Model',
    fields: [
        {name: 'filename'},
        {name: 'filesize'},
        {name: 'filedate'},
        {name: 'relativePath'}
    ]
});