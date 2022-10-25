Ext.define('App.core.backend.UI.view.maintenance.cloneForm.form', {
    extend: 'Ext.form.Panel',
    
    alias: 'widget.maintenance_cloneform_form',
    
    explotation: 'Clone form view',
    
    region: 'center',

    border: false,
    frame: false,
    bodyPadding: 10,
    autoScroll: true,
    
    config: null,
    clonedFields: null,
    
    initComponent: function() {
        
        var me = this;        
        
        this.itemId = 'maintenance_cloneform_form' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
                    
        this.title = '';
        
//        this.items = me.clonedFields;
        this.items = 
        [
            {
                xtype: 'fieldset',
                padding: 5,
                title: me.trans('main'),
                anchor: '100%',
                items: me.clonedFields
            }
        ];  
        
        this.callParent(arguments);  
//        this.on('boxready', this.onBoxready, this); 
//        this.on('afterrender', this.onAfterrender, this);   
    },
    
    onRender: function(form, eOpts)
    {
//        console.log('onRender');
        // New record
        this.getViewController().newRecord(this.config);
        
        this.callParent(arguments);
    },
    
    onAfterrender: function(form, eOpts)
    {
//        console.log('onAfterrender');
    },
    
    onBoxready: function()
    {
//        console.log('onBoxready');
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    },
            
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.cloneForm');       
        return controller;
    }
});