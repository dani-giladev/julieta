Ext.define('App.core.backend.UI.view.fileManager.treeToolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.filemanager_tree_toolbar',
    
    explotation: 'File manager tree toolbar view',
    
    region: 'north',
    border: true,
    frame: false,
    
    config: {},
    
    initComponent: function()
    {
        var me = this;
        
        this.title = '';
        
        this.items = 
        [
            {
                text: me.trans('new_folder'),
                iconCls: 'x-fa fa-file-o',
                disabled: !me.config.permissions.update,
                handler: this.newFolder
            },
            {
                text: me.trans('delete_folder'),
                iconCls: 'x-fa fa-remove',
                disabled: !me.config.permissions.delete,
                handler: this.deleteFolder
            }
        ];
            
        this.callParent(arguments);
    },
    
    newFolder: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().newFolder(me.config);
    },
    
    deleteFolder: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().deleteFolder(me.config);
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.fileManager');       
        return controller;
    }
});