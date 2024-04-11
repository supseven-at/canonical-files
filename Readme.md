# Canonical URL header for files

This TYPO3 extension adds canonical url headers to files (images, PDFs...),
depending on the file storage the files are located in. Storages can be 
linked to specific sites (respectively site configurations), so all files
located in that storage are delivered with an additional canonical header 
pointing to the domain of the according site.

Let's say a TYPO3 instance serves two domains, [example.at](https://example.at)
and [example.de](https://example.de), wich have two file storages defined 
respectively, `storage-at` and `storage-de`. Then a file provided for 
download on domain [example.at](https://example.at) but physically located in 
`storage-de` will be delivered with the canonical header
`https://example.de/fileadmin/....`

## Installation

Add the package to your composer.json via

```shell
composer require supseven/canonical-files
```

The extension adds the field `tx_canonical_files_site_identifier` to the
table `sys_file_storage`. Compare database (database migration) if necessary.

## Configuration

Add a file storage for each available site configuration (if applicable)
and set the field `Site` in the tab `Canonical Files` accordingly. All files
stored within such a file storage will get the base, respectively base
variant, of this site configuration as canonical header.

To make this happen, files have to be routed through TYPO3: Add the 
following lines to your project's .htaccess file (amend 
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
