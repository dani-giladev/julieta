Ext.define('App.core.backend.UI.view.maintenance.typeTree.maintenance', {
    extend: 'Ext.panel.Panel',
    
    alias: 'widget.maintenance_typetree',
        
    explotation: 'Maintenance tree main view',
    
    border: false,
    frame: false,
    title: '',
    layout: 'fit',
    
    config: null,
    
    initComponent: function() {
        
        var me = this;
        
        me.itemId = 'maintenance_typetree' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        me.items = 
        [
            {
                xtype: 'panel',
                border: false,
                frame: false,
                title: me.config.breadscrumb,
                layout: 'border',
                items:
                [
                    Ext.widget('maintenance_typetree_tree', {
                        config: me.config
                    }),
                    Ext.widget('maintenance_typetree_toolbar', {
                        config: me.config
                    })  
                ]
            }          
        ];
            
        me.callParent(arguments);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.typeTree');       
        
        return controller;
    }
});