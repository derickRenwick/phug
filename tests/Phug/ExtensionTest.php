<?php

namespace Phug\Test;

use Phug\Phug;
use Phug\Test\Extension\Ev1Extension;
use Phug\Test\Extension\Ev2Extension;
use Phug\Test\Extension\TwigExtension;

/**
 * @coversDefaultClass \Phug\AbstractExtension
 */
class ExtensionTest extends AbstractPhugTest
{
    /**
     * @covers ::<public>
     */
    public function testGetters()
    {
        self::assertTrue(is_array($this->verbatim->getOptions()));
        self::assertTrue(is_array($this->verbatim->getEvents()));
        self::assertTrue(is_array($this->verbatim->getIncludes()));
        self::assertTrue(is_array($this->verbatim->getScanners()));
        self::assertTrue(is_array($this->verbatim->getFilters()));
        self::assertTrue(is_array($this->verbatim->getKeywords()));
        self::assertTrue(is_array($this->verbatim->getTokenHandlers()));
        self::assertTrue(is_array($this->verbatim->getElementHandlers()));
        self::assertTrue(is_array($this->verbatim->getPhpTokenHandlers()));
        self::assertTrue(is_array($this->verbatim->getCompilers()));
        self::assertTrue(is_array($this->verbatim->getFormats()));
        self::assertTrue(is_array($this->verbatim->getAssignmentHandlers()));
        self::assertTrue(is_array($this->verbatim->getPatterns()));
    }

    /**
     * @covers ::<public>
     * @covers \Phug\Phug::getExtensionsGetters
     * @covers \Phug\Phug::removeOptions
     * @covers \Phug\Phug::hasExtension
     * @covers \Phug\Phug::addExtension
     * @covers \Phug\Phug::removeExtension
     * @covers \Phug\Phug::getOptions
     * @covers \Phug\Phug::extractExtensionOptions
     * @covers \Phug\Phug::getExtensionsOptions
     * @covers \Phug\Phug::removeExtensionFromCurrentRenderer
     */
    public function testImplement()
    {
        $code = implode("\n", [
            '//Comment',
            '- $foo = 1',
            'p=$foo',
        ]);
        $html = '<!-- Comment --><p>1</p>';
        $twig = '{# Comment #}{% $foo = 1 %}<p>{{ $foo|e }}</p>';
        $render1 = Phug::render($code);
        $has1 = Phug::hasExtension(TwigExtension::class);
        Phug::addExtension(TwigExtension::class);
        $render2 = Phug::render($code);
        $has2 = Phug::hasExtension(TwigExtension::class);
        Phug::removeExtension(TwigExtension::class);
        $render3 = Phug::render($code);
        $has3 = Phug::hasExtension(TwigExtension::class);

        self::assertFalse($has1);
        self::assertSame($html, $render1);
        self::assertTrue($has2);
        self::assertSame($twig, $render2);
        self::assertFalse($has3);
        self::assertSame($html, $render3);
    }

    /**
     * @covers \Phug\Phug::extractExtensionOptions
     * @covers \Phug\Phug::getExtensionsOptions
     * @covers \Phug\Phug::removeExtensionFromCurrentRenderer
     */
    public function testAddModuleAsExtension()
    {
        $compilerHas1 = in_array(CompilerModule::class, Phug::getRenderer()->getCompiler()->getOption('modules'));
        $has1 = Phug::hasExtension(CompilerModule::class);
        Phug::addExtension(CompilerModule::class);
        $compilerHas2 = in_array(CompilerModule::class, Phug::getRenderer()->getCompiler()->getOption('modules'));
        $has2 = Phug::hasExtension(CompilerModule::class);
        Phug::removeExtension(CompilerModule::class);
        $compilerHas3 = in_array(CompilerModule::class, Phug::getRenderer()->getCompiler()->getOption('modules'));
        $has3 = Phug::hasExtension(CompilerModule::class);

        self::assertFalse($has1);
        self::assertFalse($compilerHas1);
        self::assertTrue($has2);
        self::assertTrue($compilerHas2);
        self::assertFalse($has3);
        self::assertFalse($compilerHas3);
    }

    /**
     * @covers \Phug\Partial\ExtensionsTrait::mergeOptions
     */
    public function testEventsMerge()
    {
        Phug::addExtension(Ev1Extension::class);
        Phug::addExtension(Ev2Extension::class);
        $enabled = Phug::render('div');
        Phug::removeExtension(Ev1Extension::class);
        Phug::removeExtension(Ev2Extension::class);
        $disabled = Phug::render('div');

        self::assertSame('<div></div>', $enabled);
        self::assertSame('<div></div>', $disabled);
    }
}
