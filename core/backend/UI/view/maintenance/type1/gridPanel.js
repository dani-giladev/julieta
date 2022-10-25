Ext.define('App.core.backend.UI.view.maintenance.type1.gridPanel', {
    extend: 'Ext.panel.Panel',
    
    alias: 'widget.maintenance_type1_gridpanel',
        
    explotation: 'Maintenance grid panel view',
    
    region: 'center',    
    
    layout: 'fit',   
    
    border: false,
    frame: false,
    flex: 2,
    
    config: null,
    
    initComponent: function() {
        
        var me = this;
        
        me.itemId = 'maintenance_type1_gridpanel' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        //me.title = me.config.grid.title;
        me.title = me.config.breadscrumb;
        
        if (me.config.grid.flex)
        {
            me.flex = me.config.grid.flex;
        }
        
        me.items = 
        [
            {
                xtype: 'panel',
                layout: 'border',
                items:
                [
                    Ext.widget('maintenance_type1_grid', {
                        config: me.config
                    }),
                    Ext.widget('maintenance_type1_gridtoolbar', {
                        config: me.config
                    }) 
                ]
            }
        ];
            
        me.callParent(arguments);
    }
});