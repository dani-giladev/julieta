Ext.define('App.core.backend.UI.view.maintenance.type1.maintenance', {
    extend: 'Ext.panel.Panel',
    alias: 'widget.maintenance_type1',
        
    explotation: 'Maintenance main view',
    
    layout: 'border',
    border: false,
    frame: false,
    title: '',
    
    config: null,
    current_loaded_record: null,
    is_box_ready: false,
        
    initComponent: function() {
        
        var me = this;
                
        me.itemId = 'maintenance_type1' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        // Create the getRecords Store (main store of maintenance)
        me.config.store = me.getGetRecordsStore();
        
        var items = 
        [
            Ext.widget('maintenance_type1_gridpanel', {
                config: me.config
            })          
        ];
        
        if (me.config.form)
        {
            items.push(
                Ext.widget('maintenance_type1_formpanel', {
                    config: me.config
                })             
            );
            
        }
        
        if (me.config.filterForm)
        {
            var west_panel_items = 
            [
                Ext.widget('maintenance_type1_filterform', {
                    config: me.config
                })
            ];
            if (me.config.dynamicFilterForm)
            {
                west_panel_items.push(
                    Ext.widget('maintenance_type1_dynamicfilterform', {
                        config: me.config
                    })                     
                );
            }
            items.push(
            {
                xtype: 'panel',
                region: 'west',
                layout: 'border',
                width: 300,
                height: '100%',
                split: true,
                collapsible: true,
                title: me.trans('filters'),
                items: west_panel_items       
            });
        }
        else
        {
            if (me.config.dynamicFilterForm)
            {
                items.push(
                    Ext.widget('maintenance_type1_dynamicfilterform', {
                        config: me.config
                    })                     
                );
            }            
        }
            
        me.items = items;
        
        me.callParent(arguments);
        
        me.on('boxready', this.onBoxready, this);
    },
    
    initGeneralProperties: function()
    {
        this.config.hide_datapanel_title = true;               
        this.config.enable_publication = false;
        this.config.enable_clone = false;
        this.config.enable_deletion = true;
    },
    
    getGetRecordsStore: function()
    {
        var me = this;
        
        var autoload = !me.config.filterForm;
            
        return me.getViewController().getGetRecordsStore(me.config, autoload, false, 'update_after'); 
    },
    
    onBoxready: function(this_panel, width, height, eOpts)
    {
        var me = this;
        me.is_box_ready = true;
    },
    
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1');       
        return controller;
    },
        
    getModalFormMaintenanceController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1ModalForm');       
        return controller;
    }
});