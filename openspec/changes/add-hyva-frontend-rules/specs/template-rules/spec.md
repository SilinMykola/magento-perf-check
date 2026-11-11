## ADDED Requirements

### Requirement: T-05 synchronous external script in a template
T-05 SHALL report a `<script>` element with a `src` attribute and without `async`, `defer` or `type="module"` in a frontend template. It SHALL start as experimental, at `warning`.

#### Scenario: Synchronous script
- **WHEN** a frontend template contains `<script src="https://example.com/widget.js"></script>`
- **THEN** T-05 reports it

#### Scenario: Deferred script
- **WHEN** a frontend template contains `<script defer src="https://example.com/widget.js"></script>`
- **THEN** T-05 reports nothing

### Requirement: T-06 image without dimensions in a template
T-06 SHALL report an `<img>` element in a frontend template that does not have both a `width` and a `height` attribute, written or bound. It SHALL start as experimental, at `info`.

#### Scenario: Image without dimensions
- **WHEN** a frontend template contains `<img src="banner.jpg" alt="">`
- **THEN** T-06 reports it

#### Scenario: Image with dimensions
- **WHEN** a frontend template contains `<img src="banner.jpg" width="1200" height="400" alt="">`
- **THEN** T-06 reports nothing
