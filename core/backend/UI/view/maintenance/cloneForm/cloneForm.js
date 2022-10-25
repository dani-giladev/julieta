Ext.define('App.core.backend.UI.view.maintenance.cloneForm.cloneForm', {
    extend: 'Ext.window.Window',
    
    alias: 'widget.maintenance_cloneform',
    
    explotation: 'Clone form (window) view',
    
    width: 450,
    height: 250,
    layout: 'border',
    modal: true,

    title: null,
    record_id: null,
    config: null,
    clonedFields: null,
    
    initComponent: function()
    {
        var me = this;
        var size = me.getViewController().getSize();        
        
        this.itemId = 'maintenance_cloneform_window' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        var title = me.trans('cloning_form');
        if (!Ext.isEmpty(me.title))
        {
            title = me.title;
        }
        this.title = title;   
        this.maxHeight  = size.height - 20;
        
        this.items = 
        [
            Ext.widget('maintenance_cloneform_form', {
                config: me.config,
                clonedFields: me.clonedFields
            }),
            Ext.widget('maintenance_cloneform_formtoolbar', {
                record_id: me.record_id,
                config: me.config
            })
        ];        
            
        this.callParent(arguments);
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