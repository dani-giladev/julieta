Ext.define('App.core.backend.UI.view.maintenance.typeTree.editForm.editForm', {
    extend: 'Ext.window.Window',
    
    alias: 'widget.maintenance_typetree_editform',
        
    explotation: 'Maintenance tree (edit form window)',

    config: null,
    is_new_node: null,
    
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
        var size = me.getViewController().getSize();
        
        this.title = me.config.form.title;
        this.width = me.config.form.width;
        this.height = me.config.form.height;
        this.maxHeight  = size.height - 20;
            
        this.items = 
        [ 
            Ext.widget('maintenance_typetree_editform_form', {
                config: me.config,
                is_new_node: me.is_new_node
            }),
            Ext.widget('maintenance_typetree_editform_toolbar', {
                config: me.config,
                is_new_node: me.is_new_node
            })  
        ];

        this.callParent(arguments);
    },
        
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.typeTree');       
        return controller;
    }

});