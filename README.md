# TypoGento

This project is a fork of Flagbit's [TypoGento](http://www.typogento.com/) licensed under the GNU General Public License (GPL-2.0) Version 2.

## What's new?

The following features are introduced:

* TYPO3 cache for Magento blocks 
* Integration of the Magento compiler
* TYPO3 cache for Magento API requests (SOAP)
* Magento page meta access through TypoScript
* Extbase and Fluid for the TYPO3 extension 

Beside them, the following features have improved:

* Autoloader for the Magento framework
* Integration of Magento HTML header
* Routing between Magento URLs and TYPO3 URLs
* Handling Magento Redirects and Ajax Responses

## Prerequisites

TypoGento is distributed as an extension package for Magento and TYPO3. As such, installing TypoGento requires you to have already a running Magento and TYPO3 on your server:

* [Magento Installation Guide](http://www.magentocommerce.com/wiki/1_-_installation_and_configuration/magento_installation_guide)
* [TYPO3 Installation Guide](http://typo3.org/documentation/document-library/installation/doc_guide_install/current/)

The minimum requirements for a installation are:

* Magento 1.7+
* TYPO3 6.0+

## Installation

The installation of this fork is very similar to Flagbit's TypoGento (see [how to install TypoGento](http://www.typogento.com/documentation/how-to-install-typogento.html)). _It is highly recommended become familiar with the administration of Magento and TYPO3 before you start installing TypoGento_:

### Download

[Download](https://github.com/witrin/TypoGento/zipball/develop) the extension package from GitHub and unzip it. **Please notice that this is the latest development version, which is not ready for production use.**

### Magento module

1. Place the content of [`src/Magento`](https://github.com/witrin/TypoGento/tree/develop/src/Magento) into the Magento root directory
2. Clear the caches in `System > Cache Management` if enabled
    * `Configuration`
	* `EAV types and attributes`
	* `Web Services Configuration` 
3. Grant access to `TypoGento Settings` for the `Administrators` in `System > Permissions > Roles` if necessary
4. Setup the configuration in `System > Configuration > TypoGento`
5. Create the Magento API role for TypoGento in `System > Web Services > SOAP/XML-RPC - Roles`
	* `Catalog > Product > URL Key > List`
	* `Catalog > Product > Retrieve products data`
	* `Catalog > Category > URL Key > List`
	* `Catalog > Category > Retrieve categories tree`
	* `Core > Role > List`
	* `Core > Magento info > Module > List`
	* `Core > Magento info > Module > Controller > List`
	* `Core > Magento info > Module > Controller > Action > List`
	* `Core > Store > List of stores`
	* `TypoGento > Replication > Target > List`
	* `TypoGento > Replication > Source > List`
6. Create the Magento API user for TypoGento in `System > Web Services > SOAP/XML-RPC - Users` by using the previous created Magento API role

### TYPO3 extension

1. Place the content of [`src/TYPO3`](https://github.com/witrin/TypoGento/tree/develop/src/TYPO3) into the TYPO3 root directory
2. Activate the extension in `Admin Tools > Extension Manager`, update the database and setup the configuration
3. Check the installation in `Admin Tools > Reports > Status Report`
4. Setup the Magento role for the TYPO3 backend users in `Edit > Extended > Magento Role`
5. Add the scheduled task for clearing the Magento API cache in `Admin Tools > Scheduler`
6. Add the default TypoGento template to your root template

## Usage

The usage of this fork is currently also similar to Flagbit's TypoGento. This means you can put any [Magento block](http://www.magentocommerce.com/design_guide/articles/magento-design-terminologies4#term-blocks) on a TYPO3 page with the frontend plugin. The only restriction is that all Magento blocks you use on one TYPO3 page must be available within the [Magento route path](http://www.magentocommerce.com/wiki/5_-_modules_and_development/reference/geturl_function_parameters) you choose for the TYPO3 page; what is in turn dependent from the [Magento layout](http://www.magentocommerce.com/design_guide/articles/intro-to-layouts).

You should prefer using TypoScript to put Magento blocks beside `content` (i.e. `top.search`) on your TYPO3 pages:
```text
temp.search < plugin.tx_typogento.widgets.defaultWidget
temp.search {
	 # TypoGento plugin settings
	settings {
		 # Render Magento block 'top.search'
		block = top.search
	}
}
```

## Limitations

* Multible [Magento websites](http://www.magentocommerce.com/design_guide/articles/magento-design-terminologies4#term-website) are currently not supported, but one website with multiple Magento store views.
* Magento session ids (SID) in TYPO3 frontend URLs are currently not supported.
* The TypoScript interface for categories has not been adjusted, and is therefore not functional.
* All aspects of integrating Magento customer accounts into TYPO3 and vice versa need to be refactored.

## Contributing

**Helping hands are very welcome to complete a first stable version of TypoGento 2.0, which is ready for production use. So [contact me](mailto:artus@ionoi.net) directly or use the [GitHub issue tracker](https://github.com/witrin/TypoGento/issues) to get involved.**

Please report issues on the [GitHub issue tracker](https://github.com/witrin/TypoGento/issues). Patches are preferred as GitHub pull requests. Please use the [develop branch](https://github.com/witrin/TypoGento/tree/develop) when sending pull requests.

## License

The source code for this project is distributed under the [GNU General Public License (GPL-2.0) Version 2](http://opensource.org/licenses/gpl-2.0.php) and is available for download at [GitHub](https://github.com/witrin/TypoGento/).