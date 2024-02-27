# Canonical URLs for files Module for TYPO3

This TYPO3 extension adds canonical url headers to files (images, PDFs...),
depending on the file storage the files are located in. Storages can be set to
specific sites, so that all files located in that storage get the domain of the
assigned site.

## Installation

Add the package via composer

```shell
composer require supseven/canonical-files
```

The extension adds the field `tx_canonical_files_site_identifier` to the
table `sys_file_storage`. Compare database (database migration) if necessary.

## Configuration

Add a file storages for each available site configuration (if applicable)
and set the field `Site` in the tab `Canonical Files` accordingly. All files
stored within such a file storage will get the base, respectively base
variant, of this site configuration as canonical header.

To make this work, files have to be routed through TYPO3. To make this 
happen, add the following lines to your project's .htaccess file (amend 
accordingly):

```
RewriteCond %{REQUEST_URI} ^/fileadmin
RewriteCond %{REQUEST_FILENAME} \.(pdf|doc|docx|xls|xlsx|ppt|pptx)$
RewriteRule ^.*$ %{ENV:CWD}index.php [QSA,L]
```

## Legal

### License

This package is provided under the GPL v3 license. See the file
[LICENSE](./LICENSE) or <https://www.gnu.org/licenses/gpl-3.0> for details.
