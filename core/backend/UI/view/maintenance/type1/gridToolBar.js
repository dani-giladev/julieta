Ext.define('App.core.backend.UI.view.maintenance.type1.gridToolBar', {
    extend: 'Ext.toolbar.Toolbar',
    
    alias: 'widget.maintenance_type1_gridtoolbar',
        
    explotation: 'Maintenance grid toolbar view',

    region: 'north',
                
    border: true,
    frame: false,
    
//    ui: 'footer',
    
    config: null,
    
    initComponent: function() {
        
        var me = this;
        
        this.itemId = 'maintenance_type1_gridtoolbar' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        // console.log(this.itemId);
        this.title = '';
        
        var items = 
        [  
            {
                itemId: 'refresh_button_grid',
                text: me.trans('refresh'),
                iconCls: 'x-fa fa-refresh',
                handler: me.refreshGrid
            }  
        ];
        
        var action_buttons = 
        [
            {
                text: me.trans('publish'),
                iconCls: 'x-fa fa-flag',
                disabled: !me.config.permissions.publish,
                hidden: !me.config.enable_publication,
                handler: me.publishRecord
            },   
            {
                text: me.trans('publish_all'),
                iconCls: 'x-fa fa-flag-o',
                disabled: !me.config.permissions.publish,
                hidden: !(me.config.enable_publication && me.config.enable_publication_all),
                handler: me.publishAllRecords
            },   
            {
                text: me.trans('delete'),
                iconCls: 'x-fa fa-remove',
                disabled: !me.config.permissions.delete,
                hidden: !me.config.enable_deletion,
                handler: me.deleteRecord
            },   
            {
                text: me.trans('clone'),
                iconCls: 'x-fa fa-clone',
                disabled: !me.config.permissions.update,
                hidden: !me.config.enable_clone,
                handler: me.cloneRecord
            },   
            {
                text: me.trans('export'),
                iconCls: 'x-fa fa-sort-amount-asc',
                handler: me.exportRecords
            }             
        ];
        
        if (me.config.group_action_buttons)
        {
            items.push(
                {
                    xtype:'button',
                    text: me.trans('actions'),
                    iconCls: "x-fa fa-rocket",
                    menu: action_buttons
                }
            );
        }
        else
        {
            Ext.each(action_buttons, function(item) {
                items.push(item);
            });            
        }
        
        this.items = items;
            
        this.callParent(arguments);
    },
            
    refreshGrid: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().refreshGrid(me.config);
    },
            
    publishRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().publishRecord(me.config);
    },
            
    publishAllRecords: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().publishAllRecords(me.config);
    },
            
    deleteRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().deleteRecord(me.config);
    },
            
    cloneRecord: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().cloneRecord(me.config);
    },
            
    exportRecords: function(button, eventObject)
    {
        var me = button.up('toolbar');
        me.getViewController().exportRecords(me.config);
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1');       
        return controller;
    }
});