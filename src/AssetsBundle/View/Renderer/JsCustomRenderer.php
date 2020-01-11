<?php

namespace AssetsBundle\View\Renderer;

class JsCustomRenderer implements \Laminas\View\Renderer\RendererInterface {

    /**
     * @var \Laminas\View\Resolver\ResolverInterface
     */
    protected $resolver;

    /**
     * @return \AssetsBundle\View\Renderer\JsRenderer
     */
    public function getEngine() {
        return $this;
    }

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     * @param \Laminas\View\Resolver\ResolverInterface $oResolver
     * @return \AssetsBundle\View\Renderer\JsRenderer
     */
    public function setResolver(\Laminas\View\Resolver\ResolverInterface $oResolver) {
        $this->resolver = $oResolver;
        return $this;
    }

    /**
     * Renders js files contents
     * @param \AssetsBundle\View\Renderer\ViewModel $oViewModel
     * @param null|array|\ArrayAccess $aValues
     * @return string
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function render($oViewModel, $aValues = null) {
        if (!($oViewModel instanceof \Laminas\View\Model\ViewModel)) {
            throw new \InvalidArgumentException(sprintf(
                    'View Model expects an instance of \Laminas\View\Model\ViewModel, "%s" given', is_object($oViewModel) ? get_class($oViewModel) : gettype($oViewModel)
            ));
        }
        $aJsFiles = $oViewModel->getVariable('jsCustomFiles');
        if (!is_array($aJsFiles)) {
            throw new \LogicException('JsFiles expects an array "' . gettype($aJsFiles) . '" given');
        }
        $sRetour = '';
        foreach ($aJsFiles as $oJsAssetFile) {
            if ($oJsAssetFile instanceof \AssetsBundle\AssetFile\AssetFile) {
                $sRetour .= $oJsAssetFile->getAssetFileContents() . PHP_EOL;
            } else {
                throw new \LogicException('Js asset file expects an instance of \AssetsBundle\AssetFile\AssetFile, "' . (is_object($oJsAssetFile) ? get_class($oJsAssetFile) : gettype($oJsAssetFile)) . '" given');
            }
        }
        return $sRetour;
    }

}
