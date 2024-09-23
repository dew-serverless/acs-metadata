# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

- Organized API metadata using the structured path format: `<lang>/<product>/<version>/api-docs.json`
- Organized product list under the following path: `<lang>/products.json`
- Supported language codes (`<lang>`) include:
  - "EN_US" (normalized to "en_us")
  - "ZH_CN" (normalized to "zh_cn")
- The `<product>` values are extracted from the `code` field in the items of `products.json`, normalized to lowercase
- The _VERSION_ file reflects the last metadata update in `YYYYMMDD` format

[unreleased]: https://github.com/dew-serverless/acs-metadata