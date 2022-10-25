Ext.define('App.core.backend.UI.view.fileManager.toolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.filemanager_toolbar',
    
    explotation: 'File manager toolbar view',
    
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
                text: me.trans('refresh'),
                handler: this.refresh
            }
        ];
            
        this.callParent(arguments);
    },
    
    refresh: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().refresh(me.config);
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