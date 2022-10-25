Ext.define('App.core.backend.UI.controller.main', {
    extend: 'App.core.backend.UI.controller.common',

    requires: [
        'App.core.backend.UI.model.module',
        'App.core.backend.UI.model.info',
    
        'App.core.backend.UI.store.modules',
        'App.core.backend.UI.store.info',
        
        'App.modules.admin.backend.UI.store.language.languages'
    ],

    initModules: function()
    {
        var me = this;
        var module_id, module_name, icon, menus;       
        var i, pressed = false, is_pressed = false; 
        
        // Get available languages
        var lang_store = Ext.create('App.modules.admin.backend.UI.store.language.languages');
        lang_store.on('load', function(this_store, lang_records, successful, eOpts)
        {
            var available_langs = [];
            Ext.each(lang_records, function(record) {
                available_langs.push(record.data)
            });              
            App.app.getController('App.core.backend.UI.controller.common').setAvailableLangs(available_langs);
        }); 
        lang_store.load({
            params: {
                controller: 'modules\\' + 'admin' + '\\backend\\controller\\language'
            }
        }); 
            
        // Getting modules
        var modules_store = Ext.create('App.core.backend.UI.store.modules');
        modules_store.on('load', function(this_store, records, successful, eOpts)
        {
            // Init visible modules
            for(i=0; i<records.length; i++)
            {
                module_id = records[i].get('module_id');
                module_name = me.trans(module_id);
                icon = records[i].get('icon');
                menus= records[i].get('menus');
                
                pressed = false;
                if (!is_pressed)
                {
                    pressed = true;
                    is_pressed = true;
                }                
                me.addMainMenu(module_id, module_name, icon, menus, pressed, false, "main-menu-big-btn");
            }                

        }, this, {single: true});      
        
        modules_store.load();
    },
    
    addMainMenu: function(module_id, name, icon, menus, pressed, initialized, cls)
    {
        var me = this;
        var main_menu = Ext.getCmp('main-menu');

        var item = {
            id: 'main-menu-btn-module-' + module_id,
            text: name,
            iconCls: icon,
            pressed: pressed,
            _module_id: module_id,
            _menus: menus,
            _initialized: initialized,
            handler: function(button)
            {
                me.onClickMainMenu(button._module_id);
            }            
        };
        
        if (cls)
        {
            item.cls = cls;
        }
        
        main_menu.add(item);
        
        if (pressed)
        {
            me.onClickMainMenu(module_id);
        }
    },

    onClickMainMenu: function(module_id)
    {
        var me = this;
        var main_module_container = Ext.getCmp('main-module-container');
        var item = Ext.getCmp('main-menu-btn-module-' + module_id);
        var menus = item._menus;
        
        if (module_id === main_module_container._current_module_id)
        {
            return;
        }

        // Hide the current module
        if (!Ext.isEmpty(main_module_container._current_module_id))
        {
            main_module_container.down('[_module_id=' + main_module_container._current_module_id + ']').setVisible(false);
        }

        // Initialize content
        if (!item._initialized)
        {
            item._initialized = true; 
            me.addModule(module_id, menus);
        }
        else
        {
            // Show module
            main_module_container.down('[_module_id=' + module_id + ']').setVisible(true);            
        }

        main_module_container._current_module_id = module_id;
    },
    
    addModule: function(module_id, raw_menus)
    {
        var me = this;
        var main_module_container = Ext.getCmp('main-module-container');

        var menus = me.getUIMenus(module_id, raw_menus);

        main_module_container.add({
            xtype: 'panel',
            _module_id: module_id,
            layout: 'border',
            bodyStyle: {
                'background-color': 'white'
            },    
            items:
            [
                {
                    xtype: 'panel',
                    region: 'north',
                    items:
                    [
                        {
                            xtype: 'panel',
                            hidden: Ext.isEmpty(menus),
                            margin: '0 0 10 0',
                            tbar: {
                                overflowHandler: 'menu',                    
                                items: menus
                            }
                        },
                        {
                            xtype: 'panel',
                            hidden: !Ext.isEmpty(menus),
                            html: "<font color=\"red\">" + "I do not have permission to access any menus of this module" + "</font>"
                        },
                        {
                            xtype: 'panel',
                            itemId: 'main_module_breadscrumb' + '_' + module_id,
                            hidden: true,
                            margin: '0 0 5 0',
                            html: ''
                        }
                    ]
                },
                {
                    xtype: 'tabpanel',
                    itemId: 'main_module_centerpanel' + '_' + module_id,
                    _current_menu_id: '',
                    region: 'center',
                    layout: 'fit',
                    width: '100%',
                    height: '100%',
                    plugins: 'tabreorderer',
                    cls: 'main-tabpanel-disabled',
                    items: []
                }                    
            ]
        });
                
        // Setting client translation stores
        me.setLangStoreByModule(module_id);
        
        // In some cases, we need translations of other modules
        if (module_id === 'reporting')
        {
            me.setLangStoreByModule('ecommerce');
        }
    },
    
    setLangStoreByModule: function(module_id)
    {
        var trans_store;
        var controller = 'App.modules.' + module_id + '.backend.UI.controller.' + module_id;
        trans_store = App.app.getController(controller).getLangStore();
        //console.log(controller, trans_store);
        if (Ext.isEmpty(trans_store))
        {
            trans_store = Ext.create('App.core.backend.UI.store.translations');
            trans_store.module_id = module_id;
            trans_store.on('load', function(this_store, records, successful, eOpts)
            {
                // Set lang store
                App.app.getController(controller).setLangStore(this_store);

            }, this, {single: true});   
            trans_store.load({params:{module_id: module_id}});              
        }        
    },
    
    getUIMenus: function(module_id, raw_menus)
    {
        var me = this;
        var ret = [];
        var visualize;
        
        Ext.each(raw_menus, function(raw_menu)
        {
            var text = (!raw_menu.label || Ext.isEmpty(raw_menu.label))? raw_menu.alias : raw_menu.label;
            var is_leaf = (!raw_menu.children || Ext.isEmpty(raw_menu.children));
            var model_id = !raw_menu._model? raw_menu.alias : raw_menu._model;
            
            visualize = true;

            var object = {
                _id: raw_menu.alias,
                _breadscrumb: raw_menu.breadscrumb,
                _model_id: model_id,
                text: text,
                iconCls: raw_menu.icon,
                itemId: "module_" + module_id + "_menu_item_" + raw_menu.alias
            };

            if (is_leaf)
            {
                object['handler'] = function(button, e) {
                    me.onClickAnyMenu(module_id, button);
                };
            }
            else
            {
                var items = me.getUIMenus(module_id, raw_menu.children);

                if (Ext.isEmpty(items))
                {
                    visualize = false;
                }
                else
                {
                    object['menu'] = {
                        items: items
                    };                        
                }
            }

            if (visualize)
            {
                ret.push(object); 
            }    
        }); 
        
        return ret;
    },
    
    getModuleBreadscrumb: function(module_id)
    {
        var item = Ext.ComponentQuery.query('#main_module_breadscrumb' + '_' + module_id)[0];
        return item;
    },
    
    getModuleCenterpanel: function(module_id)
    {
        var item = Ext.ComponentQuery.query('#main_module_centerpanel' + '_' + module_id)[0];
        return item;
    },

    onClickAnyMenu: function(module_id, menu)
    {
        var me = this;
        var module_centerpanel = me.getModuleCenterpanel(module_id);
        var module_breadscrumb = me.getModuleBreadscrumb(module_id);
        var menu_id = menu._id;
        var breadscrumb = menu._breadscrumb;
        var model_id = menu._model_id;
        var text = menu.text;
        var alias_widget = module_id + "_" + menu_id;
        var widget, permissions, config, fields;
        
        if (menu_id === 'fileManager')
        {
            widget = Ext.ComponentQuery.query('#fileManager' + '_' + module_id)[0];
        }
        else
        {
            widget = Ext.ComponentQuery.query('[alias=widget.' + alias_widget + ']')[0];
        }
        
        if (widget)
        {
            //console.log('Already initialized');
            
            if (widget.isAction)
            {
                widget.fireAction();
                return;
            }
        
            module_breadscrumb.setHtml(breadscrumb);
            module_centerpanel._current_menu_id = menu_id;
            module_centerpanel.setActiveTab(widget);
            return;
        }
        
        // Get the model and permissions and keep the widget into a collection
        var info_store = Ext.create('App.core.backend.UI.store.info');
        info_store.on('load', function(this_store, records, successful, eOpts)
        {
            if (Ext.isEmpty(records[0]) || !records[0].data.success)
            {
                Ext.MessageBox.show({
                   title: 'Error',
                   msg: records[0].data.message,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
                return;
            }

            permissions = records[0].data.permissions;
            config = 
            {
                breadscrumb: breadscrumb,
                permissions: permissions
            }; 

            if (menu_id === 'fileManager')
            {
                config.module_id = module_id;
                config.enableSelectedEvent = false;
                widget = Ext.widget('fileManager', {
                    closable: true,
                    config: config
                });                     
            }
            else
            {
                fields = records[0].data.fields;
                if (fields !== '')
                {
                    config.module_id = module_id;
                    config.model = 
                    {
                        id: model_id,
                        fields: fields
                    };
                }    
                
                widget = Ext.widget(alias_widget, {
                    title: text,
                    closable: true,
                    config: config
                });

                if (widget.isWindowed)
                {
                    return;
                }

                if (widget.isAction)
                {
                    widget.fireAction();
                    return;
                }              
            }

            // Add widget to center panel
            module_breadscrumb.setHtml(breadscrumb);
            module_centerpanel.add(widget);
            if (Ext.isEmpty(module_centerpanel._current_menu_id))
            {
                module_centerpanel.removeCls('main-tabpanel-disabled').addCls('main-tabpanel-enabled');
            }
            module_centerpanel._current_menu_id = menu_id;
            module_centerpanel.setActiveTab(widget);

        }, this, {single: true});  
        info_store.load({
            params: {
                module_id: module_id,
                model_id: model_id,
                menu_id: menu_id,
                start: 0,
                limit: 9999
            }
        });    
            
    },
         
    logout: function(element, e)
    {
        var me = this;
        
        Ext.MessageBox.show({
            title: app_title,
            msg: me.trans('logout_are_you_sure'),
            buttons: Ext.MessageBox.YESNO,
            fn: function(result)
            {
                if(result == 'yes')
                {
                    Ext.Ajax.request({
                        url: 'index.php',
//                        method: 'GET',
                        params: {
                            controller: 'core\\backend\\controller\\main',
                            method: 'setLogout'
                        },
                        success: function(response)
                        {
                            window.location.reload();
                        },
                        failure: function(response)
                        {
                            window.location.reload();                        
                        }
                    });
                }
            },
            animateTarget: element,
            icon: Ext.MessageBox.QUESTION
        });
                           
    },
            
    trans: function(id)
    {
        var lang_store = App.app.getController('App.core.backend.UI.controller.common').getLangStore();
        return App.app.trans(id, lang_store);
    }
});