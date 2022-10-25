Ext.define('App.core.backend.UI.view.maintenance.type1ModalForm.form', {
    extend: 'Ext.form.Panel',
    
    alias: 'widget.maintenance_type1_modalform_form',
    itemId: 'maintenance_type1_modalform_form',
        
    explotation: 'Windowed form Maintenance (form)',
    
    region: 'center',

    border: false,
    frame: false,
    bodyPadding: 10,
    autoScroll: true,
    
    config: null,
    is_new_record: null,
    
    initComponent: function() {
        
        var me = this;
        
        me.itemId = 'maintenance_type1_modalform_form' + '_' +
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
        if (this.is_new_record)
        {
            this.getViewController().setFocusFieldOnNew(this_form);  
        } 

    },
    
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1ModalForm');       
        return controller;
    }
});