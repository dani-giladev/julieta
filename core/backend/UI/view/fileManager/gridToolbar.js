Ext.define('App.core.backend.UI.view.fileManager.gridToolbar', {
    extend: 'Ext.toolbar.Toolbar',
    
    alias: 'widget.filemanager_grid_toolbar',
    
    explotation: 'File manager grid toolbar view',
    
    region: 'north',
    border: true,
    frame: false,
    
    config: {},
    
    initComponent: function()
    {
        var me = this;
        
        this.title = '';
        
        this.items = 
        [
            {
                xtype: 'form',
                title: '',// 'Upload a File',
                width: 150,
                frame: false,
                border: false,
                margin: 0,
                padding: 0,
                items: [{
                    xtype: 'multiUpload',
                    name: 'files[]',
                    multiple: true,
                    buttonOnly : true,
                    buttonConfig : {
                        margin: 0,
                        text : 'Add file',
                        disabled: !me.config.permissions.update,
                        iconCls: "x-fa fa-plus"
                    },
                    margin: 0,
                    listeners: {
                        change: function(fld, value) {
                            var newValue = value.replace(/C:\\fakepath\\/g, '');
                            fld.setRawValue(newValue);

                            var form = this.up('form').getForm();
                            if(form.isValid())
                            {
                                me.getViewController().uploadFiles(me.config, form);
                            }                                
                        }
                    }
                }]
            },
            {
                text: me.trans('delete_file'),
                iconCls: 'x-fa fa-remove',
                disabled: !me.config.permissions.delete,
                handler: function()
                {
                    me.getViewController().deleteFile(me.config);                         
                }
            },
            {
                xtype: 'tbfill'
            },
            {
                text: me.trans('visualize'),
                iconCls: "x-fa fa-eye",
                handler: function()
                {
                    me.getViewController().visualize(me.config);                         
                }
            },
            {
                text: me.trans('download'),
                iconCls: "x-fa fa-download",
                handler: function()
                {
                    me.getViewController().download(me.config);  
                }
            } 
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
        var controller = App.app.getController('App.core.backend.UI.controller.fileManager');       
        return controller;
    }
});