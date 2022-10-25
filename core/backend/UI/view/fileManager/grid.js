Ext.define('App.core.backend.UI.view.fileManager.grid', {
    extend: 'Ext.grid.Panel',
    
    alias: 'widget.filemanager_grid',
    itemId: 'filemanager_grid',
    
    explotation: 'File manager grid view',
    
    region: 'center',
    border: false,
    frame: false,
    
    config: {},
    
    initComponent: function()
    {
        var me = this;
        
        me.itemId = me.config.itemId + '_grid';
        
        Ext.apply(this, {
            title: '',
            store: Ext.create('App.core.backend.UI.store.fileManager'),
            viewConfig: {
                itemId: 'itemsView',
                emptyText: me.trans('no_images_to_display'),
                deferEmptyText: false,
                //stripeRows: true,
                enableDrag: true,
                enableDrop: false,
                allowCopy: true,
                copy: true,
                plugins: 
                [
                    {
                        ptype: 'gridviewdragdrop',
                        dragGroup: 'filemanager_DDGroup_1',
                        dropGroup: 'filemanager_DDGroup_2'
                    }
                ],
                listeners: {
                    scope: this,
                    itemdblclick: this.onRowDblClick
                }
            },
            columns: [
                { 
                    header: me.trans('name'), 
                    filterable: true, 
                    filter: {type: 'string'}, 
                    width: 170, 
                    dataIndex: 'filename', 
                    flex: 1, 
                    renderer: this.formatTitle
                },
                { header: 'Size', width: 100, dataIndex: 'filesize', hidden: true },
                { header: 'Last modified', width: 200, dataIndex: 'filedate', hidden: true },
                { header: me.trans('preview'), width: 150, renderer: this.formatPreview }
            ],
            
            dockedItems: [{
                xtype: 'toolbar',
                dock: 'bottom',
                hidden: !me.config.enableSelectedEvent,
                items: [
                    {
                        xtype: 'tbfill'
                    },{
                        xtype: 'button',
                        text: 'Select and close',
                        handler: function()
                        {
                            me.getViewController().selectFile(me.config);
                        }
                    }
                ]
            }]
        });
        
        this.callParent(arguments);
        
        this.store.on('load', this.onLoad, this);
        
        this.store.load({params:{start:0, limit:9999}});
    },
    
    onLoad: function(this_store, records, successful, eOpts)
    {
        if(this_store.getCount() > 0)
        {
            this.getSelectionModel().select(0);
        }
    },
    
    onRowDblClick: function(view, record, product, index, e)
    {
        var me = this;
        
        if (me.config.enableSelectedEvent)
        {
            me.getViewController().selectFile(me.config); 
        }
        else
        {
            me.getViewController().visualize(me.config); 
        }
    },

    formatTitle: function(value, p, record)
    {
        //var html = '<div><b>{0}</b></br>{1}</br>{2}</div>';
        var html = '<div><b>{0}</b></br>{1}</div>';
        return Ext.String.format(html, 
            value, 
            record.get('filesize')//,
            //record.get('relativePath')
        );
    },
    
    formatPreview: function(value, p, record)
    {
        var relative_path = record.get('relativePath');
        var filename = record.get('filename');
        var src = '/' + filemanager_path + '/' + relative_path + '/' + filename;
        var html = '<img src="' + src + '" width="60" height="60" border="0" />';
        return html;
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