<?php

declare(strict_types = 1);

namespace Drupal\Tests\oe_bootstrap_theme\PatternAssertion;

use Symfony\Component\DomCrawler\Crawler;

/**
 * Assertions for the pagination pattern.
 */
class PaginationPatternAssert extends BasePatternAssert {

  /**
   * {@inheritdoc}
   */
  protected function getAssertions(string $variant): array {
    return [
      'links' => [
        [$this, 'assertLinks'],
      ],
      'alignment' => [
        [$this, 'assertAlignment'],
      ],
      'size' => [
        [$this, 'assertSize'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function assertBaseElements(string $html, string $variant): void {
    $this->assertCounts([
      'nav' => 1,
      'ul' => 1,
    ], new Crawler($html));
  }

  /**
   * Asserts the pager links.
   *
   * @param array[] $expected
   *   Expected pager links.
   *   Each link is an array, with the following keys:
   *     - 'url': The link destination.
   *     - 'label': The label, if exists.
   *     - 'icon': The name of the icon.
   *     - 'active': Whether the item has an 'active' class.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertLinks(array $expected, Crawler $crawler): void {
    $expected_count = count($expected);
    $this->assertCounts([
      // Fail on unexpected additional <li> or <a> anywhere in the html.
      'li' => $expected_count,
      'a' => $expected_count,
      // Fail if classes are missing or the structure is wrong.
      // The :first-child makes sure that each <li> has exactly one <a>.
      'nav > ul > li.page-item > a.page-link:first-child' => $expected_count,
    ], $crawler);

    $icon_pattern_assert = new IconPatternAssert();

    $actual_items_selection = $crawler->filter('nav > ul > li');

    foreach ($expected as $i => $expected_item) {
      $li = $actual_items_selection->eq($i);
      $this->assertCount(1, $li);
      $link = $li->filter('a');
      $this->assertCount(1, $link);
      $this->assertSame($expected_item['url'], $link->attr('href'));
      if (isset($expected_item['icon'])) {
        $icon_pattern_assert->assertPattern([
          'name' => $expected_item['icon'],
        ], $link->html());
      }
      if (isset($expected_item['label'])) {
        $this->assertSame($expected_item['label'], $link->html());
      }
      $this->assertSame(
        !empty($expected_item['active']) ? 1 : 0,
        $li->filter('.active')->count(),
      );
    }
  }

  /**
   * Asserts the alignment.
   *
   * @param string $expected
   *   The expected alignment. One of 'start', 'end' or 'center'.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertAlignment(string $expected, Crawler $crawler): void {
    $this->assertElementExists('nav > ul.justify-content-' . $expected, $crawler);
  }

  /**
   * Asserts the icon size.
   *
   * @param string $expected
   *   The expected icon size. Either 'sm' or 'lg'.
   * @param \Symfony\Component\DomCrawler\Crawler $crawler
   *   The crawler.
   */
  protected function assertSize(string $expected, Crawler $crawler): void {
    $this->assertElementExists('nav > ul.pagination-' . $expected, $crawler);
  }

}
