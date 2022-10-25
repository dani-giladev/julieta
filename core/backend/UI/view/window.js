Ext.define('App.core.backend.UI.view.window' ,{
    extend: 'Ext.window.Window',
    xtype: 'common-window',
    alias: 'widget.common-window',
    controller: 'viewController',
    
    requires: [
        'App.core.backend.UI.view.viewController'
    ],
    
    title: '',
    width: 950,
    autoHeight: true,
    layout: 'fit',
    resizable: true,
    modal: true,
    
    isFullScreen: false,

    initComponent: function()
    {
        var me = this;
            
        if (!me.isFullScreen)
        {
            var size = me.getController().getSize();
            this.maxHeight  = size.height - 20;
            this.maxWidth  = size.width - 20;            
        }
        
        this.items = [];        
            
        this.callParent(arguments);
    }

});