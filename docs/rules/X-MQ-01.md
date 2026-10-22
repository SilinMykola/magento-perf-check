# X-MQ-01: message queue topic synchronous by default

| | |
|---|---|
| Severity | `warning` |
| Status | default set |
| Analyzer | XML (`communication.xml`) |

## What it catches

A `<topic>` that declares `schema`, or both `request` and `response`, without `is_synchronous="false"`.

## Why it hurts performance

Topics are synchronous unless they say otherwise. On AMQP (RabbitMQ), a synchronous topic is published through the RPC publisher, which waits for the consumer to process the message and reply. The request that publishes it is blocked for that time, which defeats the purpose of putting the work in a queue.

## Example

Bad:

```xml
<topic name="acme.export" schema="Acme\Export\Api\ExportInterface::run"/>
```

Good:

```xml
<topic name="acme.export" schema="Acme\Export\Api\ExportInterface::run" is_synchronous="false"/>
```

## How to suppress

`<!-- perf-ignore X-MQ-01: reason -->` directly before the `<topic>`, when the caller does need the response.

## Known false positives

- Real request-response integrations that need the reply.

## Basis

| Source | Level | What it confirms |
|---|---|---|
| [Communication Converter.php#L127-L153](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/Communication/Config/Reader/XmlReader/Converter.php#L127-L153) | B | Topics are synchronous unless `is_synchronous="false"`. |
| [Rpc/Publisher.php#L105-L125](https://github.com/magento/magento2/blob/a385d02f60ee6dfa052f12cdc7d82e956581fba5/lib/internal/Magento/Framework/MessageQueue/Rpc/Publisher.php#L105-L125) | B | The RPC publisher waits for the reply. |
| [Adobe: Message queue configuration](https://developer.adobe.com/commerce/php/development/components/message-queues/configuration) | A | Documents `is_synchronous` on topics. |

Verified 2026-10-21 (see [source audit](../source-audit.md)).
