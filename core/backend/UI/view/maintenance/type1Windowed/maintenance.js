Ext.define('App.core.backend.UI.view.maintenance.type1Windowed.maintenance', {
    extend: 'Ext.window.Window',
    
    alias: 'widget.maintenance_type1_windowed',
        
    explotation: 'Windowed Maintenance view',
    
    layout: 'fit',
    
    border: true,
    frame: false,
    height: 700,
    width: 1010,
    modal: true,
    
    config: null,
        
    initComponent: function() {
        
        // General properties
        this.initGeneralProperties();
        
        // Add window item
        this.getWindowItem();
            
        this.callParent(arguments);
    },
    
    initGeneralProperties: function()
    {
        var size = this.getViewController().getSize();
        this.maxHeight  = size.height - 20;        
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1Windowed');       
        
        return controller;
    },
    
    getWindowItem: function()
    {
        var module_id = this.config.module_id;
        var menu_id = this.config.menu_id;
        var model_id = menu_id;
        var options = this.config.options;
        var alias_new_widget = module_id + "_" + menu_id;
        
        // Get model and permissions
        var info_store = Ext.create('App.core.backend.UI.store.info');
        info_store.on('load', function(this_store, records, successful, eOpts)
        {
            if (!records[0].data.success)
            {
                Ext.MessageBox.show({
                   title: 'Error building item for windowed maintenance view.',
                   msg: records[0].data.message,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
                return;
            }

            var permissions = records[0].data.permissions;
            var config = 
            {
                permissions: permissions
            };
            
            for (i = 0; i < options.options.length; i++)
            {
                var descriptor = options.options[i]['descriptor'];
                var value = options.options[i]['value'];
                
                config[descriptor] = value;
            } 

            var fields = records[0].data.fields;
            if (fields !== '')
            {
                config.module_id = module_id;
                config.model = 
                {
                    id: model_id,
                    fields: fields
                };
            }    

            var new_widget = Ext.widget(alias_new_widget, {
                config: config
            });
            
            this.setTitle(this.config.title);
            
            this.add(new_widget);
            
        }, this, {single: true});  
        
        info_store.load({
            params: {
                module_id: module_id,
                model_id: model_id,
                menu_id: menu_id,
                start: 0,
                limit: 9999
            }
        }); 
    }
});