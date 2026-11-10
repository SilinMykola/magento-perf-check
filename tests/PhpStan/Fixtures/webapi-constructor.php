<?php

// Routes and preferences: tests/Fixtures/Project/indexed/app/code/Acme/Core/etc/webapi.xml and di.xml.

namespace Acme\Pickup\Api {

    use Acme\Pickup\Api\Data\ResultInterface;
    use Acme\Pickup\Api\Data\SearchRequestInterface;
    use Acme\Pickup\Model\Criteria;

    interface PickupSearchInterface
    {
        /**
         * @param \Acme\Pickup\Api\Data\SearchRequestInterface $request
         * @return \Acme\Pickup\Api\Data\ResultInterface
         */
        public function execute(SearchRequestInterface $request): ResultInterface;

        /**
         * @param \Acme\Pickup\Model\Criteria $criteria
         * @return int
         */
        public function count(Criteria $criteria): int;
    }
}

namespace Acme\Pickup\Api\Data {

    interface SearchRequestInterface
    {
        /** @return \Acme\Pickup\Model\SortOrder[]|null */
        public function getSort(): ?array;

        public function getScopeCode(): string;

        /** @return int[] */
        public function getIds(): array;

        /** @return \Acme\Pickup\Api\Data\AreaInterface[] */
        public function getAreas(): array;

        public function getFilter(): ?\Acme\Pickup\Model\Filter;

        public function setFilter(?\Acme\Pickup\Model\Filter $filter): void;

        public function getPage(): \Acme\Pickup\Model\Page;

        /** @return string[] */
        public function getTags(): array;

        /** @return string */
        public function getNote();

        public function isActive(): bool;

        public function getContact(): ContactInterface;

        public function getNotes(): NoteInterface;

        public function getTree(): Tree;

        public function getRoute(): \Acme\Pickup\Model\FastRoute;
    }

    interface ItemInterface
    {
    }

    class Tree
    {
        // self is this class, which is in an Api\Data namespace.
        public function __construct(private ?self $parent)
        {
        }

        public function getParent(): ?self
        {
            return $this->parent;
        }
    }

    interface AreaInterface
    {
        public function getPoint(): \Acme\Pickup\Model\Point;

        public function getRadius(): int;

        /** @return string[] */
        public function getLabels(): array;
    }

    interface ContactInterface
    {
        public function getPoint(): \Acme\Pickup\Model\Point;
    }

    interface NoteInterface
    {
        public function getPoint(): \Acme\Pickup\Model\Point;
    }

    interface ResultInterface
    {
        public function getPoint(): \Acme\Pickup\Model\Point;
    }
}

namespace Acme\Pickup\Model {

    use Acme\Pickup\Api\Data\AreaInterface;
    use Acme\Pickup\Api\Data\ContactInterface;
    use Acme\Pickup\Api\Data\ItemInterface;
    use Acme\Pickup\Api\Data\NoteInterface;
    use Acme\Pickup\Api\Data\ResultInterface;
    use Acme\Pickup\Api\Data\SearchRequestInterface;

    class SortOrder
    {
    }

    class Filter
    {
    }

    class Page
    {
    }

    class Point
    {
    }

    class Registry
    {
    }

    class SearchRequest implements SearchRequestInterface
    {
        /**
         * @param string $scopeCode
         * @param SortOrder[]|null $sort
         * @param int[] $ids
         * @param AreaInterface[] $areas
         * @param Filter|null $filter
         * @param Page $page
         * @param Registry $registry
         * @param array $tags
         * @param bool $active
         */
        public function __construct(
            private string $scopeCode,
            private ?array $sort,
            private array $ids,
            private array $areas,
            private ?Filter $filter,
            private Page $page,
            private Registry $registry,
            private array $tags,
            private $note,
            private \stdClass $active,
            private ContactInterface $contact,
        ) {
        }

        public function getSort(): ?array
        {
            return $this->sort;
        }

        public function getScopeCode(): string
        {
            return $this->scopeCode;
        }

        public function getIds(): array
        {
            return $this->ids;
        }

