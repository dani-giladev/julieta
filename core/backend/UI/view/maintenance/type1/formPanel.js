Ext.define('App.core.backend.UI.view.maintenance.type1.formPanel', {
    extend: 'Ext.panel.Panel',
    
    alias: 'widget.maintenance_type1_formpanel',
        
    explotation: 'Maintenance form panel view',
    
    region: 'east',    
    
    layout: 'fit',   
    
    border: false,
    frame: false,
    flex: 1,
    collapsible: true,
    split: true,    
    
    config: null,
    
    initComponent: function() {
        
        var me = this;
        
        this.itemId = 'maintenance_type1_formpanel' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        this.title = (!me.config.form.title)? me.trans('main_form') : me.config.form.title;
        
        if (me.config.form.flex)
        {
            me.flex = me.config.form.flex;
        }
        
//        this.tools = 
//        [
//            {
//                type:'maximize',
//                handler: function(e, target, panel)
//                {
//
//                }
//            }
//        ];
        
        this.items = 
        [
            {
                xtype: 'panel',
                layout: 'border',
                items:
                [
                    Ext.widget('maintenance_type1_form', {
                        config: me.config
                    }),
                    Ext.widget('maintenance_type1_formtoolbar', {
                        config: me.config
                    }) 
                ]
            }
        ];
            
        this.callParent(arguments);
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    }
});