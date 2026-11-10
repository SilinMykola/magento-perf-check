<?php

declare(strict_types=1);

namespace Silin\MagentoPerfCheck\Tests\PhpStan\Rule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FileTypeMapper;
use Silin\MagentoPerfCheck\PhpStan\ProjectIndexProvider;
use Silin\MagentoPerfCheck\PhpStan\Rule\WebapiConstructorParameterRule;
use Silin\MagentoPerfCheck\PhpStan\WebapiConstructorCollector;

/**
 * Routes and preferences come from tests/Fixtures/Project/indexed: Acme_Core's webapi.xml, its
 * global and webapi_rest di.xml, and a frontend preference in Acme_Promo.
 *
 * @extends RuleTestCase<WebapiConstructorParameterRule>
 */
final class WebapiConstructorParameterRuleTest extends RuleTestCase
{
    private const MODEL = 'Acme\Pickup\Model\\';
    private const DATA = 'Acme\Pickup\Api\Data\\';

    protected function getRule(): Rule
    {
        return new WebapiConstructorParameterRule(self::getContainer()->getByType(ProjectIndexProvider::class), $this->createReflectionProvider());
    }

    protected function getCollectors(): array
    {
        return [new WebapiConstructorCollector(self::getContainer()->getByType(FileTypeMapper::class), $this->createReflectionProvider())];
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../extension.neon', __DIR__ . '/../Fixtures/index/indexed-project.neon'];
    }

    public function testFieldsOnlyAnUnsupportedParameterCouldSet(): void
    {
        $this->analyse([__DIR__ . '/../Fixtures/webapi-constructor.php'], [
            [self::message('sort', 'SearchRequest', 'SearchRequestInterface', '\Acme\Pickup\Model\SortOrder[]', 'Sort'), 152],
            // The setter is on the class, but the core looks for it on the requested type.
            [self::message('page', 'SearchRequest', 'SearchRequestInterface', 'Acme\Pickup\Model\Page', 'Page'), 156],
            [self::message('tags', 'SearchRequest', 'SearchRequestInterface', 'array[]', 'Tags'), 158],
            // No native type and no @param tag of its own.
            [self::message('note', 'SearchRequest', 'SearchRequestInterface', '(none)', 'Note'), 159],
            // isActive() is the getter.
            [self::message('active', 'SearchRequest', 'SearchRequestInterface', 'stdClass', 'Active'), 160],
            // Reached through getAreas(), built from the webapi_rest preference.
            [self::message('point', 'Area', 'AreaInterface', 'Acme\Pickup\Model\Point', 'Point'), 248],
            // The end of the preference chain, with an inherited constructor.
            [self::message('point', 'CustomContact', 'ContactInterface', 'Acme\Pickup\Model\Point', 'Point'), 280],
            // A class taken by a service method, with no preference.
            [self::message('point', 'Criteria', null, 'Acme\Pickup\Model\Point', 'Point'), 320],
            // setcity() is not setCity(): the core compares names exactly.
            [self::message('city', 'Criteria', null, 'stdClass', 'City'), 324],
            // The tag at its position names no variable, and Laminas matches tags by name.
            [self::message('label', 'FastRoute', null, '(none)', 'Label'), 374],
            // TStop is a template, not a class, for the core.
            [self::message('stops', 'FastRoute', null, 'TStop[]', 'Stops'), 374],
        ]);
    }

    private static function message(string $param, string $class, ?string $type, string $paramType, string $field): string
    {
        $type = $type !== null ? self::DATA . $type : self::MODEL . $class;

        return sprintf(
            '[P-API-01] A web API request cannot set $%s of %s, built for %s: since APSB25-88 the core passes no request value '
            . 'to a constructor parameter of type %s, and %s has no set%s(), so a request with this field fails with "%s" is not supported. '
            . 'Add set%s() to %s, or give the parameter a simple or *\Api\Data\* type.',
            $param,
            self::MODEL . $class,
            $type,
            $paramType,
            $type,
            $field,
            $field,
            $field,
            $type,
        );
    }
}
