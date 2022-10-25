Ext.define('App.core.backend.UI.view.maintenance.typeTree.editForm.form', {
    extend: 'Ext.form.Panel',
    
    alias: 'widget.maintenance_typetree_editform_form',
    itemId: 'maintenance_typetree_editform_form',
        
    explotation: 'Maintenance tree (edit form)',
    
    region: 'center',

    border: false,
    frame: false,
    bodyPadding: 10,
    autoScroll: true,
    
    config: null,
    is_new_node: null,
    
    initComponent: function() {
        
        var me = this;
        
        me.itemId = 'maintenance_typetree_editform_form' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        this.title = '';
        
        this.items = me.config.form.fields;

        this.callParent(arguments);  
        
        // Add custom listeners
        this.getViewController().addListeners(this);
        this.on('boxready', this.onBoxready, this);
        
        // Update several properties
        this.getViewController().updateFormProperties(this);
        // set combos stores dinamically
        this.getViewController().setComboStores(this);   
    },
    
    onBoxready: function(this_form, width, height, eOpts)
    {
        // Set focus
        if (this.is_new_node)
        {
            this.getViewController().setFocusFieldOnNew(this_form);  
        } 

    },
    
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.typeTree');       
        return controller;
    }
});