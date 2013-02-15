/**
 * TypoGento Rewriter.
 * 
 * Rewrites Magento forms and Ajax request to ensure that all parameters passed to the server are wrapped by the extension key.
 * 
 * @class Rewriter
 * @author Artus Kolanowski <artus@ionoi.net>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
var Rewriter = Class.create({
	/**
	 * Rewrites a form element.
	 * 
	 * @constructor
	 * @param {Element} element The form to rewrite
	 */
	initialize: function(element) {
		// check form
		if (!Object.isElement(element) 
			|| element.readAttribute('data-rewriter')) {
			// nothing to do
			return;
		}
		// set form
		this.element = element;
		// check action
		if (this.element.readAttribute('method') 
			&& this.element.readAttribute('action')
			&& this.element.method.toLowerCase() == 'get') {
			// parse action
			var action = URI.parse(this.element.action);
			// parse query
			var query = URI.parseQuery(action.query);
			// rewrite query
			$H(query).each(function(pair) {
				this.element.insert({
					bottom: new Element('input', {
						type: 'hidden', 
						name: pair.key, 
						value: pair.value
					})
				});
			}, this);
			// rewrite action
			this.element.action = this.element.action.replace('?' + action.query, '');
		}
		// rewrite controls
		this.element.select('input,button,select,textarea').each(function(element) {
			element.writeAttribute('name', Rewriter.rewrite(element.name));
		}, this);
		// save state
		this.element.writeAttribute('data-rewriter', 'done');
	}
});
Object.extend(Rewriter, {
	/**
	 * Prefix used for rewriting.
	 * 
	 * @class Rewriter
	 * @static
	 */
	prefix: 'tx_typogento',
	/**
	 * Rewrites a parameter.
	 * 
	 * @class Rewriter
	 * @static
	 * @param {String} The parameter to rewrite
	 * @returns {String} The rewritten parameter
	 */
	rewrite: function(parameter) {
		// check parameter
		if (Object.isString(parameter) 
			&& parameter.slice(0, Rewriter.prefix.length) != Rewriter.prefix) {
			// set parts
			var a = parameter,
				b = '';
			// check index
			if (a.indexOf('[') != -1) {
				// set index
				b = a.slice(a.indexOf('['), a.length);
				a = a.slice(0, a.indexOf('['));
			}
			// rewrite parameter
			parameter = Rewriter.prefix + '[' + a + ']' + b;
		}
		return parameter;
	},
	/**
	 * Injects the rewriter into the Prototype framework.
	 * 
	 * @class Rewriter
	 * @static
	 */
	inject: function() {
		// check body
		if ($$('body')[0].readAttribute('data-rewriter')) {
			// nothing to do
			return;
		}
		// overwrite ajax requests
		Ajax.Request.addMethods({
			// rewrite requests
			initialize: function($super, url, options) {
				// check rewriting possible
				if (options && options.parameters && !options.rewrite) {
					// parse parameters
					var query = Object.isString(options.parameters) ?
						URI.parseQuery(options.parameters) : 
						Object.isHash(options.parameters) ? 
							options.parameters.toObject() : 
							options.parameters;
					// clear parameters
					options.parameters = {};
					// rewrite parameters
					$H(query).each(function(parameter) {
						this[Rewriter.rewrite(parameter.key)] = parameter.value;
					}, options.parameters);
				}
				// perform request
				$super(options);
				this.transport = Ajax.getTransport();
				this.request(url);
			}
		});
		// save state
		$$('body')[0].writeAttribute('data-rewriter', 'done');
	}
});
