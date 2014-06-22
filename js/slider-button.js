(function() {
    tinymce.create('tinymce.plugins.SisSlider', {
 
        init : function(ed, url) {
            ed.addCommand('sisslidertriger', function() {
                return_text = "[slider][slides image_link=''][slides image_link=''][/slider]";
                ed.execCommand('mceInsertContent', 0, return_text);
            });

           
            ed.addButton('sisslidertriger', {
                title : 'Insert slider shortcode',
                cmd : 'sisslidertriger',
                image : url + '/slider.png'
            });

        },
        createControl : function(n, cm) {
            return null;
        },
    });

    // Register plugin
    tinymce.PluginManager.add('sisslidebutton', tinymce.plugins.SisSlider);
})();