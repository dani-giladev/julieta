Ext.define('App.core.backend.UI.view.maintenance.type1ModalForm.maintenance', {
    extend: 'Ext.window.Window',
    
    alias: 'widget.maintenance_type1_modalform',
        
    explotation: 'Windowed form Maintenance (window)',

    config: null,
    is_new_record: null,
    
    modal: true,
    closable: true,
    resizable: false,    
    header: true,
    frame: false,
    border: true,
    
    layout: 'border',
    
    initComponent: function() 
    {
        var me = this;
        
        me.itemId = 'maintenance_type1_modalform' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
                    
        var size = me.getViewController().getSize();
        
        this.title = me.config.form.title;
        this.width = me.config.form.width;
        this.height = me.config.form.height;
        this.maxHeight  = size.height - 20;
            
        this.items = 
        [ 
            Ext.widget('maintenance_type1_modalform_form', {
                config: me.config,
                is_new_record: me.is_new_record
            }),
            Ext.widget('maintenance_type1_modalform_toolbar', {
                config: me.config
            })  
        ];

        this.callParent(arguments);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1ModalForm');       
        return controller;
    }

});