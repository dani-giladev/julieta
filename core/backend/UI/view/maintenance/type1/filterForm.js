Ext.define('App.core.backend.UI.view.maintenance.type1.filterForm', {
    extend: 'Ext.form.Panel',
    
    alias: 'widget.maintenance_type1_filterform',
        
    explotation: 'Filter form view',
    
    region: 'north',
    border: false,
    frame: false,
    autoWidth: true,
    bodyPadding: 10,
    anchor: '100%',
    
    config: {},
    
    initComponent: function() {
        
        var me = this;
        
        this.itemId = 'maintenance_type1_filterform' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        this.title = '';
    
        this.items = me.config.filterForm.fields;
            
        this.callParent(arguments);   
    },
    
    onRender: function(thisForm, eOpts)
    {
        var me = this;
        
        me.getViewController().refreshGrid(me.config);
        
        me.callParent(arguments);
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