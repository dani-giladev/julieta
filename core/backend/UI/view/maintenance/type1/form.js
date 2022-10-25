Ext.define('App.core.backend.UI.view.maintenance.type1.form', {
    extend: 'Ext.form.Panel',
    
    alias: 'widget.maintenance_type1_form',
        
    explotation: 'Maintenance form view',
    
    region: 'center',

    border: false,
    frame: false,
    bodyPadding: 10,
    autoScroll: true,
    
    trackResetOnLoad: true,
    
    config: null,
    
    initComponent: function() {
        
        var me = this;
        
        this.itemId = 'maintenance_type1_form' + '_' +
                        me.config.module_id + '_' +
                        me.config.model.id;
        
        this.title = '';
        
        this.items = me.config.form.fields;

        this.callParent(arguments);
        
        // Add custom listeners
        this.getViewController().addListeners(this);
        // Update several properties
        this.getViewController().updateFormProperties(this);
        // set combos stores dinamically
        this.getViewController().setComboStores(this);   
    },
    
    /* 
     * Important!!!
     * For the common maintenance development, we have to define all the events in the view.  
     * We works with dynamic itemId properties and it's very dificult place these events in the controller.
     */
    
    onRender: function(form, eOpts)
    {
        // Hide refresh button if there isn't any combo or multiselect
        this.getViewController().hideRefreshButtonForm(this.config, this);
        
        // New record
        this.getViewController().newRecord(this.config);
                                  
        this.callParent(arguments);
    },
            
    getViewController: function()
    {
        var controller = App.app.getController('App.core.backend.UI.controller.maintenance.type1');       
        return controller;
    }
});