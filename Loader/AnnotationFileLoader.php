<?php

namespace Intaro\TwigSandboxBundle\Loader;

use Intaro\TwigSandboxBundle\SecurityPolicy\SecurityPolicyRules;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Resource\FileResource;

class AnnotationFileLoader extends FileLoader
{
    protected AnnotationClassLoader $loader;

    /**
     * Constructor.
     *
     * @param FileLocator           $locator A FileLocator instance
     * @param AnnotationClassLoader $loader  An AnnotationClassLoader instance
     */
    public function __construct(FileLocator $locator, AnnotationClassLoader $loader, string $env = null)
    {
        if (!function_exists('token_get_all')) {
            throw new \RuntimeException('The Tokenizer extension is required for the routing annotation loaders.');
        }

        parent::__construct($locator, $env);

        $this->loader = $loader;
    }

    /**
     * Loads from annotations from a file.
     *
     * @param string  $file A PHP file path
     * @param ?string $type The resource type
     *
     * @return SecurityPolicyRules A Rules instance
     *
     * @throws \InvalidArgumentException When annotations can't be parsed
     */
    public function load($file, $type = null): SecurityPolicyRules
    {
        $path = $this->locator->locate($file);

        $rules = new SecurityPolicyRules();
        if ($class = $this->findClass($path)) {
            $rules->addResource(new FileResource($path));
            $rules->merge($this->loader->load($class, $type));
        }

        return $rules;
    }

    /**
     * @param ?string $type
     */
    public function supports($resource, $type = null): bool
    {
        return is_string($resource) && 'php' === pathinfo($resource, PATHINFO_EXTENSION) && (!$type || 'annotation' === $type);
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return class-string|false Full class name if found, false otherwise
     */
    protected function findClass(string $file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all((string) file_get_contents($file));

        if (1 === \count($tokens) && \T_INLINE_HTML === $tokens[0][0]) {
            throw new \InvalidArgumentException(sprintf('The file "%s" does not contain PHP code. Did you forgot to add the "<?php" start tag at the beginning of the file?', $file));
        }

        $nsTokens = [\T_NS_SEPARATOR => true, \T_STRING => true];
        if (\defined('T_NAME_QUALIFIED')) {
            $nsTokens[\T_NAME_QUALIFIED] = true;
        }

        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];

            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && \T_STRING === $token[0]) {
                /** @var class-string $r */
                $r = $namespace . '\\' . $token[1];

                return $r;
            }

            if (true === $namespace && isset($nsTokens[$token[0]])) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1], $nsTokens[$tokens[$i][0]])) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (\T_CLASS === $token[0]) {
                // Skip usage of ::class constant and anonymous classes
                $skipClassToken = false;
                for ($j = $i - 1; $j > 0; --$j) {
                    if (!isset($tokens[$j][1])) {
                        if ('(' === $tokens[$j] || ',' === $tokens[$j]) {
                            $skipClassToken = true;
                        }
                        break;
                    }

                    if (\T_DOUBLE_COLON === $tokens[$j][0] || \T_NEW === $tokens[$j][0]) {
                        $skipClassToken = true;
                        break;
                    } elseif (!\in_array($tokens[$j][0], [\T_WHITESPACE, \T_DOC_COMMENT, \T_COMMENT])) {
                        break;
                    }
                }

                if (!$skipClassToken) {
                    $class = true;
                }
            }

            if (\T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}
