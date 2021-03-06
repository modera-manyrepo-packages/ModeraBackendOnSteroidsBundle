<?php

namespace Modera\BackendOnSteroidsBundle\Tests\Functional\Command;

use Modera\BackendOnSteroidsBundle\Tests\Fixtures\TestOutput;
use Modera\FoundationBundle\Testing\FunctionalTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * @author Sergei Lissovski <sergei.lissovski@gmail.com>
 */
class GenerateScriptsCommandTest extends FunctionalTestCase
{
    // see doSetUpBeforeClass()
    private static $scriptsDir;

    public static function getScriptsPaths()
    {
        $result = [];

        foreach (['cleanup', 'compile-mjr', 'compile-bundles', 'setup'] as $name) {
            $result[] = self::$scriptsDir.'steroids-'.$name.'.sh';
        }

        return $result;
    }

    private static function deleteGeneratedScripts()
    {
        foreach (self::getScriptsPaths() as $filepath) {
            if (file_exists($filepath)) {
                unlink($filepath);
            }
        }
    }

    /**
     * Template method.
     */
    public static function doSetUpBeforeClass()
    {
        self::$scriptsDir = __DIR__.'/../../../';

        static::deleteGeneratedScripts();
    }

    /**
     * {@inheritdoc}
     */
    public static function doTearDownAfterClass()
    {
        static::deleteGeneratedScripts();
    }

    public function testExecute()
    {
        $app = new Application(self::$container->get('kernel'));
        $app->setAutoExit(false);

        $input = new ArrayInput(array(
            'command' => 'modera:backend-on-steroids:generate-scripts',
        ));
        $input->setInteractive(false);

        $output = new TestOutput();

        $result = $app->run($input, $output);

        $this->assertEquals(0, $result);

        foreach (self::getScriptsPaths() as $filepath) {
            $this->assertTrue(file_exists($filepath));

            $contents = @file_get_contents($filepath);

            $this->assertTrue('' != $contents);
        }
    }
}
