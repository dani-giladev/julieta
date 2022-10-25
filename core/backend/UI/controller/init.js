Ext.define('App.core.backend.UI.controller.init', {
    extend: 'Ext.app.Controller', 

    requires: [
        'App.core.backend.UI.view.window',
        'App.core.backend.UI.view.login.login',
        'App.core.backend.UI.view.main.main'
    ],
    
    initialize: function () {
        var me = this;
        
        me.setOverrides();
        
        // Create and show the first view
        Ext.Ajax.request({
            type: 'ajax',
            url: 'index.php',
            method: 'GET',
            params: {
                controller: 'core\\backend\\controller\\login',
                method: 'checkLogin'
            },
            success: function(response)
            {
                var value = Ext.JSON.decode(response.responseText);
                //console.log(value);
                
                // Set global vars
                me.setGlobalVars(value.data.result);
                //console.log(value.data.result);
                
                if (value.data.result.success)
                {
                    me.showMainViewPort(true);
                }
                else
                {
                    me.loginWindow();
                }
            },
            failure: function(response)
            {
                me.loginWindow();
            }
        });

    },
    
    setOverrides: function()
    {
        var me = this;
        
        me.setAjaxBehabiour();

        me.defineUtilities();

        me.defineUploadingForm();

        me.fixExtjsGridBug();

        Ext.override(Ext.form.Panel, {
            reset: function()
            {
                var items = this.getForm().getFields().items,
                    i = 0,
                    len = items.length;
                for(; i < len; i++) {
                    var c = items[i];
                    // Set the default value in all form fields
                    if (c._defaultValue)
                    {
                        //console.log(c._defaultValue);
                        c.value = c._defaultValue;
                        //console.log(c.xtype);
                        if (c.xtype === 'checkboxfield')
                        {
                            c.checked = true;
                        }
                    }
                    else
                    {
                        c.value = '';
                    }
                    if(c.mixins && c.mixins.field && typeof c.mixins.field['initValue'] === 'function'){
                        c.mixins.field.initValue.apply(c);
                        c.wasDirty = false;
                    }
                }
            }
        });
    },
    
    setGlobalVars: function(object)
    {
        app_base_path = object.app_base_path;
        app_code = object.app_code;
        app_dateformat = object.app_dateformat;
        app_dateformat_database = object.app_dateformat_database;
        app_decimal_separator = object.app_decimal_separator;
        app_erp_interface_description = object.app_erp_interface_description;
        app_height_logo = object.app_height_logo;
        app_path_logo = object.app_path_logo;
        app_title = object.app_title;
        app_version = object.app_version;
        app_width_logo = object.app_width_logo;
        ecommerce_erp_interface_code = object.ecommerce_erp_interface_code;
        ecommerce_only_one_delegation = object.ecommerce_only_one_delegation;
        ecommerce_vat_is_always_inclued_to_cost_price = object.ecommerce_vat_is_always_inclued_to_cost_price;
        filemanager_path = object.filemanager_path;
        is_super_user = object.is_super_user;
        logged_full_name_user = object.logged_full_name_user;
        logged_lang = object.logged_lang;
        logged_user = object.logged_user;
    },
    
    setAjaxBehabiour: function()
    {
        var me = this;
        var globalAjaxOpsTimeout = 600000; // 10 minutes (1000 seg. * 60 * 10)
        Ext.Ajax.setTimeout(globalAjaxOpsTimeout);
        me.setAjaxBehabiourOnFormBasic();
        Ext.override(Ext.data.proxy.Server, { 
            constructor: function(config) {
                  var me=this;
                  config.timeout=Ext.Ajax.getTimeout();
                  me.callParent(arguments);
             }
         });
        Ext.override(Ext.data.Connection, { 
            constructor: function(config) {
                 var me=this;
                 config.timeout=Ext.Ajax.getTimeout();
                 me.callParent(arguments);
            }
        });        
    },
    
    setAjaxBehabiourOnFormBasic: function()
    {
        Ext.override( Ext.form.Basic, { 
            timeout: Ext.Ajax.getTimeout() / 1000
        });        
    },
    
    defineUtilities: function()
    {
        Ext.define('Utilities', {
            statics: {
                // Get back to previously selected record into grid after edit by a form
                selectPreviousRecord: function(grid, form_to_reload_record)
                {
                    var record_selected = grid.getSelectionModel().getSelection()[0];
                    if(record_selected)
                    {
                        var _id = record_selected.data.id;
                        if(!record_selected.data.id)
                        {
                            _id = record_selected.data._id;
                        }
                        var record_index = grid.getStore().indexOfId(_id);

                        grid.getStore().on('load', function()
                        {
                            grid.getSelectionModel().deselectAll(true);
                            grid.getSelectionModel().select(record_index);
                            if(form_to_reload_record)
                            {
                                form_to_reload_record.getForm().loadRecord(record_selected);
                            }
                        }, this, {single: true});
                    }
                }
            }
        });        
    },
    
    defineUploadingForm: function()
    {
        Ext.define('App.ux.field.upload', {
            extend: 'Ext.form.field.File',
            alias: 'widget.multiUpload',
            xtype: 'multiUpload',
            /*iconCls: 'ux-mu-icon-action-browse',
            buttonText: 'Select File(s)',
            buttonOnly: false,*/
            initComponent: function () {
                this.on('afterrender', function () {
                    this.setMultiple();
                }, this);
                this.callParent(arguments);
            },
            reset: function () {
                this.callParent(arguments);
                this.setMultiple();
            },
            setMultiple: function (inputEl) {
                inputEl = inputEl || this.fileInputEl;
                inputEl.dom.setAttribute('multiple', 'multiple');
            }
        });        
    },
    
    fixExtjsGridBug: function()
    {
        // BUG: grid blank after reload store. You have to scroll down and up a little to get data in the grid.
        // solved in extjs 6.5
        Ext.define('extjsGridBug.view.TableLayout', {
            override: 'Ext.view.TableLayout',
            finishedLayout: function(ownerContext) {
                var me = this,
                    ownerGrid = me.owner.ownerGrid,
                    nodeContainer = Ext.fly(me.owner.getNodeContainer()),
                    scroller = this.owner.getScrollable(),
                    buffered;

                me.callSuper([ ownerContext ]);

                if (nodeContainer) {
                    nodeContainer.setWidth(ownerContext.headerContext.props.contentWidth);
                }

                buffered = me.owner.bufferedRenderer;
                if (buffered) {
                    buffered.afterTableLayout(ownerContext);
                }

                if (ownerGrid) {
                    ownerGrid.syncRowHeightOnNextLayout = false;
                }

                if (scroller && !scroller.isScrolling) {
                    if (buffered && buffered.nextRefreshStartIndex === 0) {
                        return;
                    }
                    scroller.restoreState();
                }
            }
        });              
    },
    
    loginWindow: function()
    {
        // Getting core translation store
        var trans_store = Ext.create('App.core.backend.UI.store.translations');
        trans_store.on('load', function()
        {
            // Set common (core) lang store
            App.app.getController('App.core.backend.UI.controller.common').setLangStore(trans_store);
            
            // Create login window
            var window = Ext.widget('common-window', {
                isFullScreen: true
            });
            window.setHeight('100%');
            window.setWidth('100%');
            window.closable = false;
            window.resizable = false;   
            window.header = false;
            window.frame = false;
            window.border = false;

            var login_form = Ext.create({
                xtype: 'login'
            }); 

            window.add(login_form);   
            window.show();
        
        }, this, {single: true});   
        //console.log('Load core translations NOW!!');
        trans_store.load({params:{module_id: 'core'}});

    },
    
    showMainViewPort: function(load_translations)
    {
        var me = this;
    
        Ext.QuickTips.init();

        // Loading external extjs
        Ext.Loader.loadScript('resources/js/locale/locale-' + logged_lang + '.js');
        
        var global_config_store = Ext.create('App.core.backend.UI.store.globalConfig');
        global_config_store.on('load', function()
        {
            // Set global config store
            App.app.getController('App.core.backend.UI.controller.common').setGlobalConfigStore(global_config_store);

            // Getting core translation store
            if (load_translations)
            {
                var trans_store = Ext.create('App.core.backend.UI.store.translations');
                trans_store.on('load', function()
                {
                    // Set common (core) lang store
                    App.app.getController('App.core.backend.UI.controller.common').setLangStore(trans_store);

                    // Create main viewport
                    Ext.create('App.core.backend.UI.view.main.main').show();       

                }, this, {single: true});
                //console.log('Load core translations NOW!!');
                trans_store.load({params:{module_id: 'core'}});                 
            }
            else
            {
                // Create main viewport
                Ext.create('App.core.backend.UI.view.main.main').show();                                    
            }
            
        }, this, {single: true});   
        global_config_store.load();
    },
    
    trans: function(id, lang_store)
    {
        var ret;
        
        if (Ext.isEmpty(id))
        {
            console.log('The key (id) has an undefined value');
            console.log(lang_store);
            return '?';
        }
        
        if (Ext.isEmpty(lang_store))
        {
            console.log('The lang store has an undefined value');
            console.log(id);
            return '?';
        }
        
//        console.log(id);
//        console.log(lang_store);

        var record = lang_store.getById(id);
        if (record)
        {
            ret = record.get('trans');
            return ret;
        }
        
        // Trans from core lang store
        var core_lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        if (core_lang_store)
        {
            record = core_lang_store.getById(id);
            if (record)
            {
                ret = record.get('trans');
                return ret;
            }
            else
            {
                console.log(core_lang_store);
            }
        }
        else
        {
            console.log(core_lang_store);
        }
        
        console.log('The key: "' + id + '" does not exist in the translation file');
        //console.log(lang_store);
        return '?';     
    }
        
});