Ext.define('App.core.backend.UI.view.fileManager.fileManager', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.fileManager',

    explotation: 'File manager view',
    
    layout: 'border',
    border: false,
    frame: false,
    title: '',
    
    config: {},
            
    initComponent: function()
    {
        var me = this;
        
        if (!me.config.itemId)
        {
            me.config.itemId = 'fileManager' + '_' + me.config.module_id;
        }
        me.itemId = me.config.itemId;
        
        if (!me.config.hideTitle)
        {
            me.title = me.trans('fileManager');
        }
        
        if (!me.config.baseNode)
        {
            me.config.baseNode = "";
        }
        
        var items = 
        [
            /*Ext.widget('filemanager_toolbar', {
                config: me.config
            }),*/
            {
                xtype: 'panel',
                title: me.trans('folders'),
                split: true,
                region: 'west',
                width: 300,    
                layout: 'border',
                items:
                [
                    Ext.widget('filemanager_tree_toolbar', {
                        config: me.config
                    }),
                    Ext.widget('filemanager_tree', {
                        config: me.config
                    })
                ]
            },
            {
                xtype: 'panel',
                title: me.trans('images'),
                region: 'center',
                layout: 'border',
                items:
                [
                    Ext.widget('filemanager_grid_toolbar', {
                        config: me.config
                    }),
                    Ext.widget('filemanager_grid', {
                        config: me.config
                    })
                ]
            }
        ];
        
        if (me.config.enableSelectMultiImagesGrid)
        {
            me.config.enableSelectedEvent = false;
            items.push({
                xtype: 'panel',
                title: me.trans('assigned_images'),
                split: true,
                region: 'east',
                width: 450,    
                layout: 'border',
                items:
                [
                    Ext.widget('filemanager_multi_images_grid_toolbar', {
                        config: me.config
                    }),
                    Ext.widget('filemanager_multi_images_grid', {
                        config: me.config
                    })
                ]
            });
        }
            
        me.items = items;       
            
        this.callParent(arguments);
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