<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\DoctrineBundle\DoctrineBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            new FOS\UserBundle\FOSUserBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new KL\UtilBundle\KLUtilBundle(),
            new Magend\IssueBundle\MagendIssueBundle(),
            new Magend\ArticleBundle\MagendArticleBundle(),
            new Magend\PageBundle\MagendPageBundle(),
            new Magend\ArchitectBundle\MagendArchitectBundle(),
            new Magend\KeywordBundle\MagendKeywordBundle(),
            new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
            new Avalanche\Bundle\ImagineBundle\AvalancheImagineBundle(),
            new Magend\HomeBundle\MagendHomeBundle(),
            new Magend\MagazineBundle\MagendMagazineBundle(),
            new Magend\InstituteBundle\MagendInstituteBundle(),
            new Magend\BaseBundle\MagendBaseBundle(),
            new Magend\FeedbackBundle\MagendFeedbackBundle(),
            new Magend\UserBundle\MagendUserBundle(),
            new Magend\HotBundle\MagendHotBundle(),
            new Magend\ProjectBundle\MagendProjectBundle(),
            new Magend\OutputBundle\MagendOutputBundle(),
            new Magend\AssetBundle\MagendAssetBundle(),
            new Magend\VersionBundle\MagendVersionBundle(),
            new Magend\BackendBundle\MagendBackendBundle(),
            new Sonata\DoctrineORMAdminBundle\SonataDoctrineORMAdminBundle(),
            new Sonata\jQueryBundle\SonatajQueryBundle(),
            new Sonata\BlockBundle\SonataBlockBundle(),
            new Sonata\CacheBundle\SonataCacheBundle(),
            new Sonata\AdminBundle\SonataAdminBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Magend\NewsBundle\MagendNewsBundle(),
            new Magend\OperationBundle\MagendOperationBundle(),
            new Magend\IAPBundle\MagendIAPBundle(),
            new Magend\DevBundle\MagendDevBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Acme\DemoBundle\AcmeDemoBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
