Ext.define('App.core.backend.UI.view.fileManager.multiImagesGridToolbar', {
    extend: 'Ext.toolbar.Toolbar',
    alias: 'widget.filemanager_multi_images_grid_toolbar',
    
    explotation: 'File manager - Multi images grid toolbar view',
    
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
                text: me.trans('deallocate_image'),
                disabled: !me.config.permissions.delete,
                handler: this.deleteAssignedImageFromMultiImageGrid
            }
        ];
            
        this.callParent(arguments);
    },
    
    deleteAssignedImageFromMultiImageGrid: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().deleteAssignedImageFromMultiImageGrid(me.config);
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