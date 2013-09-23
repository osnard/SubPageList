<?php

namespace Tests\Component\SubPageList;

use ParamProcessor\Processor;
use ParserHooks\HookDefinition;
use SubPageList\Extension;
use SubPageList\Page;
use SubPageList\Settings;
use SubPageList\UI\WikitextSubPageListRenderer;
use Title;

/**
 * @covers SubPageList\UI\WikitextSubPageListRenderer
 *
 * @group SubPageList
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class WikitextSubPageListRendererTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var Page[]
	 */
	protected static $pages;

	public static function setUpBeforeClass() {
		self::$pages = array(
//		'TempSPLTest:QQQ',
//		'TempSPLTest:QQQ/Root0',
//		'TempSPLTest:QQQ/Root1/Sub1-0',
//		'TempSPLTest:QQQ/Root2/Sub2-0',
//		'TempSPLTest:QQQ/Root2/Sub2-1',
//		'TempSPLTest:QQQ/Root2/Sub2-2',
//		'TempSPLTest:QQQ/Root2/Sub2-2/SubSub2-2-0',
//		'TempSPLTest:QQQ/Root2/Sub2-3',

			// A page with no sub pages
			'AAA' => new Page(
				Title::newFromText( 'AAA' )
			),

			// A page with one sub page
			'BBB' => new Page(
				Title::newFromText( 'BBB' ),
				array(
					new Page(
						Title::newFromText( 'BBB/Sub' )
					)
				)
			),
		);
	}

	protected function getDefaultOptions() {
		return array(

		);
	}

	protected function assertCreatesList( array $params, $listText ) {
		$params = array_merge( $this->getDefaultOptions(), $params );

		$this->assertEquals(
			$listText,
			$this->getListForParams( $params )
		);
	}

	protected function getListForParams( array $rawParams ) {
		$extension = new Extension( Settings::newFromGlobals( $GLOBALS ) );

		$definition = $extension->getListFunctionRunner()->getDefinition();

		$params = $this->getProcessedParams( $definition, $rawParams );

		return $extension->newSubPageListRenderer()->render(
			self::$pages[$params['page']],
			$params
		);
	}

	protected function getProcessedParams( HookDefinition $definition, array $rawParams ) {
		$processor = Processor::newDefault();

		$processor->setParameters(
			$rawParams,
			$definition->getParameters()
		);

		$params = array();

		foreach ( $processor->processParameters()->getParameters() as $param ) {
			$params[$param->getName()] = $param->getValue();
		}

		return $params;
	}

	public function testListForOnePage() {
		$this->assertCreatesList(
			array(
				'page' => 'AAA',
				'showpage' => 'yes',
			),
			'<div class="subpagelist">' . "\n[[AAA|AAA]]\n</div>"
		);
	}

	public function testListForOnePageWithOneSub() {
		$this->assertCreatesList(
			array(
				'page' => 'BBB',
				'showpage' => 'yes',
			),
			'<div class="subpagelist">
[[BBB|BBB]]
* [[BBB/Sub|BBB/Sub]]
</div>'
		);
	}

	/**
	 * @dataProvider textProvider
	 */
	public function testListWithHeader( $introText ) {
		$this->assertCreatesList(
			array(
				'page' => 'AAA',
				'intro' => $introText,
				'showpage' => 'yes',
			),
			'<div class="subpagelist">' . "\n" .$introText . "\n[[AAA|AAA]]\n</div>"
		);
	}

	public function textProvider() {
		return array(
			array( 'a' ),
			array( '0' ),
			array( '~=[,,_,,]:3' ),
		);
	}

	/**
	 * @dataProvider textProvider
	 */
	public function testListWithFooter( $outroText ) {
		$this->assertCreatesList(
			array(
				'page' => 'AAA',
				'outro' => $outroText,
				'showpage' => 'yes',
			),
			'<div class="subpagelist">' ."\n[[AAA|AAA]]\n" . $outroText . "\n</div>"
		);
	}

	public function testListWithoutLinks() {
		$this->assertCreatesList(
			array(
				'page' => 'BBB',
				'links' => 'no',
			),
			'<div class="subpagelist">' ."\n* BBB/Sub\n</div>"
		);
	}

}
