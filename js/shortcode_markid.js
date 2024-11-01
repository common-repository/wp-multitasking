jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.plugin', {
        init: function(ed, url) {
            // Register command for when button is clicked
            ed.addCommand('markid', function() {
                content = '[markid id="Selector_ID" title=""]';

                tinymce.execCommand('mceInsertContent', false, content);
            });

            // Register buttons - trigger above command when clicked
            ed.addButton('button', {title: 'Mark ID', cmd: 'markid', image: url + '/../images/id_icon.png'});
        }
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('button', tinymce.plugins.plugin);
});