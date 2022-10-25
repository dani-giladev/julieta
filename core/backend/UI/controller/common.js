Ext.define('App.core.backend.UI.controller.common', {
    extend: 'Ext.app.Controller',    

    requires: [
        'App.core.backend.UI.model.globalConfig',
        'App.core.backend.UI.model.translation',
        'App.core.backend.UI.model.country',
        'App.core.backend.UI.model.language',
        
        'App.core.backend.UI.store.globalConfig',
        'App.core.backend.UI.store.translations',
        'App.core.backend.UI.store.countries',
        'App.core.backend.UI.store.languages'
    ],

    globalConfigStore: null,
    langStore: null,
    availableLangs: [],
    
    init: function() 
    {
        this.control({

        }); 
    },
    
    getGlobalConfigStore: function()
    {
        return this.globalConfigStore;
    },
            
    setGlobalConfigStore: function(global_config_store)
    {
        this.globalConfigStore = global_config_store;
    },
            
    getGlobalConfigValue: function(id)
    {
        var store = this.getGlobalConfigStore();
        var record = store.getById(id);
        if (!record)
        {
            console.log('The key: "'+id+'" does not exist in the global config store');
            console.log(store);
            return '?';
        }
        var ret = record.get('val');
        return ret;
    },
            
    getLangStore: function()
    {
        return this.langStore;
    },
            
    setLangStore: function(lang_store)
    {
        this.langStore = lang_store;
    },
            
    getAvailableLangs: function()
    {
        return this.availableLangs;
    },
            
    setAvailableLangs: function(langs)
    {
        this.availableLangs = langs;
    },
            
    trans: function(id)
    {
        var lang_store = this.getLangStore();        
        return App.app.trans(id, lang_store);
    },
            
    alertInitMaintenance: function(config)
    {
        if (typeof config.module_id === 'undefined')
        {
            Ext.MessageBox.show({
               title: 'Error',
               msg: 'The model is not properly defined.',
               buttons: Ext.MessageBox.OK,
               icon: Ext.MessageBox.ERROR
            });
        }        
    },
    
    cloneStore: function(source) {
//        var target = Ext.create ('Ext.data.Store', {
//            model: source.model
//        });
//
//        Ext.each (source.getRange (), function (record) {
//            var newRecordData = Ext.clone (record.copy().data);
//            var model = new source.model (newRecordData, newRecordData.id);
//
//            target.add (model);
//        });
//
//        return target;
        
	var records = [];
	var newStore = new Ext.data.Store(
	    {
		model : source.model
	    });
	source.each(
	    function (r)
	    {
		records.push (r.copy());
	    });
	
	newStore.loadRecords(records);
	return newStore;        
    },   
    
    cloneObject: function(object, result)
    {      
        var me = this;
        
        if (Ext.isEmpty(result))
        {
            result = {};
        }
        
        for(var key in object) {
//            console.log(key);
            if (key === '__proto__')
            {
                //console.log(key, object[key]);
            }
            else
            {
                var value = object[key];
                if (Ext.isObject(value))
                {
                    result[key] = me.cloneObject(value);
                }
                else
                {
                    result[key] = value;
                }                 
            }
        }
        
        return result;
    },
    
    areEqualObjects: function(object1, object2)
    {   
        var me = this;
        
        for(var key in object1) {
            var value = object1[key];
            if (Ext.isObject(value))
            {
                if (!me.areEqualObjects(value, object2[key]))
                {
                    return false;
                }
            }
            else
            {
                if (value !== object2[key])
                {
                    return false;
                }
            }
        }     
        
        return true;
    },
    
    getSize: function()
    { 
        var ret = {};
        var w = window,
            d = document,
            e = d.documentElement,
            g = d.getElementsByTagName('body')[0],
            x = w.innerWidth || e.clientWidth || g.clientWidth,
            y = w.innerHeight|| e.clientHeight|| g.clientHeight;
        ret.width = x;
        ret.height = y;
        return ret;
    },
    
    copyMissingProperties: function(object, objectToCopy)
    {      
        var me = this;
        
        for(var key in objectToCopy) {
            var value = objectToCopy[key];
            if (Ext.isEmpty(object[key]))
            {
                if (Ext.isObject(value))
                {
                    object[key] = me.cloneObject(value);
                }
                else
                {
                    object[key] = value;
                }
            } 
            else
            {
                if (Ext.isObject(value))
                {
                    me.copyMissingProperties(object[key], value);
                }
            }
        }     
        
    },
    
    /* Use of struct method:
     * 
     * var item = App.app.getController('App.core.backend.UI.controller.common').struct("fieldname1 fieldname2 fieldname3");
     * var row = new item(1, 'john', 'au');
     * alert(row.fieldname1);
     * 
     */
    struct: function (names)
    {
        var names = names.split(' ');
        var count = names.length;
        function constructor()
        {
            for (var i = 0; i < count; i++)
            {
                this[names[i]] = arguments[i];
    }
        }
    
        return constructor;
    },
    
    convert2URLText: function(text)
    {
        var text = text.toLowerCase();

        //text = text.replace(/[áàäâå]/g, "a");
        text = text.replace(/[\u00E1\u00E0\u00E4\u00E2\u00E5\u00E3]/g, "a");      
        //text = text.replace(/[éèëê]/g, "e");
        text = text.replace(/[\u00E9\u00E8\u00EB\u00EA]/g, "e");
        //text = text.replace(/[íìïî]/g, "i");
        text = text.replace(/[\u00ED\u00EC\u00EF\u00EE]/g, "i");
        //text = text.replace(/[óòöô]/g, "o");        
        text = text.replace(/[\u00F3\u00F2\u00F6\u00F4]/g, "o");
        //text = text.replace(/[úùüû]/g, "u");
        text = text.replace(/[\u00FA\u00F9\u00FC\u00FB]/g, "u");
        //text = text.replace(/[ýÿ]/g, "y");
        text = text.replace(/[\u00FD\u00FF]/g, "y");
        //text = text.replace(/[ñ]/g, "n");
        text = text.replace(/[\u00F1]/g, "n");
        //text = text.replace(/[ç]/g, "c");
        text = text.replace(/[\u00E7]/g, "c");
        text = text.replace(/['"]/g, "");
        text = text.replace(/[^a-zA-Z0-9-]/g, "-");
        text = text.replace(/\s+/g, "-");
        text = text.replace(/(_)$/g, "-");
        text = text.replace(/^(_)/g, "-");
        text = text.replace("----", "-");
        text = text.replace("---", "-");
        text = text.replace("--", "-");

        return text;
    },
    
    removeHtmlTags: function(html)
    {
        var me = this;
        
        Ext.Ajax.request(
        {
            type: 'ajax',
            url : 'index.php',
            method: 'POST',
            params: {
                controller: 'core\\backend\\controller\\backend', 
                method: 'removeHtmlTags',
                html: html
            },
            waitMsg : 'Removing html tags',
            success: function(response, opts)
            {
                var new_html = response.responseText;
                me.fireEvent('removedHtmlTags', true, new_html);
            },
            failure: function(form, data)
            {
                var obj = Ext.JSON.decode(data.response.responseText);
                Ext.MessageBox.show({
                   title: 'Removing html tags', //me.trans('xxx'),
                   msg: obj.data.result,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
                me.fireEvent('removedHtmlTags', false, null);
            }
        });          
    },
    
    translate: function(source_lang_code, target_lang_code, source_text)
    {
        var me = this;
        
        Ext.Ajax.request(
        {
            type: 'ajax',
            url : 'index.php',
            method: 'POST',
            params: {
                controller: 'core\\backend\\controller\\backend', 
                method: 'translate',
                source_lang_code: source_lang_code,
                target_lang_code: target_lang_code,
                source_text: source_text
            },
            waitMsg : 'Removing html tags',
            success: function(response, opts)
            {
                var translation = response.responseText;
                me.fireEvent('translate', true, translation);
            },
            failure: function(form, data)
            {
                var obj = Ext.JSON.decode(data.response.responseText);
                Ext.MessageBox.show({
                   title: 'Removing html tags',
                   msg: obj.data.result,
                   buttons: Ext.MessageBox.OK,
                   icon: Ext.MessageBox.ERROR
                });
                me.fireEvent('translate', false, null);
            }
        });          
    },
    
    getActionMenuButtonForHtmlManagement: function(tabItemId)
    {
        var me = this;
        var option = 2;
        
        if (option === 1)
        {
            return {
                xtype: 'button',
                text: me.trans('actions'),
                margin: '5 0 0 0',        
                style: 'float:right;',
                menu: {
                    items: 
                    [
                        me.getCleanHtmlCodeButton(tabItemId, option),
                        me.getCopyAndTranslateFromMenu(tabItemId, option)
                    ]
                }            
            };            
        }
        else
        {
            return {
//                xtype: 'panel',
                xtype: 'toolbar',
                itemId: tabItemId + '_wrapper',
                layout: 'hbox',
                margin: '5 0 0 0',
                items:
                [
                    { xtype: 'tbfill' },
                    me.getCleanHtmlCodeButton(tabItemId, option)
                ],                    
                listeners: {
                    afterrender: function(container, eOpts)
                    {
                        var task = new Ext.util.DelayedTask(function(){
                            me.createCopyAndTranslateButtons(tabItemId, option);
                        });        
                        task.delay(500);                        
                    }
                }
            };            
        }

    },
    
    getCleanHtmlCodeButton: function(tabItemId, option)
    {
        var me = this;
        
        var ret = {
            text: me.trans('clean_html_code'),
            handler: function(button, e)
            {
                var tab = Ext.ComponentQuery.query('#' + tabItemId)[0];
                var activeTab = tab.getActiveTab();
                var html = activeTab.getValue();
                me.on('removedHtmlTags', function(success, html)
                {
                    if (success)
                    {
                        activeTab.setValue(html);
                    }
                }, this, {single: true});
                me.removeHtmlTags(html);
            }
        };
        
        if (option === 2)
        {
            ret.xtype = 'button';
        }
        
        return ret;
    },
    
    createCopyAndTranslateButtons: function(tabItemId, option)
    {
        var me = this;
        var container = Ext.ComponentQuery.query('#' + tabItemId + '_wrapper')[0];
        var tab = Ext.ComponentQuery.query('#' + tabItemId)[0];
        var items = tab.items.items;                        
        Ext.each(items, function(item)
        {
            var button = me.getCopyAndTranslateFromButton(tab, items, item, option);
            container.add(button);
        });        
    },
    
    getCopyAndTranslateFromMenu: function(tabItemId, option)
    {
        var me = this;
        
        return {
            text: me.trans('copy_and_translate_from'),
            menu: {
                items: []
            },                    
            listeners: {
                render: function(button, eOpts)
                {
                    var tab = Ext.ComponentQuery.query('#' + tabItemId)[0];
                    var items = tab.items.items;

                    var menu = button.down('menu');
                    Ext.each(items, function(item)
                    {
                        var button = me.getCopyAndTranslateFromButton(tab, items, item, option);
                        menu.add(button);
                    });                        
                }
            }
        };
    },
    
    getCopyAndTranslateFromButton: function(tab, items, item, option)
    {
        var me = this;
        
        var text = item.title;
        if (option === 2)
        {
            text = me.trans('copy_and_translate_from') + " " + text;
        }
        
        var ret = {
            text: text,
            _lang_code: item._lang_code,
            _lang_name: item.title,
            margin: '0 0 0 5',
            handler: function(optionbutton, e)
            {
                var source_lang_code = optionbutton._lang_code;
                var activeTab = tab.getActiveTab();
                var target_lang_code = activeTab._lang_code;

                var source_text = '';
                Ext.each(items, function(item) {
                    if (source_lang_code ===  item._lang_code)
                    {
                        source_text = item.value;
                        return false;
                    }
                });

                if (Ext.isEmpty(source_text))
                {
                    return;
                }

                me.on('translate', function(success, translation)
                {
                    if (success)
                    {
                        activeTab.setValue(translation);
                    }
                }, this, {single: true});                        
                me.translate(source_lang_code, target_lang_code, source_text);
                //console.log(source_lang_code, target_lang_code, source_text);
            }                                        
        };
        
        if (option === 2)
        {
            ret.xtype = 'button';
        }
        
        return ret;
    },
    
    capitalizeFirstLetter: function(string)
    {
        var lower = string.toLowerCase();
        return lower.replace(/(^| )(\w)/g, function(x) {
          return x.toUpperCase();
        });
    },
    
    getRandom: function(min, max)
    {
        return Math.floor(Math.random() * max) + min;        
    },
    
    getComponentQuery: function(id, config)
    {
        var itemId = Ext.isEmpty(id)? config.itemId : (config.itemId + '_' + id);
//        console.log(itemId);
        return Ext.ComponentQuery.query('#' + itemId)[0];
    }
        
});