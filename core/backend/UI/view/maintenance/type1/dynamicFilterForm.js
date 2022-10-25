Ext.define('App.core.backend.UI.view.maintenance.type1.dynamicFilterForm', {
    extend: 'Ext.form.Panel',
    
    alias: 'widget.maintenance_type1_dynamicfilterform',
        
    explotation: 'Dynamic filter form view',
    
    border: false,
    frame: false,
    width: 300,
    region: 'west',
    split: true,
    collapsible: true,
    //collapsed: true,
    bodyPadding: 10,
    autoScroll: true,
    
    config: {},
    
    initComponent: function() {
        
        var me = this;
        
        this.itemId = 'maintenance_type1_dynamicfilterform' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        this.title = (!me.config.dynamicFilterForm.title)? me.trans('filters') : me.config.dynamicFilterForm.title;
        
        if (me.config.filterForm)
        {
            this.width = '100%'; 
            this.region = 'center'; 
            this.split = false;
            this.collapsible = false;      
        }
    
        this.items = [
            me.getItems()
        ];
        
        me.dockedItems = 
        [
            {
                xtype: 'toolbar',
                anchor: '100%',
                dock: 'bottom',
                items: [
                    {
                        xtype: 'tbfill'
                    },
                    {
                        xtype: 'button',
                        text: me.trans('reset'),
                        handler: me.resetFilterForm
                    }
                ]
            }
        ];
            
        this.callParent(arguments);   
    },
    
    getItems: function()
    {
        var me = this;
        
        var ret = 
        {
            xtype: 'container',
            itemId: 'maintenance_type1_dynamicfilterform_fields_container' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id,
            defaults: {
                //width: '100%',
                labelAlign: 'right'//,
                //labelWidth: 60
            },
            items: me.config.dynamicFilterForm.fields
        };
        
        return ret;
    },
    
    onRender: function(thisForm, eOpts)
    {
        var me = this;
        
        me.getDynamicFilterController().initialize(me.config);
        
        me.callParent(arguments);
    },
    
    resetFilterForm: function(button, eventObject)
    {
        var me = button.up('form');
        me.getDynamicFilterController().resetFilterForm(me.config);
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
        
    getDynamicFilterController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1DynamicFilterForm');
        return controller;
    }
});