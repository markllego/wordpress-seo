<?php

namespace Yoast\WP\SEO\Tests\Unit\Presentations\Indexable_Presentation;

use Brain\Monkey;
use Mockery;
use Yoast\WP\SEO\Tests\Unit\TestCase;

/**
 * Class Canonical_Test
 *
 * @coversDefaultClass \Yoast\WP\SEO\Presentations\Indexable_Presentation
 *
 * @group presentations
 * @group canonical
 */
class Canonical_Test extends TestCase {

	use Presentation_Instance_Builder;

	/**
	 * Sets up the test class.
	 */
	protected function set_up() {
		parent::set_up();

		$this->set_instance();
	}

	/**
	 * Tests the situation where the canonical is given.
	 *
	 * @covers ::generate_canonical
	 */
	public function test_with_canonical() {
		$this->indexable->canonical = 'https://example.com/canonical/';

		$this->assertEquals( 'https://example.com/canonical/', $this->instance->generate_canonical() );
	}

	/**
	 * Tests the situation where the permalink is given.
	 *
	 * @covers ::generate_canonical
	 */
	public function test_with_permalink() {
		$this->indexable->permalink = 'https://example.com/permalink/';

		$this->indexable_helper
			->expects( 'dynamic_permalinks_enabled' )
			->once()
			->andReturn( false );

		Monkey\Functions\expect( 'is_date' )
			->once()
			->andReturn( false );

		Monkey\Functions\expect( 'is_attachment' )
			->once()
			->andReturn( false );

		$this->assertEquals( 'https://example.com/permalink/', $this->instance->generate_canonical() );
	}

	/**
	 * Tests the situation where an empty value is returned.
	 *
	 * @covers ::generate_canonical
	 */
	public function test_without_canonical_or_permalink() {
		$this->indexable_helper
			->expects( 'dynamic_permalinks_enabled' )
			->once()
			->andReturn( false );

		Monkey\Functions\expect( 'is_date' )
			->once()
			->andReturn( false );

		Monkey\Functions\expect( 'is_attachment' )
			->once()
			->andReturn( false );

		$this->assertEmpty( $this->instance->generate_canonical() );
	}

	/**
	 * Tests the situation where the permalink is given and dynamic permalinks are enabled
	 *
	 * @covers ::generate_canonical
	 */
	public function test_with_permalink_with_dynamic_permalinks() {
		$this->indexable->permalink = 'https://example.com/permalink/';

		$this->indexable_helper
			->expects( 'dynamic_permalinks_enabled' )
			->once()
			->andReturn( true );

		$this->permalink_helper
			->expects( 'get_permalink_for_indexable' )
			->with( $this->instance->model )
			->once()
			->andReturn( 'https://example.com/dynamic-permalink/' );

		$this->assertEquals( 'https://example.com/dynamic-permalink/', $this->instance->generate_canonical() );
	}

	/**
	 * Tests the situation where the permalink is given and we are in a date archive.
	 *
	 * @covers ::generate_canonical
	 */
	public function test_with_permalink_on_date_archive() {
		$this->indexable->permalink = '';

		$this->indexable_helper
			->expects( 'dynamic_permalinks_enabled' )
			->once()
			->andReturn( false );

		Monkey\Functions\expect( 'is_date' )
			->once()
			->andReturnTrue();

		$this->current_page
			->expects( 'get_date_archive_permalink' )
			->andReturn( 'https://example.com/2022/06' );

		$this->assertEquals( 'https://example.com/2022/06', $this->instance->generate_canonical() );
	}

	/**
	 * Tests the situation where the permalink is given and we are in an attachment page.
	 *
	 * @covers ::generate_canonical
	 */
	public function test_with_permalink_on_attachment_page() {
		$wp          = Mockery::mock();
		$wp->request = 'https://example.com/image';

		$GLOBALS['wp'] = $wp;

		$this->indexable_helper
			->expects( 'dynamic_permalinks_enabled' )
			->once()
			->andReturn( false );

		Monkey\Functions\expect( 'is_date' )
			->once()
			->andReturn( false );

		Monkey\Functions\expect( 'is_attachment' )
			->once()
			->andReturn( true );

		Monkey\Functions\expect( 'home_url' )
			->with( $wp->request )
			->andReturnFirstArg();

		$this->assertEquals( 'https://example.com/image/', $this->instance->generate_canonical() );
	}
}
