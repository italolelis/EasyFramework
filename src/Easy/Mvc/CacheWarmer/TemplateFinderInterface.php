<?php

// Copyright (c) Lellys Informática. All rights reserved. See License.txt in the project root for license information.

namespace Easy\HttpKernel\CacheClearer;

use Easy\HttpKernel\Bundle\BundleInterface;
use Easy\HttpKernel\KernelInterface;
use Easy\Mvc\View\TemplateNameParserInterface;
use Symfony\Component\Finder\Finder;

/**
 * Finds all the templates.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class TemplateFinder implements TemplateFinderInterface
{

    private $kernel;
    private $parser;
    private $rootDir;
    private $templates;

    /**
     * Constructor.
     *
     * @param KernelInterface             $kernel  A KernelInterface instance
     * @param TemplateNameParserInterface $parser  A TemplateNameParserInterface instance
     * @param string                      $rootDir The directory where global templates can be stored
     */
    public function __construct(KernelInterface $kernel, TemplateNameParserInterface $parser, $rootDir)
    {
        $this->kernel = $kernel;
        $this->parser = $parser;
        $this->rootDir = $rootDir;
    }

    /**
     * Find all the templates in the bundle and in the kernel Resources folder.
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    public function findAllTemplates()
    {
        if (null !== $this->templates) {
            return $this->templates;
        }

        $templates = array();

        foreach ($this->kernel->getBundles() as $name => $bundle) {
            $templates = array_merge($templates, $this->findTemplatesInBundle($bundle));
        }

        $templates = array_merge($templates, $this->findTemplatesInFolder($this->rootDir . '/views'));

        return $this->templates = $templates;
    }

    /**
     * Find templates in the given directory.
     *
     * @param string $dir The folder where to look for templates
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    private function findTemplatesInFolder($dir)
    {
        $templates = array();

        if (is_dir($dir)) {
            $finder = new Finder();
            foreach ($finder->files()->followLinks()->in($dir) as $file) {
                $template = $this->parser->parse($file->getRelativePathname());
                if (false !== $template) {
                    $templates[] = $template;
                }
            }
        }

        return $templates;
    }

    /**
     * Find templates in the given bundle.
     *
     * @param BundleInterface $bundle The bundle where to look for templates
     *
     * @return array An array of templates of type TemplateReferenceInterface
     */
    private function findTemplatesInBundle(BundleInterface $bundle)
    {
        $templates = $this->findTemplatesInFolder($bundle->getPath() . '/Resources/views');
        $name = $bundle->getName();

        foreach ($templates as $i => $template) {
            $templates[$i] = $template->set('bundle', $name);
        }

        return $templates;
    }

}