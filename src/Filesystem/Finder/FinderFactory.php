<?php

namespace Meteor\Filesystem\Finder;

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\Glob;

class FinderFactory
{
    /**
     * @param string $path
     * @param array $filters
     * @param int $depth
     *
     * @return Finder
     */
    public function create($path, array $filters = null, $depth = null)
    {
        $finder = new Finder();
        $finder->in($path);
        $finder->ignoreVCS(true);
        $finder->ignoreDotFiles(false);

        if ($depth !== null) {
            $finder->depth($depth);
        }

        if ($filters !== null) {
            $patterns = [];
            foreach ($filters as $filter) {
                $patterns[] = $this->generatePattern($filter);
            }

            $finder->filter(function (SplFileInfo $file) use ($path, $patterns) {
                if ($file->isLink() && strpos($file->getLinkTarget(), $path) !== 0) {
                    return false;
                }

                $include = false;
                foreach ($patterns as $patternData) {
                    list($pattern, $negate) = $patternData;

                    $filepath = preg_replace('/^' . preg_quote($path . '/', '/') . '/', '', $file->getPathname());
                    if (preg_match($pattern, $filepath)) {
                        if ($negate) {
                            return false;
                        }

                        $include = true;
                    } elseif ($negate && !$file->isDir()) {
                        $include = true;
                    }
                }

                return $include;
            });
        }

        return $finder;
    }

    /**
     * Generate a regex pattern from the filter rule.
     *
     * @param string $filter
     */
    private function generatePattern($filter)
    {
        $negate = false;
        $pattern = '';

        if (strlen($filter) && $filter[0] === '!') {
            $negate = true;
            $filter = substr($filter, 1);
        }

        if (strlen($filter) && $filter[0] === '/') {
            $pattern .= '^';
            $filter = substr($filter, 1);
        } elseif (strlen($filter) - 1 === strpos($filter, '/')) {
            $filter = substr($filter, 0, -1);
        }

        // Remove delimiters as well as caret (^) and dollar sign ($) from the regex produced by Glob
        $pattern .= substr(Glob::toRegex($filter, false), 2, -2) . '(?=$|/)';

        $pattern = '/' . str_replace('/', '\/', $pattern) . '/';

        return [$pattern, $negate];
    }
}
