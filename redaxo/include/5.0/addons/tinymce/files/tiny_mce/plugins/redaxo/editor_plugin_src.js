/**
 * @author Andreas Eberhard
 * http://andreaseberhard.de - http://projekte.andreaseberhard.de/tinyredaxo
 */

(function() {
	// Load plugin specific language pack
	tinymce.PluginManager.requireLangPack('redaxo');

	tinymce.create('tinymce.plugins.redaxo', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function(ed, url) {

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceRedaxoEmail', function() {
			var se = ed.selection;

				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A'))
					return;
					
				ed.windowManager.open({
					file : url + '/redaxoEmail.html',
					width : 400 + parseInt(ed.getLang('redaxo.redaxoEmail_delta_width', 0)),
					height : 160 + parseInt(ed.getLang('redaxo.redaxoEmail_delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register redaxoEmail button
			ed.addButton('redaxoEmail', {
				title : 'redaxo.redaxoEmail_ButtonDesc',
				cmd : 'mceRedaxoEmail',
				image : url + '/img/redaxoEmail.gif'
			});

			// Add a node change handler, selects the button in the UI when a link is selected
			ed.onNodeChange.add(function(ed, cm, n, co) {
				var se = ed.selection;
				cm.setDisabled('redaxoEmail', co && n.nodeName != 'A');
				cm.setActive('redaxoEmail', 0);
				if (n.nodeName == 'A') {
					href = n.getAttribute('href');
					if (href.indexOf('mailto:') >= 0) {
						cm.setActive('redaxoEmail', 1);
					}
				} else if (ed.dom.getParent(se.getNode(), 'A')) {
					el = ed.dom.getParent(se.getNode(), 'A');
					href = el.getAttribute('href');
					if (href.indexOf('mailto:') >= 0) {
						cm.setActive('redaxoEmail', 1);
					}
				}
			});


// -------------------------------------------------------------------------- //


			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
			ed.addCommand('mceRedaxoMedia', function() {
			var se = ed.selection;

				// No selection and not in link
				if (se.isCollapsed() && !ed.dom.getParent(se.getNode(), 'A'))
					return;
					
				ed.windowManager.open({
					file : url + '/redaxoMedia.html',
					width : 400 + parseInt(ed.getLang('redaxo.redaxoMedia_delta_width', 0)),
					height : 160 + parseInt(ed.getLang('redaxo.redaxoMedia_delta_height', 0)),
					inline : 1
				}, {
					plugin_url : url, // Plugin absolute URL
					some_custom_arg : 'custom arg' // Custom argument
				});
			});

			// Register redaxoMedia button
			ed.addButton('redaxoMedia', {
				title : 'redaxo.redaxoMedia_ButtonDesc',
				cmd : 'mceRedaxoMedia',
				image : url + '/img/redaxoMedia.gif'
			});

			// Add a node change handler, selects the button in the UI when a link is selected
			ed.onNodeChange.add(function(ed, cm, n, co) {
				var se = ed.selection;
				cm.setDisabled('redaxoMedia', co && n.nodeName != 'A');
				cm.setActive('redaxoMedia', 0);
				if (n.nodeName == 'A') {
					href = n.getAttribute('href');
					if (href.indexOf('files/') >= 0) {
						cm.setActive('redaxoMedia', 1);
					}
				} else if (ed.dom.getParent(se.getNode(), 'A')) {
					el = ed.dom.getParent(se.getNode(), 'A');
					href = el.getAttribute('href');
					if (href.indexOf('files/') >= 0) {
						cm.setActive('redaxoMedia', 1);
					}
				}
			});

		},

		/**
		 * Creates control instances based in the incomming name. This method is normally not
		 * needed since the addButton method of the tinymce.Editor class is a more easy way of adding buttons
		 * but you sometimes need to create more complex controls like listboxes, split buttons etc then this
		 * method can be used to create those.
		 *
		 * @param {String} n Name of the control to create.
		 * @param {tinymce.ControlManager} cm Control manager to use inorder to create new control.
		 * @return {tinymce.ui.Control} New control instance or null if no control was created.
		 */
		createControl : function(n, cm) {
			return null;
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'REDAXO email and filelink',
				author : 'Andreas Eberhard',
				authorurl : 'http://andreaseberhard.de',
				infourl : 'http://projekte.andreaseberhard.de/tinyredaxo',
				version : "1.0"
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('redaxo', tinymce.plugins.redaxo);
})();