# queue-rules Specification

## Purpose
Rules on message queue configuration that catch topics which block the publisher, contrary to what asynchronous queues are used for.

## Requirements

### Requirement: X-MQ-01 synchronous topic by default
X-MQ-01 SHALL report, as `warning`, a `<topic>` in `communication.xml` that declares `schema`, or both `request` and `response`, without `is_synchronous="false"`. Such a topic is synchronous by default; on AMQP it is published through the RPC publisher, which waits for a reply.

#### Scenario: Schema without flag
- **WHEN** `communication.xml` has `<topic name="acme.export" schema="Acme\Export\Api\ExportInterface::run"/>`
- **THEN** X-MQ-01 reports a warning

#### Scenario: Explicitly asynchronous
- **WHEN** the same topic has `is_synchronous="false"`
- **THEN** X-MQ-01 reports nothing

#### Scenario: Request only
- **WHEN** a topic declares `request` without `response` and without `schema`
- **THEN** X-MQ-01 reports nothing
