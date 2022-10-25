Ext.define('App.core.backend.UI.view.fileManager.multiImagesGrid', {
    extend: 'Ext.grid.Panel',
    alias: 'widget.filemanager_multi_images_grid',
    
    explotation: 'File manager - Multi images grid view',
    
    region: 'center',
    border: false,
    frame: false,    
    autoScroll: true,
    sortableColumns: false,
    enableColumnHide : false,
    
    config: {},
    
    initComponent: function()
    {
        var me = this;
        
        me.itemId = me.config.itemId + '_filemanager_multi_images_grid';

        this.title = '';        
        this.store = me.config.imagesStore;
        
        // Drag and drop in order to sort rows
        this.enableDrag =  false;
        this.enableDrop = true;
        this.viewConfig =
        {
            plugins:
            [
                {
                    ptype: 'gridviewdragdrop',
                    dragGroup: 'filemanager_DDGroup_1',
                    dropGroup: 'filemanager_DDGroup_1'
                }

            ]             
        };
            
        this.columns =
        [
            {
                text: me.trans('image'),
                align: 'center',
                width: 100,
                renderer: me.formatPreview                  
            },        
            {
                text: me.trans('file'),
                flex: 1,
                renderer: me.formatTitle
            }
        ];
            
        this.dockedItems =
        [
            {
                xtype: 'toolbar',
                dock: 'bottom',
                items: [
                    {
                        xtype: 'tbfill'
                    },{
                        xtype: 'button',
                        text: me.trans('apply_and_close'),
                        handler: function()
                        {
                            me.getViewController().applyAssignedImagesFromMultiImageGridAndClose(me.config);
                        }
                    }
                ]
            }
        ];
        
        this.callParent(arguments);
        
        this.store.on('load', this.onLoad, this);
        
    },

    onRender: function(grid, options)
    {      
        
        this.callParent(arguments);           
    },

    onLoad: function(this_store, records, successful, eOpts)
    {
//        if(this_store.getCount() > 0)
//        {
//            this.getSelectionModel().select(0);
//        }
    },  
    
    formatPreview: function(value, p, record)
    {
        var relative_path = record.get('relativePath');
        var filename = record.get('filename');
        var src = '/' + filemanager_path + '/' + relative_path + '/' + filename;
        var html = '<img src="' + src + '" width="60" height="60" border="0" />';
        return html;
    },
    
    formatTitle: function(value, p, record)
    {
        var relative_path = record.get('relativePath');
        var filename = record.get('filename');
        var path = relative_path;
        if (!Ext.isEmpty(path))
        {
            path += '/';
        }
        path += filename;
        return Ext.String.format('<div><b>{0}</b></br>{1}</div>', path, record.get('filesize'));        
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