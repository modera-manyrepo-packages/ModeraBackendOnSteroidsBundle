<?php

namespace Modera\BackendOnSteroidsBundle\AssetsDiscovery;

use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Given that system has these bundles installed: FooXBundle, FooYBundle, after invoking `resolve` method
 * with expression "@Foo.*Bundle", paths of bundles FooXBundle and FooYBundle will be returned.
 *
 * @author    Sergei Lissovski <sergei.lissovski@modera.org>
 * @copyright 2015 Modera Foundation
 */
class PathExpressionResolver
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    /**
     * @param string $pathExpression
     *
     * @return string
     */
    public function resolve($pathExpression)
    {
        if (substr($pathExpression, 0, 1) == '@') { // bundle's presence indicated as "@" in path
            $bundleName = null;

            $separatorIndex = strpos($pathExpression, '/');
            // 1 as start index because we don't need @
            if (false !== $separatorIndex) {
                $bundleName = substr($pathExpression, 1, $separatorIndex - 1);
            } else { // the whole path is just a bundle name
                $bundleName = substr($pathExpression, 1);
            }

            // @ModeraBackend.*Bundle/Resources/public/js
            // ModeraBackend.*Bundle

            /* @var BundleInterface[] $matchedBundles */
            $matchedBundles = [];
            foreach ($this->kernel->getBundles() as $bundle) {
                /* @var BundleInterface $bundle */

                $regex = '|^'.$bundleName.'$|';

                if (preg_match($regex, $bundle->getName())) {
                    $matchedBundles[] = $bundle;
                }
            }

            $resolvedPaths = [];

            foreach ($matchedBundles as $matchedBundle) {
                if (false !== $separatorIndex) {
                    // @ModeraBackend.*Bundle/Resources/public/js -->
                    // /var/www/mymegaproject/PathOfBackendBlahBundle/Resources/public/js
                    $resolvedPaths[] = implode('', [
                        $matchedBundle->getPath(),
                        substr($pathExpression, $separatorIndex),
                    ]);
                } else { // the whole bundle
                    $resolvedPaths[] = $matchedBundle->getPath();
                }
            }

            return $resolvedPaths;
        }

        return [$pathExpression];
    }
}