        public function getAreas(): array
        {
            return $this->areas;
        }

        public function getFilter(): ?Filter
        {
            return $this->filter;
        }

        public function setFilter(?Filter $filter): void
        {
            $this->filter = $filter;
        }

        public function getPage(): Page
        {
            return $this->page;
        }

        public function setPage(Page $page): void
        {
            $this->page = $page;
        }

        public function getRegistry(): Registry
        {
            return $this->registry;
        }

        public function getTags(): array
        {
            return $this->tags;
        }

        public function getNote()
        {
            return $this->note;
        }

        public function isActive(): bool
        {
            return (bool) $this->active;
        }

        public function getContact(): ContactInterface
        {
            return $this->contact;
        }

        public function getNotes(): NoteInterface
        {
            throw new \LogicException();
        }

        public function getTree(): \Acme\Pickup\Api\Data\Tree
        {
            throw new \LogicException();
        }

        public function getRoute(): FastRoute
        {
            throw new \LogicException();
        }
    }

    class Area implements AreaInterface
    {
        public function __construct(private Point $point, private int $radius, private array $labels)
        {
        }

        public function getLabels(): array
        {
            return $this->labels;
        }

        public function getPoint(): Point
        {
            return $this->point;
        }

        public function getRadius(): int
        {
            return $this->radius;
        }
    }

    class Contact implements ContactInterface
    {
        public function __construct(private Point $point)
        {
        }

        public function getPoint(): Point
        {
            return $this->point;
        }
    }

    class CustomContact extends Contact
    {
    }

    class FrontendNote implements NoteInterface
    {
        public function __construct(private Point $point)
        {
        }

        public function getPoint(): Point
        {
            return $this->point;
        }
    }

    class Result implements ResultInterface
    {
        public function __construct(private Point $point)
        {
        }

        public function getPoint(): Point
        {
            return $this->point;
        }
    }

    class Criteria
    {
        /**
         * The values follow the @param list below.
         *
         * @param Point $point
         * @param bool[] $flags
         * @param int|string $code
         * @param \stdClass $pickup
         * @param \stdClass $city
         */
        public function __construct(
            private Point $point,
            private array $flags,
            private int|string $code,
            private \stdClass $pickup,
            private \stdClass $city,
        ) {
        }

        public function getPoint(): Point
        {
            return $this->point;
        }

        public function getFlags(): array
        {
            return $this->flags;
        }

        public function getCode(): int|string
        {
            return $this->code;
        }

        public function isPickup(): bool
        {
            return (bool) $this->pickup;
        }

        public function setIsPickup(\stdClass $pickup): void
        {
            $this->pickup = $pickup;
        }

        public function getCity(): \stdClass
        {
            return $this->city;
        }

        // The core compares setter names exactly: this one does not count for "City".
        public function setcity(\stdClass $city): void
        {
            $this->city = $city;
        }
    }

    /** A built-in constructor, which PHPStan reflects with several variants. */
    class Period extends \DatePeriod
    {
        public function getStart(): \DateTimeInterface
        {
            return $this->getStartDate();
        }
    }

    class FastRoute extends Route
    {
    }

    /** @template TStop of \Acme\Pickup\Api\Data\ItemInterface */
    class Route
    {
        /**
         * The tags are out of order: the core takes the array's type from the first one. FastRoute,
         * which is built, inherits this constructor.
         *
         * @param ItemInterface $item
         * @param Point[] $points
         * @param \Acme\Pickup\Api\Data\ItemInterface
         * @param null|string $zone
         * @param TStop[] $stops
         */
        public function __construct(
            private array $points,
            private ItemInterface $item,
            private $label,
            private $zone,
            private array $stops,
        ) {
        }

        public function getPoints(): array
        {
            return $this->points;
        }

        public function getItem(): ItemInterface
        {
            return $this->item;
        }

        public function getLabel(): mixed
        {
            return $this->label;
        }

        public function getZone(): mixed
        {
            return $this->zone;
        }

        public function getStops(): array
        {
            return $this->stops;
        }
    }
}
