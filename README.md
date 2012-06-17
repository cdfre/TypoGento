# TypoGento

This project is a fork of Flagbit's [TypoGento](http://www.typogento.com/) licensed under the GNU General Public License (GPL-2.0) Version 2.

## What's new?

The following features are introduced:

* TYPO3 cache for Magento blocks 
* Integration of the Magento compiler
* TYPO3 cache for Magento API requests (SOAP)
* Magento page meta access through TypoScript
* RealURL rewrites for Magento products
* Dual master replication of Magento customers and TYPO3 users

Beside them, the following features have improved:

* Autoloader for the Magento framework
* Single sign-on to Magento and TYPO3
* Integration of Magento HTML header
* Routing between Magento URLs and TYPO3 URLs
* Handling Magento Redirects and Ajax Responses

## Prerequisites

TypoGento is distributed as an extension package for Magento and TYPO3. As such, installing TypoGento requires you to have already a running Magento and TYPO3 on your server:

* [Magento Installation Guide](http://www.magentocommerce.com/wiki/1_-_installation_and_configuration/magento_installation_guide)
* [TYPO3 Installation Guide](http://typo3.org/documentation/document-library/installation/doc_guide_install/current/)

The minimum requirements for a installation are:

* PHP 5.3+
* Magento 1.6+
* TYPO3 4.5+

## Installation

The installation of this fork is very similar to Flagbit's TypoGento (see [how to install TypoGento](http://www.typogento.com/documentation/how-to-install-typogento.html)). It is highly recommended become familiar with the administration of Magento and TYPO3 before you start installing TypoGento:

1. [Download](https://github.com/witrin/TypoGento/zipball/develop) the package
2. Unpack the downloaded package
3. Install the Magento module
 1. Place the content of [`src/Magento`](https://github.com/witrin/TypoGento/tree/develop/src/Magento) into the Magento root directory
 2. If caching is enabled, clear `Configuration`, `EAV types and attributes` and `Web Services Configuration` in `System > Cache Management`
 3. Grant access to `TypoGento Settings` for the `Administrators` in `System > Permissions > Roles` 
 4. Setup the configuration in `System > Configuration > TypoGento`
 5. Create the Magento API account for TypoGento in `System > Web Services > User`
4. Install the TYPO3 extension
 1. Place the content of [`src/TYPO3`](https://github.com/witrin/TypoGento/tree/develop/src/TYPO3) into your TYPO3 root directory
 2. Activate the extension in `Admin Tools > Extension Manager`, update the database and setup the configuration
 3. Setup the Magento role for the TYPO3 backend accounts in `Edit > Extended > Magento Role`

## Usage

The usage of this fork is currently also similar to Flagbit's TypoGento. This means you can put any [Magento block](http://www.magentocommerce.com/design_guide/articles/magento-design-terminologies4#term-blocks) on a TYPO3 page with the frontend plugin (`tx_typogento_pi1`). The only restriction is that all Magento blocks you use on one TYPO3 page must be available within the [Magento route path](http://www.magentocommerce.com/wiki/5_-_modules_and_development/reference/geturl_function_parameters) you choose for the TYPO3 page; what is in turn dependent from the [Magento layout](http://www.magentocommerce.com/design_guide/articles/intro-to-layouts).

You should prefer using TypoScript to put Magento blocks beside `content` (i.e. `top.search`) on your TYPO3 pages:
```text
temp.search = USER
temp.search {
	
	 # Render Magento block 'top.search'
	userFunc = tx_typogento_pi1->main
	block = top.search
}
```

The Magento HTML header can be integrated easily as follows into a TYPO3 page using `config.tx_typogento.header`:

```text
config.tx_typogento {
	
	header = 1
	header {
		
		 # Render Magento block 'head'
		block = head
		
		 # Compress resources (see http://wiki.typo3.org/TSref/CONFIG)
		compressJs = 1
		compressCss = 1
		
		 # Import resources 
		importJs = 1
		importCss = 1
		
		 # Enable register:tx_typogento.header.<field>
		register = 1
		register.fields = title,description
	}
```

## Routing

Before you start putting Magento content on a TYPO3 page, you must to set up the routing system for it, so that TypoGento is able to send a TYPO3 URL to Magento and render later Magento URLs on the TYPO3 frontend. These settings are not bound by the content objects, but apply to the entire page, and are thus stored in `config.tx_typogento`.

It might be helpful to configure the so-called content-plugin for the further use of its configuration (Flexform) in the routes section.

```text
config.tx_typogento {
	
	content {
		
		 # Page column 'normal'
		column = 0
	
		 # First frontend plugin
		position = 0
	
		 # Enable register:tx_typogento.content.<field>
		register = 1
		register.fields = id,route,controller,action,cache
	}
}
```

### Dispatch
The dispatch section transforms TYPO3 URLs into Magento URLs. Remark that the HTTP GET variables namespace will be transfered from `tx_typogento[<name>]` to `<name>` between `filter` and `target`:

```text
config.tx_typogento {
	
	routes {
			
		10 {
			
			 # Set the dispatch section
			section = dispatch
			
			 # The default priority is 0
			priority = 1
		
			 # The route will taken if the filter matches and the route has the 
			 # highest priority (see http://wiki.typo3.org/TSref/if)
			filter {
				 # Match for a TYPO3 page with the id {$pid}
				value.data = TSFE:id
				equals = {$pid}
			}
		
			 # The resulting URL of this external typolink will be dispatched 
			 # to Magento (see http://wiki.typo3.org/TSref/typolink)
			target {
				 # The dispatch section requires always external links
				parameter.cObject = TEXT
				parameter.cObject {
					 # Targets the Magento product view
					wrap = mage:/catalog/product/view/id/|/
					data = GP:id // register:tx_typogento.content.id
					if.isTrue.data = GP:id // register:tx_typogento.content.id
				}
				 # Also available for external links
				addQueryString = 1
				addQueryString.exclude = route,controller,action
			}
		}
	}
}
```

### Render
The render section transforms Magento URLs into TYPO3 URLs. Remark that the HTTP GET variables namespace will be transfered from `<name>` to `tx_typogento[<name>]` between `filter` and `target`:

```text
config.tx_typogento {
	
	routes {
		
		20 {
			
			 # Set the render section
			section = render
			
			 # The default priority is 0
			priority = 1
			
			 # The route will taken if the filter match and the route has the 
			 # highest priority (see http://wiki.typo3.org/TSref/if)
			filter {
				 # Match for all Magento URLs starting with '/catalog/product/'
				value.dataWrap = /{GP:route}/{GP:controller}/
				equals = /catalog/product/
			}
			
			 # The resulting URL of this internal typolink will be rendered 
			 # on the TYPO3 frontend (see http://wiki.typo3.org/TSref/typolink)
			target {
				 # Targets a TYPO3 page with the id {$pid}
				parameter = {$pid}
				 # Remove route path from the URL
				addQueryString = 1
				addQueryString.exclude = tx_typogento[route],tx_typogento[controller],tx_typogento[action]
				useCacheHash = 1
			}
		}
	}
}
```

## Limitations

* Multible [Magento websites](http://www.magentocommerce.com/design_guide/articles/magento-design-terminologies4#term-website) are currently not supported, but one website with multiple Magento store views.
* Magento session ids (SID) in TYPO3 frontend URLs are currently not supported.
* The TypoScript interface for categories has not been adjusted, and is therefore not functional.

## Contributing

Please report issues on the [GitHub issue tracker](https://github.com/witrin/TypoGento/issues). Patches are preferred as GitHub pull requests.

## License

The source code for this project is distributed under the [GNU General Public License (GPL-2.0) Version 2](http://opensource.org/licenses/gpl-2.0.php) and is available for download at [GitHub](https://github.com/witrin/TypoGento/).