# TypoGento

> The plug-in shall not cache at all since the views may contain highly dynamic information 
> and Magento does a lot of caching as well.
> 
>  -- <cite>[Flagbit](https://github.com/Flagbit/TypoGento/issues/1#issuecomment-705602)</cite>

This project is a fork of Flagbit's [TypoGento](http://www.typogento.com/) giving you the choice 
to decide Magento does enough caching for you. Their are already many Magento full page cache solutions 
for a reason. This is more than just that, licensed under the GNU General Public License (GPL-2.0)
Version 2. See the [wiki](https://github.com/witrin/TypoGento/wiki/Overview#wiki-features) gives you a 
brief description about what has changed in this fork.

## Prerequisites

TypoGento is distributed as a extension package for Magento and TYPO3. As such, installing TypoGento 
requires you to have already running Magento and TYPO3 on your server:

* [Magento Installation Guide](http://www.magentocommerce.com/wiki/1_-_installation_and_configuration/magento_installation_guide)
* [TYPO3 Installation Guide](http://typo3.org/documentation/document-library/installation/doc_guide_install/current/)

The minimum requirements for a installation of this fork are:

* PHP version 5.3.1 or higher version
* Magento version 1.6.0 or higher version
* TYPO3 version 4.5.7 or higher version

## Installation

The installation of this fork is very similar to Flagbit's TypoGento (see [how to install TypoGento](http://www.typogento.com/documentation/how-to-install-typogento.html)). 
Before you start it's recommended that you're familiar with the administration of Magento and TYPO3:

1. [Download](https://github.com/witrin/TypoGento/zipball/develop) the package
2. Unpack the downloaded package
3. Place the content of [`src/TYPO3`](https://github.com/witrin/TypoGento/tree/develop/src/TYPO3) 
   into your TYPO3 root directory
4. Place the content of [`src/Magento`](https://github.com/witrin/TypoGento/tree/develop/src/Magento) 
   into your Magento root directory
5. Setup the Magento module in `System->Configuration->TypoGento`
6. Setup the Magento API account in `System->Web Services->User`
7. Setup the TYPO3 extension
8. Setup the Magento group memberships of your TYPO3 backend accounts

## Usage

The usage of this fork is also very similiar to Flagbit's TypoGento. This means you can put any block of 
your Magento layout on a TYPO3 page. The only restriction is that all Magento blocks you use on one TYPO3 
page (through the TYPO3 backend or TypoScript) must be available within the Magento route path you choose 
for the TYPO3 page. See the routing section below for more information how to link Magento route paths with 
TYPO3 pages.

You should prefer using TypoScript to put Magento blocks beside `content` (i.e. `top.search`) on your TYPO3 pages:

	includeLibs.user_tx_weetypogento_pi1 = EXT:wee_typogento_pi1/pi1/class.tx_weetypogento_pi1.php
	/**
	 * You could also use USER_INT in this case the frontend 
	 * plugin wouldn't trigger cache hash checking.
	 */
	temp.search = USER
	temp.search {
		userFunc = tx_weetypogento_pi1->main
		block = top.search
		noWrap = 1
	}

## Routing

Before you start putting Magento content on your TYPO3 pages, you must provide the TypoGento routing system 
with enough data to dispatch the TYPO3 frontend page requests to Magento. See the [wiki](https://github.com/witrin/TypoGento/wiki/Overview#wiki-routing) for a brief 
explanation of how this general works.

	plugin.tx_weetypogento_pi1 {
		/** 
		 * Section for all TypoGento routes.
		 */
		routes {
			/** 
			 * This is a route in the dispatch section. It means TypoGento 
			 * will use the target to dispatch the TYPO3 page request to Magento 
			 * if the filter match and if the route has the highest priority.
			 * Remark that TypoGento will transfer the the GET vars namespace 
			 * from tx_weetypogento[...] to [...] between filter and target and 
			 * vice versa in the links section. The default route priority is 0.
			 */
			10 {
				/** 
				 * This is a just a if function. If this is true and the route 
				 * has the highest priority in its section the target will be processed.
				 */
				filter {
					isTrue = 1
				}
				/** 
				 * This is a just a typolink function. The resulting URL of this external 
				 * typolink will be processed by Magento (e.g. if the TYPO3 frontend request
				 * contains just the id for a TYPO3 page and there is no content plugin set 
				 * through the TYPO3 backend, this results in the Magento route path 
				 * /typogento/page/index/). The typolink function here also provides the 
				 * property .addQueryString but only for Magento GET/POST vars.
				 */
				target {
					parameter.cObject = COA
					parameter.cObject {
						10 = TEXT
						10 {
							wrap = magento:/|/
							value = typogento
							override.data = GP:route // TSFE:config|tx_weetypogento|route
						}
						20 = TEXT
						20 {
							wrap = |/
							value = page
							override.data = GP:controller // TSFE:config|tx_weetypogento|controller
						}
						30 = TEXT
						30 {
							wrap = |/
							value = index
							override.data = GP:action // TSFE:config|tx_weetypogento|action
						}
						40 = TEXT
						40 {
							wrap = id/|/
							data = GP:id // TSFE:config|tx_weetypogento|id
							if.isTrue.data = GP:id // TSFE:config|tx_weetypogento|id
						}
					}
					addQueryString = 1
					addQueryString.exclude = route,controller,action,id
				}
				section = dispatch
			}
			/** 
			 * This is a route in the links section similiar to the example above. 
			 * It means TypoGento will use this if Magento requests an URL during 
			 * block rendering. The target is here just a internal TYPO3 frontend URL.
			 */
			20 {
				filter {
					value.dataWrap = /{GP:route}/{GP:controller}/
					equals = /catalog/product/
				}
				target {
					# constant for a TYPO3 page (pid) for product details
					parameter = {$pid} 
					addQueryString = 1
					# e.g. the page {$pid} has already a frontend plugin for 
					# the content block of /catalog/product/view/ so wee need
					# only the product id there
					addQueryString.exclude = tx_weetypogento[route],tx_weetypogento[controller],tx_weetypogento[action]
					useCacheHash = 1
				}
				section = links
				priority = 1
			}
		}
	}

## Contributing

Please report issues on the [Github issue tracker](https://github.com/witrin/TypoGento/issues). Patches are 
preferred as Github pull requests.

## License

The source code for this project is distributed under the [GNU General Public License (GPL-2.0)
Version 2](http://opensource.org/licenses/gpl-2.0.php) and is available for download at [Github](https://github.com/witrin/TypoGento/).