<?php
declare(strict_types=1);

namespace ElasticScoutDriverPlus\Tests\Integration\Builders;

use ElasticAdapter\Search\SearchRequest;
use ElasticScoutDriverPlus\Builders\BoolSearchRequestBuilder;
use ElasticScoutDriverPlus\Exceptions\SearchRequestBuilderException;
use ElasticScoutDriverPlus\Tests\App\Book;
use ElasticScoutDriverPlus\Tests\Integration\TestCase;
use stdClass;

/**
 * @covers \ElasticScoutDriverPlus\Builders\BoolSearchRequestBuilder
 * @uses   \ElasticScoutDriverPlus\Builders\AbstractSearchRequestBuilder
 * @uses   \ElasticScoutDriverPlus\Decorators\EngineDecorator
 */
final class BoolSearchRequestBuilderTest extends TestCase
{
    /**
     * @var BoolSearchRequestBuilder
     */
    private $builder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = new BoolSearchRequestBuilder(new Book());
    }

    public function test_exception_is_thrown_when_none_of_the_clauses_are_specified(): void
    {
        $this->expectException(SearchRequestBuilderException::class);

        $this->builder
            ->withTrashed()
            ->buildSearchRequest();
    }

    public function test_request_with_trashed_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'must' => [
                    ['match_all' => new stdClass()],
                ]
            ]
        ]);

        $actual = $this->builder
            ->withTrashed()
            ->must('match_all')
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_request_with_only_trashed_can_be_built(): void
    {
        $this->app['config']->set('scout.soft_delete', true);

        $expected = new SearchRequest([
            'bool' => [
                'filter' => [
                    ['term' => ['__soft_deleted' => 1]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->onlyTrashed()
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_must_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'must' => [
                    ['term' => ['year' => 2020]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->must('term', ['year' => 2020])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_raw_must_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'must' => [
                    'term' => ['year' => 2020],
                ]
            ]
        ]);

        $actual = $this->builder
            ->mustRaw(['term' => ['year' => 2020]])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_consecutive_usage_of_must_and_must_raw_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'must' => [
                    ['term' => ['year' => 2019]],
                    ['term' => ['year' => 2020]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->mustRaw(['term' => ['year' => 2019]])
            ->must('term', ['year' => 2020])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_must_not_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'must_not' => [
                    ['term' => ['year' => 2020]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->mustNot('term', ['year' => 2020])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_raw_must_not_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'must_not' => [
                    'term' => ['year' => 2020],
                ]
            ]
        ]);

        $actual = $this->builder
            ->mustNotRaw(['term' => ['year' => 2020]])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_should_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'should' => [
                    ['term' => ['year' => 2019]],
                    ['term' => ['year' => 2020]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->should('term', ['year' => 2019])
            ->should('term', ['year' => 2020])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_raw_should_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'should' => [
                    ['term' => ['year' => 2019]],
                    ['term' => ['year' => 2020]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->shouldRaw([
                ['term' => ['year' => 2019]],
                ['term' => ['year' => 2020]],
            ])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_minimum_should_match_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'should' => [
                    ['term' => ['year' => 2019]],
                    ['term' => ['year' => 2020]],
                ],
                'minimum_should_match' => 1
            ]
        ]);

        $actual = $this->builder
            ->should('term', ['year' => 2019])
            ->should('term', ['year' => 2020])
            ->minimumShouldMatch(1)
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_filter_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'filter' => [
                    ['term' => ['year' => 2020]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->filter('term', ['year' => 2020])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_raw_filter_can_be_built(): void
    {
        $expected = new SearchRequest([
            'bool' => [
                'filter' => [
                    'term' => ['year' => 2020],
                ]
            ]
        ]);

        $actual = $this->builder
            ->filterRaw(['term' => ['year' => 2020]])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }

    public function test_search_request_with_raw_filter_and_soft_deletes_can_be_built(): void
    {
        $this->app['config']->set('scout.soft_delete', true);

        $expected = new SearchRequest([
            'bool' => [
                'filter' => [
                    ['term' => ['year' => 2020]],
                    ['term' => ['__soft_deleted' => 0]],
                ]
            ]
        ]);

        $actual = $this->builder
            ->filterRaw(['term' => ['year' => 2020]])
            ->buildSearchRequest();

        $this->assertEquals($expected, $actual);
    }
}